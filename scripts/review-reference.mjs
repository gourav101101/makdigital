import { spawn } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';

const root = path.resolve('storage/app/reference/lemonyellow');
await fs.mkdir(root, { recursive: true });
const browser = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=0', '--no-first-run', '--no-default-browser-check',
    `--user-data-dir=${path.join(root, 'browser-profile')}`, 'about:blank',
], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
let socket;
try {
    const endpoint = await new Promise((resolve, reject) => {
        let output = '';
        const timer = setTimeout(() => reject(new Error('Browser startup timed out')), 25000);
        browser.on('error', reject);
        browser.stderr.on('data', chunk => {
            output += chunk;
            const match = output.match(/DevTools listening on (ws:\/\/[^\s]+)/);
            if (match) { clearTimeout(timer); resolve(match[1]); }
        });
    });
    socket = new WebSocket(endpoint);
    await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
    let sequence = 0;
    const pending = new Map();
    socket.onmessage = event => {
        const message = JSON.parse(event.data);
        const handler = pending.get(message.id);
        if (handler) {
            pending.delete(message.id);
            message.error ? handler.reject(new Error(JSON.stringify(message.error))) : handler.resolve(message.result);
        }
    };
    const call = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
        const id = ++sequence;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params, ...(sessionId ? { sessionId } : {}) }));
    });
    const { targetId } = await call('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await call('Target.attachToTarget', { targetId, flatten: true });
    const command = (method, params) => call(method, params, sessionId);
    const evaluate = async expression => (await command('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })).result.value;
    const capture = async name => {
        const { data } = await command('Page.captureScreenshot', { format: 'png' });
        await fs.writeFile(path.join(root, name), Buffer.from(data, 'base64'));
    };
    await command('Page.enable');
    await command('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    await command('Page.navigate', { url: 'https://lemonyellow.design/' });
    await sleep(10000);
    await capture('desktop-hero.png');
    const audit = await evaluate(`JSON.stringify({
        title:document.title, url:location.href, width:innerWidth, height:innerHeight,
        documentHeight:document.documentElement.scrollHeight,
        headings:[...document.querySelectorAll('h1,h2,h3')].map(e=>({tag:e.tagName,text:e.innerText})),
        links:[...document.querySelectorAll('a')].map(e=>({text:e.innerText,url:e.href})),
        canvases:document.querySelectorAll('canvas').length,
        videos:[...document.querySelectorAll('video')].map(e=>({src:e.currentSrc,autoplay:e.autoplay,muted:e.muted})),
        scripts:[...document.scripts].map(e=>e.src).filter(Boolean),
        resources:performance.getEntriesByType('resource').map(e=>e.name),
        fonts:[...new Set([...document.querySelectorAll('h1,h2,p,a')].slice(0,100).map(e=>getComputedStyle(e).fontFamily))],
        bodyText:document.body.innerText
    })`);
    await fs.writeFile(path.join(root, 'audit.json'), JSON.stringify(JSON.parse(audit), null, 2));
    for (const [name, y] of [['desktop-work.png', 1000], ['desktop-solutions.png', 2200], ['desktop-process.png', 3400]]) {
        await evaluate(`window.scrollTo(0,${y})`);
        await sleep(2200);
        await capture(name);
    }
    await command('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await command('Page.navigate', { url: 'https://lemonyellow.design/' });
    await sleep(7000);
    await capture('mobile-hero.png');
    console.log(JSON.stringify({ savedTo: root, ...JSON.parse(audit), resources: undefined, bodyText: undefined, links: undefined, scripts: undefined }, null, 2));
    await call('Browser.close');
} finally {
    socket?.close();
    browser.kill();
}
