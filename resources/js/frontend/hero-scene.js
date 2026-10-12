import * as THREE from 'three';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

export function createGlobe(element) {
    const mount = element.querySelector('.globe-canvas');
    const pauseButton = element.querySelector('[data-scene-pause]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.6));
    renderer.setClearColor(0x000000, 0);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.0;
    mount.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(35, 1, .1, 30);
    camera.position.set(0, .1, 7.5);
    const environment = new RoomEnvironment();
    const pmrem = new THREE.PMREMGenerator(renderer);
    const environmentTarget = pmrem.fromScene(environment, .04);
    scene.environment = environmentTarget.texture;
    environment.dispose();
    pmrem.dispose();

    const globe = new THREE.Group();
    globe.rotation.set(.22, -.55, -.49);
    scene.add(globe);
    const colors = [0x163ec3, 0x155dd6, 0x176bdf, 0x1a83dc, 0x23a8dd, 0x37c3e0, 0x80dbee];
    const ribbons = [];
    for (let band = 0; band < 7; band++) {
        const latitude = -1.13 + band * .375;
        const profile = [];
        for (let step = 0; step <= 12; step++) {
            const phi = latitude - .135 + .27 * step / 12;
            profile.push(new THREE.Vector2(1.69 * Math.cos(phi), 1.69 * Math.sin(phi)));
        }
        for (let step = 12; step >= 0; step--) {
            const phi = latitude - .135 + .27 * step / 12;
            profile.push(new THREE.Vector2(1.60 * Math.cos(phi), 1.60 * Math.sin(phi)));
        }
        profile.push(profile[0].clone());
        const geometry = new THREE.LatheGeometry(profile, 112, band * .3, Math.PI * 2 - .18);
        const material = new THREE.MeshPhysicalMaterial({ color: colors[band], metalness: .25, roughness: .3, clearcoat: .5, clearcoatRoughness: .3, side: THREE.DoubleSide, envMapIntensity: .7 });
        const ribbon = new THREE.Mesh(geometry, material);
        ribbons.push(ribbon);
        globe.add(ribbon);
    }
    const key = new THREE.DirectionalLight(0xf4fbff, 2);
    key.position.set(-3, 5, 5);
    scene.add(key);
    const fill = new THREE.DirectionalLight(0x78cfff, 2);
    fill.position.set(4, 1, -3);
    scene.add(fill);

    let visible = true;
    let paused = false;
    let frame = 0;
    let previousTime = 0;
    let elapsed = 0;
    let pointerX = 0;
    let pointerY = 0;
    let disposed = false;
    function resize() {
        const { width, height } = mount.getBoundingClientRect();
        if (!width || !height) return;
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.position.z = camera.aspect < 1 ? 8.0 : 7.5;
        camera.updateProjectionMatrix();
        renderer.render(scene, camera);
    }
    function render(time) {
        frame = 0;
        if (disposed || !visible || document.hidden || paused || reduced.matches) return;
        const delta = previousTime ? Math.min((time - previousTime) / 1000, .05) : .016;
        previousTime = time;
        elapsed += delta;
        globe.rotation.y += ((-.55 + pointerX * .2 + elapsed * .06) - globe.rotation.y) * .035;
        globe.rotation.x += ((.22 + pointerY * .13) - globe.rotation.x) * .035;
        globe.position.y = Math.sin(elapsed * .6) * .05;
        const assembly = Math.max(0, 1 - elapsed / 1.25);
        ribbons.forEach((ribbon, index) => { ribbon.position.y = (index - 3) * .08 * assembly * assembly; });
        renderer.render(scene, camera);
        frame = requestAnimationFrame(render);
    }
    function sync() {
        if (frame) cancelAnimationFrame(frame);
        frame = 0;
        previousTime = 0;
        if (!disposed && visible && !document.hidden && !paused && !reduced.matches) frame = requestAnimationFrame(render);
    }
    function onPointer(event) {
        if (event.pointerType === 'touch') return;
        const box = element.getBoundingClientRect();
        pointerX = (event.clientX - box.left) / box.width - .5;
        pointerY = (event.clientY - box.top) / box.height - .5;
    }
    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(mount);
    const intersectionObserver = new IntersectionObserver(entries => { visible = entries[0].isIntersecting; sync(); });
    intersectionObserver.observe(element);
    document.addEventListener('visibilitychange', sync);
    reduced.addEventListener('change', sync);
    element.addEventListener('pointermove', onPointer);
    pauseButton.hidden = false;
    pauseButton.addEventListener('click', () => {
        paused = !paused;
        pauseButton.setAttribute('aria-pressed', String(paused));
        pauseButton.setAttribute('aria-label', paused ? 'Play globe animation' : 'Pause globe animation');
        pauseButton.firstElementChild.textContent = paused ? '▷' : 'Ⅱ';
        sync();
    });
    renderer.domElement.addEventListener('webglcontextlost', event => {
        event.preventDefault();
        paused = true;
        sync();
        element.classList.remove('scene-ready');
        pauseButton.hidden = true;
        mount.hidden = true;
    });
    resize();
    element.classList.add('scene-ready');
    sync();
    window.addEventListener('pagehide', event => {
        if (event.persisted) return;
        disposed = true;
        sync();
        resizeObserver.disconnect();
        intersectionObserver.disconnect();
        document.removeEventListener('visibilitychange', sync);
        reduced.removeEventListener('change', sync);
        element.removeEventListener('pointermove', onPointer);
        ribbons.forEach(ribbon => { ribbon.geometry.dispose(); ribbon.material.dispose(); });
        environmentTarget.dispose();
        renderer.dispose();
    }, { once: true });
}
