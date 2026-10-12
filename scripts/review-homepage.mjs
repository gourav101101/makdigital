import { spawn } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';

const root = path.resolve('storage/app/review/homepage');
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
    const errors = [];
    socket.onmessage = event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
        if (message.method === 'Log.entryAdded' && message.params.entry.level === 'error') errors.push(message.params.entry);
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
    const evaluate = async expression => {
        const result = await command('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
        return result.result.value;
    };
    const capture = async name => {
        const { data } = await command('Page.captureScreenshot', { format: 'png' });
        await fs.writeFile(path.join(root, name), Buffer.from(data, 'base64'));
    };
    await command('Page.enable');
    await command('Runtime.enable');
    await command('Log.enable');
    const report = { errors, viewports: [] };
    for (const [width, height] of (process.env.REVIEW_QUICK ? [] : [[1440, 1000], [390, 844], [360, 800], [768, 1024]])) {
        await command('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width < 600 });
        await command('Page.navigate', { url: 'http://127.0.0.1:8000/' });
        await sleep(width === 1440 ? 6500 : 3500);
        const metrics = await evaluate(`({width:innerWidth,scrollWidth:document.documentElement.scrollWidth,title:document.title,canvas:!!document.querySelector('.scene-ready canvas'),failedImages:[...document.images].filter(i=>i.complete&&!i.naturalWidth).map(i=>i.src),font:getComputedStyle(document.querySelector('h1')).fontFamily})`);
        report.viewports.push(metrics);
        await capture(`hero-${width}.png`);
        if (width === 1440 || width === 390) {
            for (const id of ['work', 'services', 'about', 'contact']) {
                await evaluate(`document.getElementById('${id}').scrollIntoView({behavior:'instant'})`);
                await sleep(1200);
                await capture(`${id}-${width}.png`);
            }
        }
        if (width === 390) {
            report.mobile3DInitiallyDeferred = await evaluate(`!document.querySelector('canvas') && !document.querySelector('[data-scene-activate]').hidden`);
            await evaluate(`document.querySelector('[data-scene-activate]').click()`);
            await sleep(5000);
            report.mobile3DActivated = await evaluate(`!!document.querySelector('.scene-ready canvas')`);
            await evaluate(`document.querySelector('[data-menu-toggle]').click()`);
            report.mobileMenu = await evaluate(`({open:!document.querySelector('#mobile-menu').hidden,expanded:document.querySelector('[data-menu-toggle]').getAttribute('aria-expanded'),mainInert:document.querySelector('main').inert,focus:document.activeElement.textContent})`);
            await capture('mobile-menu.png');
            await command('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
            report.menuEscape = await evaluate(`({closed:document.querySelector('#mobile-menu').hidden,focusReturned:document.activeElement===document.querySelector('[data-menu-toggle]')})`);
            await evaluate(`document.querySelectorAll('.service-item')[1].open=true`);
            report.serviceAccordion = await evaluate(`({openCount:document.querySelectorAll('.service-item[open]').length,secondOpen:document.querySelectorAll('.service-item')[1].open})`);
        }
    }
    await command('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    await command('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await sleep(2500);
    report.reducedMotion = await evaluate(`({canvas:!!document.querySelector('canvas'),posterVisible:getComputedStyle(document.querySelector('.globe-poster')).opacity,headlineVisible:getComputedStyle(document.querySelector('h1')).opacity})`);
    await capture('reduced-motion.png');
    await command('Emulation.setEmulatedMedia', { features: [] });
    await command('Emulation.setScriptExecutionDisabled', { value: true });
    await command('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await sleep(1500);
    await capture('no-javascript.png');
    await command('Emulation.setScriptExecutionDisabled', { value: false });
    await command('Page.navigate', { url: 'http://127.0.0.1:8000/work/stoneplus-surfaces' });
    await sleep(1500);
    await capture('project-detail.png');
    const axe = await fs.readFile('node_modules/axe-core/axe.min.js', 'utf8');
    report.accessibility = [];
    for (const route of ['/', '/work', '/services', '/insights', '/about', '/careers', '/contact', '/privacy-policy', '/terms-and-conditions', '/admin/login']) {
        await command('Page.navigate', { url: `http://127.0.0.1:8000${route}` });
        await sleep(1000);
        await evaluate(axe);
        const violations = await evaluate(`axe.run(document, {runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21aa']}}).then(r=>r.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary}))})))`);
        report.accessibility.push({ route, violations });
    }
    const credentials = JSON.parse(await fs.readFile('storage/app/private/admin-access.json', 'utf8'));
    await evaluate(`if(document.querySelector('[name=password]')) { document.querySelector('[name=email]').value=${JSON.stringify(credentials.email)}; document.querySelector('[name=password]').value=${JSON.stringify(credentials.password)}; document.querySelector('form').requestSubmit(); }`);
    await sleep(1500);
    report.adminLogin = await evaluate(`({path:location.pathname,heading:document.querySelector('h1')?.textContent})`);
    for (const route of ['/admin', '/admin/content', '/admin/enquiries', '/admin/settings']) {
        await command('Page.navigate', { url: `http://127.0.0.1:8000${route}` });
        await sleep(800);
        await evaluate(axe);
        report.accessibility.push({route,violations:await evaluate(`axe.run(document, {runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21aa']}}).then(r=>r.violations.map(v=>({id:v.id,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary}))})))`)});
    }
    const { default: lighthouse } = await import('lighthouse');
    const audit = await lighthouse('http://127.0.0.1:8000/', { port:Number(new URL(endpoint).port), output:'json', onlyCategories:['performance','accessibility','best-practices','seo'], logLevel:'error' });
    await fs.writeFile(path.join(root, '../lighthouse-mobile.json'), audit.report);
    report.lighthouse = Object.fromEntries(Object.entries(audit.lhr.categories).map(([key,value])=>[key,value.score]));
    if (process.env.REVIEW_QUICK) {
        const previous = JSON.parse(await fs.readFile(path.join(root, 'report.json'), 'utf8'));
        report.viewports = previous.viewports;
        for (const key of ['mobile3DInitiallyDeferred', 'mobile3DActivated', 'mobileMenu', 'menuEscape', 'serviceAccordion']) report[key] = previous[key];
    }
    await fs.writeFile(path.join(root, 'report.json'), JSON.stringify(report, null, 2));
    console.log(JSON.stringify(report, null, 2));
    await call('Browser.close');
} finally {
    socket?.close();
    browser.kill();
}
