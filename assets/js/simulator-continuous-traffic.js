/**
 * Conclusion Intermodal Dry Port (CIDP) - Expanded 35 Ha Smart Hub
 * Autonomous Continuous Traffic Loop & Assisted Yard Crane Engine (End-to-End v4.1)
 *
 * Fitur Utama:
 * 1. Peta Diperluas Proporsional 480m x 230m (Blok Stacking 8 Bay x 8 Row jauh lebih lebar
 *    dibandingkan koridor jalan aspal 10.5m-16m, sesuai struktur Block-Bay-Row-Tier nyata).
 * 2. Arus Gate-In & Gate-Out Kontinu Tanpa Jeda (6 Truk Eksternal + 2 Shuttle + 2 Tol):
 *    Memakai sensor jarak lajur ketat & 100% mengikuti jalan aspal tanpa memotong blok yard.
 * 3. 4 E-RTG Lapangan + 1 RTG Rel + 4 Reach Stacker (Truck <-> RTG / RS Sinergi Aktif):
 *    - RTG-02 (Blok A) & RTG-03 (Blok B) melayani lajur utara Z=+4.5;
 *    - RTG-04 (Blok C) & RTG-05 (Blok D) melayani lajur selatan Z=-4.5;
 *    - RS-01..RS-04 membantu di koridor Empty Depot, Blok E, Reefer & South Haul.
 * 4. 2 Forklift Elektrik, Kereta Api KA 2518 aktif & Lencana SVG Interaktif Opt-In.
 */

(function (global) {
    'use strict';

    const TRAFFIC_VERSION = '4.1.0';

    const state = {
        initialized: false,
        running: true,
        speedMultiplier: 1.0,
        showBadges: true,
        followTargetId: null,
        lastFollowPos: null,
        totalCompletedLoops: 0,
        totalGateInCount: 0,
        totalGateOutCount: 0,
        totalBoxesHandled: 0,
        wasManualAnimating: false,
        lastLogAt: 0,
        lastTimeMs: 0,
        trucks: [],
        highwayCruisers: [],
        forklifts: [],
        equipments: [],
        trainAgent: null,
        badgeElements: {},
        scanBeams: {},
        gateLeds: {},
        bayHalos: [],
        followRing: null,
        barrierState: { inOpen: 0, outOpen: 0 },
        lastKpiUpdateAt: 0
    };

    const BADGE_THEMES = {
        emerald: { bg: 'rgba(6, 78, 59, 0.90)',   border: 'rgba(52, 211, 153, 0.80)', dot: '#34d399', text: '#ecfdf5', bar: '#10b981' },
        blue:    { bg: 'rgba(15, 23, 42, 0.90)',  border: 'rgba(56, 189, 248, 0.80)', dot: '#38bdf8', text: '#f0f9ff', bar: '#0ea5e9' },
        amber:   { bg: 'rgba(69, 26, 3, 0.90)',   border: 'rgba(251, 191, 36, 0.85)', dot: '#fbbf24', text: '#fffbeb', bar: '#f59e0b' },
        cyan:    { bg: 'rgba(8, 51, 68, 0.90)',   border: 'rgba(34, 211, 238, 0.85)', dot: '#22d3ee', text: '#ecfeff', bar: '#06b6d4' },
        purple:  { bg: 'rgba(46, 16, 101, 0.90)', border: 'rgba(192, 132, 252, 0.80)',dot: '#c084fc', text: '#faf5ff', bar: '#a855f7' },
        rose:    { bg: 'rgba(76, 5, 25, 0.90)',   border: 'rgba(251, 113, 133, 0.80)',dot: '#fb7185', text: '#fff1f2', bar: '#f43f5e' },
        slate:   { bg: 'rgba(15, 23, 42, 0.86)',  border: 'rgba(148, 163, 184, 0.6)', dot: '#cbd5e1', text: '#f8fafc', bar: '#64748b' }
    };

    const VECTOR_ICONS = {
        truck: `<svg viewBox="0 0 28 16" class="w-4 h-3 shrink-0" fill="none">
            <rect x="1" y="3" width="16" height="8" rx="1" fill="#38bdf8" stroke="#e0f2fe" stroke-width="1"/>
            <path d="M18 5h4.5l3.5 3.5V11H18V5z" fill="#f8fafc"/>
            <rect x="20" y="6" width="3.2" height="2.2" rx="0.4" fill="#0f172a"/>
            <circle cx="5" cy="12.5" r="2" fill="#0f172a" stroke="#cbd5e1" stroke-width="1"/>
            <circle cx="14" cy="12.5" r="2" fill="#0f172a" stroke="#cbd5e1" stroke-width="1"/>
            <circle cx="22.5" cy="12.5" r="2" fill="#0f172a" stroke="#cbd5e1" stroke-width="1"/>
        </svg>`,
        chassis: `<svg viewBox="0 0 28 16" class="w-4 h-3 shrink-0" fill="none">
            <rect x="1" y="9" width="17" height="2" rx="0.5" fill="#fbbf24"/>
            <path d="M18 5h4.5l3.5 3.5V11H18V5z" fill="#fb923c"/>
            <rect x="20" y="6" width="3.2" height="2.2" rx="0.4" fill="#0f172a"/>
            <circle cx="5" cy="12.5" r="2" fill="#0f172a" stroke="#fde68a" stroke-width="1"/>
            <circle cx="14" cy="12.5" r="2" fill="#0f172a" stroke="#fde68a" stroke-width="1"/>
            <circle cx="22.5" cy="12.5" r="2" fill="#0f172a" stroke="#fde68a" stroke-width="1"/>
        </svg>`,
        reefer: `<svg viewBox="0 0 28 16" class="w-4 h-3 shrink-0" fill="none">
            <rect x="1" y="3" width="16" height="8" rx="1" fill="#f8fafc" stroke="#22d3ee" stroke-width="1.2"/>
            <path d="M9 4.5v5M6.8 5.8l4.4 2.4M11.2 5.8L6.8 8.2" stroke="#0891b2" stroke-width="1.1" stroke-linecap="round"/>
            <path d="M18 5h4.5l3.5 3.5V11H18V5z" fill="#22d3ee"/>
            <circle cx="5" cy="12.5" r="2" fill="#0f172a" stroke="#a5f3fc" stroke-width="1"/>
            <circle cx="22.5" cy="12.5" r="2" fill="#0f172a" stroke="#a5f3fc" stroke-width="1"/>
        </svg>`,
        shuttle: `<svg viewBox="0 0 28 16" class="w-4 h-3 shrink-0" fill="none">
            <rect x="1" y="4" width="15" height="7" rx="1" fill="#3b82f6" stroke="#bfdbfe" stroke-width="1"/>
            <rect x="17" y="5" width="7" height="6" rx="1" fill="#eab308"/>
            <circle cx="5" cy="12.5" r="2" fill="#0f172a" stroke="#fef08a" stroke-width="1"/>
            <circle cx="20.5" cy="12.5" r="2" fill="#0f172a" stroke="#fef08a" stroke-width="1"/>
        </svg>`,
        rs: `<svg viewBox="0 0 24 18" class="w-3.5 h-3.5 shrink-0" fill="none">
            <rect x="2" y="9" width="11" height="5" rx="1" fill="#ef4444"/>
            <path d="M7 10L19 3" stroke="#cbd5e1" stroke-width="2.2" stroke-linecap="round"/>
            <rect x="15" y="2" width="7" height="3" rx="0.5" fill="#f59e0b"/>
            <circle cx="5" cy="15" r="2" fill="#0f172a" stroke="#e2e8f0" stroke-width="1"/>
            <circle cx="11" cy="15" r="2.2" fill="#0f172a" stroke="#e2e8f0" stroke-width="1"/>
        </svg>`,
        rtg: `<svg viewBox="0 0 24 18" class="w-3.5 h-3.5 shrink-0" fill="none">
            <path d="M4 16V3h16v13" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round"/>
            <rect x="8" y="2" width="8" height="2.5" fill="#f59e0b"/>
            <path d="M12 4.5v5" stroke="#e2e8f0" stroke-width="1.2"/>
            <rect x="7" y="9.5" width="10" height="3.2" rx="0.5" fill="#10b981"/>
        </svg>`,
        forklift: `<svg viewBox="0 0 20 16" class="w-3.5 h-3 shrink-0" fill="none">
            <rect x="2" y="7" width="8" height="5" rx="1" fill="#facc15"/>
            <path d="M12 3v10h5" stroke="#e2e8f0" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="4.5" cy="13.5" r="1.8" fill="#0f172a" stroke="#fef08a" stroke-width="1"/>
            <circle cx="9.5" cy="13.5" r="1.8" fill="#0f172a" stroke="#fef08a" stroke-width="1"/>
        </svg>`,
        train: `<svg viewBox="0 0 28 16" class="w-4 h-3 shrink-0" fill="none">
            <rect x="1" y="4" width="12" height="7" rx="1" fill="#f97316"/>
            <rect x="14" y="5" width="12" height="6" rx="0.8" fill="#0284c7"/>
            <path d="M1 13h26" stroke="#94a3b8" stroke-width="1.5"/>
            <circle cx="4" cy="12.5" r="1.4" fill="#e2e8f0"/>
            <circle cx="10" cy="12.5" r="1.4" fill="#e2e8f0"/>
            <circle cx="17" cy="12.5" r="1.4" fill="#e2e8f0"/>
            <circle cx="23" cy="12.5" r="1.4" fill="#e2e8f0"/>
        </svg>`
    };

    const BOX_COLORS = [0x0090c0, 0xd49b00, 0x007a3d, 0x0b2341, 0xe05615, 0x0284c7];

    function getGateMetrics() {
        const hasLW = (typeof LW === 'function');
        const gIn  = hasLW ? LW('f_gate_in')   : { x: -77,  z: 118 };
        const gOut = hasLW ? LW('f_gate_out')  : { x: -129, z: 118 };
        const rail = hasLW ? LW('rail_siding') : { x: 0,    z: -82 };
        const fenceHalfW = (typeof CIDP_LAYOUT !== 'undefined' && CIDP_LAYOUT.meta) ? CIDP_LAYOUT.meta.fence_half_w : 240;
        const fenceHalfD = (typeof CIDP_LAYOUT !== 'undefined' && CIDP_LAYOUT.meta) ? CIDP_LAYOUT.meta.fence_half_d : 148;
        const gateZ = Number(gIn.z || 118);
        const lanes = (typeof getAllGateLanes === 'function')
            ? getAllGateLanes()
            : ((typeof CIDP_LAYOUT !== 'undefined' && Array.isArray(CIDP_LAYOUT.gate_lanes) && CIDP_LAYOUT.gate_lanes.length)
                ? CIDP_LAYOUT.gate_lanes
                : [
                    { code: 'IN-01', direction: 'INBOUND', lane_num: 1, has_weighbridge: true, x: -56.0, z: gateZ },
                    { code: 'IN-02', direction: 'INBOUND', lane_num: 2, has_weighbridge: true, x: -64.5, z: gateZ },
                    { code: 'IN-03', direction: 'INBOUND', lane_num: 3, has_weighbridge: true, x: -73.0, z: gateZ },
                    { code: 'IN-04', direction: 'INBOUND', lane_num: 4, has_weighbridge: false, x: -81.5, z: gateZ },
                    { code: 'IN-05', direction: 'INBOUND', lane_num: 5, has_weighbridge: false, x: -90.0, z: gateZ },
                    { code: 'IN-06', direction: 'INBOUND', lane_num: 6, has_weighbridge: true, x: -98.5, z: gateZ },
                    { code: 'OUT-01', direction: 'OUTBOUND', lane_num: 1, has_weighbridge: false, x: -116.0, z: gateZ },
                    { code: 'OUT-02', direction: 'OUTBOUND', lane_num: 2, has_weighbridge: false, x: -124.5, z: gateZ },
                    { code: 'OUT-03', direction: 'OUTBOUND', lane_num: 3, has_weighbridge: false, x: -133.0, z: gateZ },
                    { code: 'OUT-04', direction: 'OUTBOUND', lane_num: 4, has_weighbridge: false, x: -141.5, z: gateZ }
                ]);
        const laneX = (code, fb) => {
            const found = lanes.find(l => l.code === code);
            return found ? Number(found.x) : fb;
        };
        const minX = Math.min(...lanes.map(l => Number(l.x)));
        const maxX = Math.max(...lanes.map(l => Number(l.x)));
        return {
            lanes,
            laneX,
            inX: laneX('IN-01', -56.0),
            outX: laneX('OUT-01', -116.0),
            inCenterX: Number(gIn.x || -77.25),
            outCenterX: Number(gOut.x || -128.75),
            plazaCenterX: (minX + maxX) / 2,
            gateZ,
            barrierZ: gateZ - 11,
            railZ: rail.z,
            fenceHalfW,
            fenceHalfD,
            hwyCenterZ: fenceHalfD + 22,
            hwyInZ: fenceHalfD + 16,
            hwyOutZ: fenceHalfD + 28,
            vergeZ: fenceHalfD + 4,
            openL: minX - 6.5, // -148.0
            openR: maxX + 8.0  // -48.0
        };
    }

    function wrapAngle(a) {
        while (a > Math.PI) a -= Math.PI * 2;
        while (a < -Math.PI) a += Math.PI * 2;
        return a;
    }

    function lerp(a, b, t) {
        return a + (b - a) * Math.min(1, Math.max(0, t));
    }

    function isManualActionRunning() {
        try {
            return (typeof isAnimating !== 'undefined' && isAnimating === true);
        } catch (e) {
            return false;
        }
    }

    function emitTrafficLog(msg, force) {
        const now = performance.now();
        if (!force && now - state.lastLogAt < 3200) return;
        state.lastLogAt = now;
        if (typeof logTicker === 'function') logTicker(msg);
    }

    // =========================================================================
    // 1. PETA DIPERLUAS: JALAN ASPAL LEBAR, TOL 4-LAJUR & PORTAL GERBANG UTAMA
    // =========================================================================
    function buildEnhancedDryPortMap() {
        if (typeof scene === 'undefined' || !scene) return;

        const gm = getGateMetrics();
        const gateMidX = gm.plazaCenterX; // -98.75 (pusat simetri Plaza 10 Lajur)

        const mapGroup = new THREE.Group();
        mapGroup.name = 'EnhancedPortRoadAndMapLayer';

        const asphaltMat    = new THREE.MeshStandardMaterial({ color: 0x263142, roughness: 0.90, metalness: 0.04 });
        const highwayMat    = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.86, metalness: 0.06 });
        const curbMat       = new THREE.MeshStandardMaterial({ color: 0xcbd5e1, roughness: 0.7 });
        const grassMat      = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.95 });
        const whiteLineMat  = new THREE.MeshBasicMaterial({ color: 0xf8fafc });
        const yellowLineMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 });

        const addAsphaltStrip = (w, d, x, z, mat) => {
            const m = new THREE.Mesh(new THREE.BoxGeometry(w, 0.016, d), mat || asphaltMat);
            m.position.set(x, 0.126, z);
            m.receiveShadow = true;
            mapGroup.add(m);
            return m;
        };

        // A. Jaringan Jalan Aspal Internal Rapi & Proporsional (Lebih sempit daripada Blok Yard 114m)
        addAsphaltStrip(378, 10.5, 0, 47.0);      // North Cross Boulevard (Z=47)
        addAsphaltStrip(318, 16.0, -30, 0.0);     // Central Dual-Lane Yard Spine (Z=-8..+8: North lane +4.5, South lane -4.5; berhenti di X=+129 agar tidak memotong Empty Depot)
        addAsphaltStrip(378, 10.5, 0, -47.0);     // South Haul Boulevard (Z=-47, sejajar selatan Blok C/D)
        addAsphaltStrip(22.0, 22.0, 4.0, -57.5);  // Spur Apron ke Rail Siding Ramp (X=4, Z=-47..-68)
        // Koridor Megah Plaza Gate 10 Lajur (6 In + 4 Out) + Buffer Boulevard ke North Cross Z=47
        const plazaAspW = Math.abs(gm.openR - gm.openL) + 4; // 104m lebar aspal plaza
        const plazaAspD = (gm.hwyInZ - 47.0);                // 117m bentang koridor (Z=47..164)
        addAsphaltStrip(plazaAspW, plazaAspD, gateMidX, 47.0 + plazaAspD / 2);
        addAsphaltStrip(13.5, 100, 0, 0.0);       // Central Transfer Boulevard (X=0, celah 14m Blok A/C <-> B/D)
        addAsphaltStrip(10.5, 100, -126.5, 0.0);  // Mid-East Avenue (X=-126.5, celah 11m Blok B/D <-> Reefer/Blok E)
        addAsphaltStrip(13.0, 100, -183.0, 0.0);  // Eastern Haul Road (X=-183, Reefer/Blok E <-> Bea Cukai X-Ray)
        addAsphaltStrip(10.5, 100, 126.5, 0.0);   // Mid-West Avenue (X=+126.5, celah 11m Blok A/C <-> Empty Depot)
        addAsphaltStrip(13.5, 100, 183.0, 0.0);   // CFS Logistics Avenue (X=+183, Empty Depot <-> CFS Warehouse)

        // Panah Arah di Permukaan Jalan Seluruh 10 Lajur Gate (Pre-Gate & Buffer Koridor)
        const addRoadArrow = (x, z, rotY) => {
            const grp = new THREE.Group();
            const shaft = new THREE.Mesh(new THREE.BoxGeometry(0.42, 0.01, 2.3), whiteLineMat);
            grp.add(shaft);
            const head = new THREE.Mesh(new THREE.ConeGeometry(0.82, 1.4, 3), whiteLineMat);
            head.rotation.x = Math.PI / 2;
            head.rotation.y = Math.PI / 6;
            head.position.z = 1.5;
            grp.add(head);
            grp.position.set(x, 0.138, z);
            grp.rotation.y = rotY;
            mapGroup.add(grp);
        };
        gm.lanes.forEach(lane => {
            const rotY = (lane.direction === 'INBOUND') ? Math.PI : 0; // IN menghadap Selatan (-Z), OUT menghadap Utara (+Z)
            addRoadArrow(lane.x, gm.gateZ + 22, rotY);
            addRoadArrow(lane.x, 76, rotY);
        });

        // Marka pemisah lajur tengah Central Spine (Z=0.0) & Jalan Tol (Z=hwyInZ & Z=hwyOutZ) -> 1 InstancedMesh!
        const dashGeoH = new THREE.BoxGeometry(3.4, 0.01, 0.25);
        const hDashPos = [];
        for (let x = -183; x <= 126.5; x += 7.5) hDashPos.push([x, 0.136, 0.0]);

        // B. JALAN TOL / ARTERI PELABUHAN 4-LAJUR (Z = hwyCenterZ ± 12, lebar penuh 640m)
        addAsphaltStrip(640, 24, 0, gm.hwyCenterZ, highwayMat);

        // Bahu Hijau Berumput di luar Pagar Utara (membuka koridor Plaza 10 Lajur openL..-48)
        const leftVWidth  = gm.openL - (-320); // 172m
        const rightVWidth = 320 - gm.openR;    // 368m
        const leftVCenter = -320 + leftVWidth / 2;
        const rightVCenter = gm.openR + rightVWidth / 2;

        const greenL = new THREE.Mesh(new THREE.BoxGeometry(leftVWidth, 0.14, 7.2), grassMat);
        greenL.position.set(leftVCenter, 0.07, gm.vergeZ);
        mapGroup.add(greenL);
        const greenR = new THREE.Mesh(new THREE.BoxGeometry(rightVWidth, 0.14, 7.2), grassMat);
        greenR.position.set(rightVCenter, 0.07, gm.vergeZ);
        mapGroup.add(greenR);

        // Jersey Barrier Pemisah Tol (Z = hwyCenterZ) dengan bukaan persimpangan Gate Plaza (openL .. openR)
        const medianL = new THREE.Mesh(new THREE.BoxGeometry(leftVWidth, 0.9, 0.95), curbMat);
        medianL.position.set(leftVCenter, 0.45, gm.hwyCenterZ);
        medianL.castShadow = true;
        mapGroup.add(medianL);

        const medianR = new THREE.Mesh(new THREE.BoxGeometry(rightVWidth, 0.9, 0.95), curbMat);
        medianR.position.set(rightVCenter, 0.45, gm.hwyCenterZ);
        medianR.castShadow = true;
        mapGroup.add(medianR);

        // Marka lajur tol (Inbound hwyInZ & Outbound hwyOutZ)
        for (let hx = -305; hx <= 305; hx += 8.5) {
            hDashPos.push([hx, 0.136, gm.hwyInZ]);
            hDashPos.push([hx, 0.136, gm.hwyOutZ]);
        }
        if (hDashPos.length) {
            const hDashInst = new THREE.InstancedMesh(dashGeoH, whiteLineMat, hDashPos.length);
            const hdDummy = new THREE.Object3D();
            for (let di = 0; di < hDashPos.length; di++) {
                hdDummy.position.set(hDashPos[di][0], hDashPos[di][1], hDashPos[di][2]);
                hdDummy.updateMatrix();
                hDashInst.setMatrixAt(di, hdDummy.matrix);
            }
            hDashInst.instanceMatrix.needsUpdate = true;
            hDashInst.frustumCulled = false;
            hDashInst.matrixAutoUpdate = false;
            mapGroup.add(hDashInst);
        }

        const shoulderL = new THREE.Mesh(new THREE.BoxGeometry(leftVWidth, 0.01, 0.25), yellowLineMat);
        shoulderL.position.set(leftVCenter, 0.136, gm.hwyCenterZ - 11.5);
        mapGroup.add(shoulderL);
        const shoulderR = new THREE.Mesh(new THREE.BoxGeometry(rightVWidth, 0.01, 0.25), yellowLineMat);
        shoulderR.position.set(rightVCenter, 0.136, gm.hwyCenterZ - 11.5);
        mapGroup.add(shoulderR);

        // Lampu PJU Ganda Jalan Tol (Geometri & Material Berbagi)
        const poleMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.7, roughness: 0.3 });
        const bulbMat = new THREE.MeshBasicMaterial({ color: 0xfef3c7 });
        const pjuPoleGeo = new THREE.CylinderGeometry(0.14, 0.22, 9.8, 8);
        const pjuArmGeo  = new THREE.BoxGeometry(0.18, 0.18, 5.4);
        const pjuBulbGeo = new THREE.BoxGeometry(0.6, 0.16, 0.9);
        for (let lx = -250; lx <= 250; lx += 55) {
            const pole = new THREE.Mesh(pjuPoleGeo, poleMat);
            pole.position.set(lx, 4.9, gm.hwyCenterZ);
            mapGroup.add(pole);
            const arm = new THREE.Mesh(pjuArmGeo, poleMat);
            arm.position.set(lx, 9.6, gm.hwyCenterZ);
            mapGroup.add(arm);
            [-2.5, 2.5].forEach(lz => {
                const bulb = new THREE.Mesh(pjuBulbGeo, bulbMat);
                bulb.position.set(lx, 9.45, gm.hwyCenterZ + lz);
                mapGroup.add(bulb);
            });
        }

        if (typeof createBlockLabelSprite === 'function') {
            createBlockLabelSprite('JALAN TOL ARTERI PELABUHAN (EXIT CIDP DRY PORT ↙)', -120, 11.8, gm.hwyInZ - 1);
            createBlockLabelSprite('AKSES TOL CIKAMPEK & TANJUNG PRIOK ↗', 70, 11.8, gm.hwyOutZ + 1);
        }

        // C. BUKA PAGAR UTARA (Z = fenceHalfD = 148) & PASANG GAPURA GERBANG UTAMA 10 LAJUR
        const perimGroup = scene.getObjectByName('PerimeterAndSecurity');
        if (perimGroup && perimGroup.children.length > 0) {
            const oldNorth = perimGroup.children[0];
            if (oldNorth && oldNorth.geometry && oldNorth.geometry.parameters && oldNorth.geometry.parameters.width > 300) {
                oldNorth.visible = false;
                const fenceMat = oldNorth.material;
                const leftW = gm.openL - (-gm.fenceHalfW);
                const rightW = gm.fenceHalfW - gm.openR;
                if (leftW > 0) {
                    const nfL = new THREE.Mesh(new THREE.BoxGeometry(leftW, 2.5, 0.1), fenceMat);
                    nfL.position.set(-gm.fenceHalfW + leftW / 2, 1.25, gm.fenceHalfD);
                    perimGroup.add(nfL);
                }
                if (rightW > 0) {
                    const nfR = new THREE.Mesh(new THREE.BoxGeometry(rightW, 2.5, 0.1), fenceMat);
                    nfR.position.set(gm.openR + rightW / 2, 1.25, gm.fenceHalfD);
                    perimGroup.add(nfR);
                }
            }
        }

        const pillarMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4, metalness: 0.3 });
        const orangeMat = new THREE.MeshStandardMaterial({ color: 0xea580c, roughness: 0.3 });
        [gm.openL + 0.5, gm.openR - 0.5].forEach(px => {
            const pCol = new THREE.Mesh(new THREE.BoxGeometry(1.6, 8.8, 1.9), pillarMat);
            pCol.position.set(px, 4.4, gm.fenceHalfD);
            pCol.castShadow = true;
            mapGroup.add(pCol);
        });
        const archW = Math.abs(gm.openR - gm.openL) + 1.0;
        const archCenter = (gm.openL + gm.openR) / 2;
        const archBeam = new THREE.Mesh(new THREE.BoxGeometry(archW, 2.0, 2.1), pillarMat);
        archBeam.position.set(archCenter, 8.6, gm.fenceHalfD);
        archBeam.castShadow = true;
        mapGroup.add(archBeam);

        const archStripe = new THREE.Mesh(new THREE.BoxGeometry(archW + 0.2, 0.38, 2.2), orangeMat);
        archStripe.position.set(archCenter, 7.7, gm.fenceHalfD);
        mapGroup.add(archStripe);

        if (typeof createBlockLabelSprite === 'function') {
            createBlockLabelSprite('GERBANG UTAMA CIDP 35 HA • 6 GATE-IN & 4 GATE-OUT', archCenter, 11.4, gm.fenceHalfD);
        }

        // D. Sensor Laser Scan Curtain & Status LED Gate (Semua 10 Lajur)
        const beamGeo = new THREE.PlaneGeometry(3.8, 6.0);
        const makeBeam = (colorHex, x, z) => {
            const mat = new THREE.MeshBasicMaterial({
                color: colorHex,
                transparent: true,
                opacity: 0.0,
                side: THREE.DoubleSide,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            });
            const m = new THREE.Mesh(beamGeo, mat);
            m.position.set(x, 3.1, z);
            mapGroup.add(m);
            return m;
        };
        state.scanBeams.lanes = {};
        state.gateLeds = { lanes: {}, inMat: null, outMat: null };
        const ledGeo = new THREE.SphereGeometry(0.42, 10, 10);
        gm.lanes.forEach(lane => {
            const isIn = (lane.direction === 'INBOUND');
            state.scanBeams.lanes[lane.code] = makeBeam(isIn ? 0x06b6d4 : 0x10b981, lane.x, gm.gateZ);
            const ledMat = new THREE.MeshBasicMaterial({ color: 0x22c55e });
            const ledMesh = new THREE.Mesh(ledGeo, ledMat);
            ledMesh.position.set(lane.x, 7.6, gm.gateZ);
            mapGroup.add(ledMesh);
            state.gateLeds.lanes[lane.code] = ledMat;
            if (lane.code === 'IN-01') {
                state.scanBeams.inGate = state.scanBeams.lanes[lane.code];
                state.gateLeds.inMat = ledMat;
            }
            if (lane.code === 'OUT-01') {
                state.scanBeams.outGate = state.scanBeams.lanes[lane.code];
                state.gateLeds.outMat = ledMat;
            }
        });
        state.scanBeams.xray = makeBeam(0xf59e0b, -183, 24);

        // E. Zona Transfer Yard Hologram Halo Ring (Tepat di titik berhenti truk!)
        const haloCoords = [
            { x: 64,     z: 4.5,   color: 0x38bdf8 },  // Blok A (Under RTG-02)
            { x: -64,    z: 4.5,   color: 0xf59e0b },  // Blok B (Under RTG-03)
            { x: 64,     z: -4.5,  color: 0x10b981 },  // Blok C (Under RTG-04)
            { x: -64,    z: -4.5,  color: 0xa855f7 },  // Blok D (Under RTG-05)
            { x: -126.5, z: 24.0,  color: 0x22d3ee },  // Reefer Rack (RS-03 Assist)
            { x: -126.5, z: -24.0, color: 0xf43f5e },  // Blok E Buffer (RS-02 Assist)
            { x: 126.5,  z: 12.0,  color: 0x0ea5e9 },  // Empty Depot (RS-01 Assist)
            { x: 64,     z: -47.0, color: 0x10b981 },  // South Haul C (RS-04 Assist)
            { x: 4,      z: -66.0, color: 0x10b981 }   // Rail Apron (RTG-01 Assist)
        ];
        haloCoords.forEach(hc => {
            const hGeo = new THREE.RingGeometry(2.6, 3.3, 28);
            const hMat = new THREE.MeshBasicMaterial({ color: hc.color, side: THREE.DoubleSide, transparent: true, opacity: 0.45 });
            const hMesh = new THREE.Mesh(hGeo, hMat);
            hMesh.rotation.x = -Math.PI / 2;
            hMesh.position.set(hc.x, 0.16, hc.z);
            mapGroup.add(hMesh);
            state.bayHalos.push(hMesh);
        });

        const fRing = new THREE.Mesh(
            new THREE.RingGeometry(4.4, 5.4, 36),
            new THREE.MeshBasicMaterial({ color: 0x38bdf8, side: THREE.DoubleSide, transparent: true, opacity: 0.9 })
        );
        fRing.rotation.x = -Math.PI / 2;
        fRing.position.y = 0.24;
        fRing.visible = false;
        mapGroup.add(fRing);
        state.followRing = fRing;

        scene.add(mapGroup);
    }

    // =========================================================================
    // 2. PROCEDURAL VEHICLE & FORKLIFT BUILDERS
    // =========================================================================
    let _truckResCache = null;
    function getSharedTruckResources() {
        if (_truckResCache) return _truckResCache;
        _truckResCache = {
            trimMat:    new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4, metalness: 0.65 }),
            glassMat:   new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1, transparent: true, opacity: 0.88 }),
            chromeMat:  new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.15 }),
            chassisMat: new THREE.MeshStandardMaterial({ color: 0x0f172a, metalness: 0.55, roughness: 0.45 }),
            tyreMat:    new THREE.MeshStandardMaterial({ color: 0x0a0f1a, roughness: 0.88 }),
            bumperMat:  new THREE.MeshStandardMaterial({ color: 0xdc2626 }),
            headMat:    new THREE.MeshBasicMaterial({ color: 0xfef08a }),
            tailMat:    new THREE.MeshBasicMaterial({ color: 0xef4444 }),
            cabGeo:     new THREE.BoxGeometry(3.2, 2.55, 2.42),
            defGeo:     new THREE.BoxGeometry(2.0, 0.52, 2.36),
            grilleGeo:  new THREE.BoxGeometry(0.26, 1.35, 2.05),
            wsGeo:      new THREE.BoxGeometry(0.26, 1.05, 2.28),
            hlGeo:      new THREE.BoxGeometry(0.2, 0.24, 0.42),
            tlGeo:      new THREE.BoxGeometry(0.16, 0.22, 0.36),
            exGeo:      new THREE.CylinderGeometry(0.09, 0.09, 2.6, 10),
            trailerGeo: new THREE.BoxGeometry(12.5, 0.35, 2.42),
            bumperGeo:  new THREE.BoxGeometry(0.2, 0.28, 2.38),
            wheelGeo:   new THREE.CylinderGeometry(0.52, 0.52, 0.35, 14),
            rimGeo:     new THREE.CylinderGeometry(0.29, 0.29, 0.36, 10),
            cabMats:    {}
        };
        return _truckResCache;
    }

    function createTrafficTruckMesh(opts) {
        const res = getSharedTruckResources();
        const grp = new THREE.Group();
        grp.name = 'TrafficTruck_' + opts.id;

        const cabCol = opts.cabColor || 0x0284c7;
        if (!res.cabMats[cabCol]) {
            res.cabMats[cabCol] = new THREE.MeshStandardMaterial({ color: cabCol, roughness: 0.35, metalness: 0.2 });
        }
        const cabMat = res.cabMats[cabCol];

        const cab = new THREE.Mesh(res.cabGeo, cabMat);
        cab.position.set(6.2, 2.22, 0);
        cab.castShadow = true;
        grp.add(cab);

        const deflector = new THREE.Mesh(res.defGeo, cabMat);
        deflector.position.set(6.0, 3.76, 0);
        grp.add(deflector);

        const grille = new THREE.Mesh(res.grilleGeo, res.trimMat);
        grille.position.set(7.8, 1.7, 0);
        grp.add(grille);

        const windshield = new THREE.Mesh(res.wsGeo, res.glassMat);
        windshield.position.set(7.72, 2.78, 0);
        grp.add(windshield);

        [-0.85, 0.85].forEach(zOff => {
            const hl = new THREE.Mesh(res.hlGeo, res.headMat);
            hl.position.set(7.85, 1.25, zOff);
            grp.add(hl);
            const tl = new THREE.Mesh(res.tlGeo, res.tailMat);
            tl.position.set(-7.32, 0.95, zOff);
            grp.add(tl);
        });

        const strobe = new THREE.Mesh(
            new THREE.CylinderGeometry(0.14, 0.14, 0.26, 8),
            new THREE.MeshBasicMaterial({ color: 0xf59e0b })
        );
        strobe.position.set(6.1, 4.18, 0);
        grp.add(strobe);
        if (typeof registerHazardLight === 'function') registerHazardLight(strobe, null, 0, true);

        [-1.02, 1.02].forEach(zOff => {
            const ex = new THREE.Mesh(res.exGeo, res.chromeMat);
            ex.position.set(4.5, 2.95, zOff);
            grp.add(ex);
        });

        const trailer = new THREE.Mesh(res.trailerGeo, res.chassisMat);
        trailer.position.set(-1.0, 1.25, 0);
        trailer.castShadow = true;
        grp.add(trailer);

        const bumper = new THREE.Mesh(res.bumperGeo, res.bumperMat);
        bumper.position.set(-7.3, 0.75, 0);
        grp.add(bumper);

        const wheels = [];
        [7.0, 4.2, 2.9, -4.2, -5.5, -6.8].forEach(wx => {
            [-1.15, 1.15].forEach(wz => {
                const hub = new THREE.Group();
                hub.position.set(wx, 0.52, wz);
                const tire = new THREE.Mesh(res.wheelGeo, res.tyreMat);
                tire.rotation.x = Math.PI / 2;
                hub.add(tire);
                const rim = new THREE.Mesh(res.rimGeo, res.chromeMat);
                rim.rotation.x = Math.PI / 2;
                hub.add(rim);
                grp.add(hub);
                wheels.push(hub);
            });
        });

        let boxMesh = null;
        if (typeof createContainerMesh === 'function') {
            boxMesh = createContainerMesh(
                12.0, 2.6, 2.44,
                opts.containerColor || 0x0284c7,
                opts.containerNo || 'MSKU9012841',
                opts.cargoType || 'dry'
            );
            boxMesh.position.set(-1.0, 2.65, 0);
        } else {
            boxMesh = new THREE.Mesh(
                new THREE.BoxGeometry(12.0, 2.6, 2.44),
                new THREE.MeshStandardMaterial({ color: opts.containerColor || 0x0284c7, roughness: 0.45 })
            );
            boxMesh.position.set(-1.0, 2.65, 0);
            boxMesh.castShadow = true;
        }
        boxMesh.visible = opts.startLoaded !== false;
        grp.add(boxMesh);

        scene.add(grp);
        return { mesh: grp, boxMesh: boxMesh, wheels: wheels };
    }

    function createForkliftMesh(id, x, z) {
        const fl = new THREE.Group();
        fl.name = 'CFSForklift_' + id;

        const bodyMat  = new THREE.MeshStandardMaterial({ color: 0xfacc15, roughness: 0.35 });
        const darkMat  = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 });
        const steelMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, metalness: 0.8 });

        const body = new THREE.Mesh(new THREE.BoxGeometry(2.4, 1.15, 1.3), bodyMat);
        body.position.set(0, 0.85, 0);
        body.castShadow = true;
        fl.add(body);

        const counterweight = new THREE.Mesh(new THREE.BoxGeometry(0.65, 0.95, 1.28), darkMat);
        counterweight.position.set(-1.1, 0.95, 0);
        fl.add(counterweight);

        const cage = new THREE.Mesh(new THREE.BoxGeometry(1.3, 1.25, 1.1), darkMat);
        cage.position.set(0.1, 1.9, 0);
        fl.add(cage);

        const mast = new THREE.Mesh(new THREE.BoxGeometry(0.18, 2.8, 0.9), steelMat);
        mast.position.set(1.3, 1.55, 0);
        fl.add(mast);

        const forkGrp = new THREE.Group();
        forkGrp.name = 'flForks';
        [-0.32, 0.32].forEach(fz => {
            const prong = new THREE.Mesh(new THREE.BoxGeometry(1.3, 0.08, 0.14), steelMat);
            prong.position.set(1.95, 0.35, fz);
            forkGrp.add(prong);
        });
        const pallet = new THREE.Mesh(
            new THREE.BoxGeometry(1.1, 0.95, 1.05),
            new THREE.MeshStandardMaterial({ color: 0x2563eb, roughness: 0.5 })
        );
        pallet.name = 'flPallet';
        pallet.position.set(1.95, 0.88, 0);
        pallet.castShadow = true;
        forkGrp.add(pallet);
        fl.add(forkGrp);

        const strobe = new THREE.Mesh(
            new THREE.CylinderGeometry(0.09, 0.09, 0.18, 8),
            new THREE.MeshBasicMaterial({ color: 0xf59e0b })
        );
        strobe.position.set(0.1, 2.62, 0);
        fl.add(strobe);
        if (typeof registerHazardLight === 'function') registerHazardLight(strobe, null, 0, true);

        const wGeo = new THREE.CylinderGeometry(0.32, 0.32, 0.22, 12);
        [[-0.85, -0.62], [-0.85, 0.62], [0.85, -0.62], [0.85, 0.62]].forEach(([wx, wz]) => {
            const wh = new THREE.Mesh(wGeo, darkMat);
            wh.rotation.x = Math.PI / 2;
            wh.position.set(wx, 0.32, wz);
            fl.add(wh);
        });

        fl.position.set(x, 0, z);
        scene.add(fl);
        return fl;
    }

    function attachWheelsDetectorForPrimaryTruck(tMesh) {
        const wheels = [];
        if (!tMesh) return wheels;
        tMesh.traverse(child => {
            if (child.isMesh && Math.abs(child.position.y - 0.52) < 0.05) wheels.push(child);
        });
        return wheels;
    }

    function refreshTruckBoxVisual(trk, isLoaded) {
        trk.hasCargo = isLoaded;
        if (trk.boxMesh) {
            trk.boxMesh.visible = isLoaded;
            if (isLoaded && trk.boxMesh.material && !Array.isArray(trk.boxMesh.material)) {
                const col = trk.fixedCargoColor || BOX_COLORS[Math.floor(Math.random() * BOX_COLORS.length)];
                trk.boxMesh.material.color.setHex(col);
            }
        }
    }

    // =========================================================================
    // 3. RUTE JALAN ASPAL 100% BEBAS POTONG BLOK + SINKRON BANTU RTG / RS
    // =========================================================================
    function buildFleetAgents() {
        const gm = getGateMetrics();
        const gateZ = gm.gateZ;      // 118
        const hwyInZ = gm.hwyInZ;    // 164
        const hwyOutZ = gm.hwyOutZ;  // 176
        const laneX = gm.laneX;
        const in1 = laneX('IN-01', -56.0),  in2 = laneX('IN-02', -64.5),  in3 = laneX('IN-03', -73.0);
        const in4 = laneX('IN-04', -81.5),  in5 = laneX('IN-05', -90.0),  in6 = laneX('IN-06', -98.5);
        const out1 = laneX('OUT-01', -116.0), out2 = laneX('OUT-02', -124.5), out3 = laneX('OUT-03', -133.0), out4 = laneX('OUT-04', -141.5);

        const externalTruckConfigs = [
            {
                // TRK-01: IN-01 (80T VGM) -> Koridor Buffer -> Blok A (RTG-02) -> OUT-01
                id: 'TRK-01', label: 'B 9481 UIX', company: 'Samudera Logistik',
                iconType: 'truck', usePrimary: true, startWp: 2, speed: 16.5,
                startPos: { x: in1, z: 140, ry: Math.PI / 2 }, startLoaded: true,
                waypoints: [
                    { x: -265,  z: hwyInZ, pause: 0,   action: 'load',    badge: 'TOL MENUJU CIDP',     theme: 'blue' },
                    { x: in1,   z: hwyInZ, pause: 0,   badge: 'OFF-RAMP IN-01',      theme: 'blue' },
                    { x: in1,   z: gateZ,  pause: 1.4, action: 'inGate',  laneCode: 'IN-01', badge: 'IN-01 • VGM 32.4T', theme: 'emerald', log: '[GATE IN-01] B 9481 UIX lolos Timbang SOLAS 32.4T & OCR.' },
                    { x: in1,   z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',      theme: 'blue' },
                    { x: in1,   z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',     theme: 'blue' },
                    { x: 4.0,   z: 47.0,   pause: 0,   badge: 'BOULEVARD TENGAH',    theme: 'blue' },
                    { x: 4.0,   z: 4.5,    pause: 0,   badge: 'JALUR BAWAH RTG',     theme: 'amber' },
                    { x: 64.0,  z: 4.5,    pause: 3.4, action: 'unload',  assistEq: 'RTG-02', assistMode: 'dropoff', badge: 'LIFT-OFF • RTG-02', theme: 'amber', log: '[YARD BLOK A] RTG-02 mengangkat kontainer dari B 9481 UIX ke 8x8 stack.' },
                    { x: 126.5, z: 4.5,    pause: 0,   badge: 'MID-WEST AVE',        theme: 'slate' },
                    { x: 126.5, z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',     theme: 'purple' },
                    { x: out1,  z: 47.0,   pause: 0,   badge: 'MENUJU OUT-01',       theme: 'purple' },
                    { x: out1,  z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-01', badge: 'OUT-01 • SPPB OK', theme: 'purple', completeLoop: true, log: '[GATE OUT-01] B 9481 UIX keluar melalui Plaza OUT-01 ke Tol.' },
                    { x: out1,  z: hwyOutZ,pause: 0,   badge: 'ON-RAMP TOL',         theme: 'slate' },
                    { x: -265,  z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'JALAN TOL',   theme: 'slate' }
                ]
            },
            {
                // TRK-02: IN-04 (Fast OCR) -> Koridor Buffer -> Blok B Pick-Up (RTG-03) -> OUT-02
                id: 'TRK-02', label: 'B 9120 KLP', company: 'PT Dunex Express',
                iconType: 'chassis', cabColor: 0xea580c, containerColor: 0x059669, containerNo: 'EGLU7718294',
                startWp: 7, speed: 15.8,
                startPos: { x: -24, z: 4.5, ry: Math.PI }, startLoaded: false,
                waypoints: [
                    { x: -265,   z: hwyInZ, pause: 0,   action: 'unload',  badge: 'SASIS DARI TOL',     theme: 'amber' },
                    { x: in4,    z: hwyInZ, pause: 0,   badge: 'APPROACH IN-04',     theme: 'amber' },
                    { x: in4,    z: gateZ,  pause: 1.2, action: 'inGate',  laneCode: 'IN-04', badge: 'IN-04 • FAST DO', theme: 'emerald', log: '[GATE IN-04] B 9120 KLP (Sasis Fast OCR) verifikasi e-DO.' },
                    { x: in4,    z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',     theme: 'amber' },
                    { x: in4,    z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'amber' },
                    { x: -4.0,   z: 47.0,   pause: 0,   badge: 'BOULEVARD SELATAN',  theme: 'amber' },
                    { x: -4.0,   z: 4.5,    pause: 0,   badge: 'MASUK BAY BLOK B',   theme: 'amber' },
                    { x: -64.0,  z: 4.5,    pause: 3.4, action: 'load',    assistEq: 'RTG-03', assistMode: 'pickup', badge: 'LIFT-ON • RTG-03', theme: 'emerald', log: '[YARD BLOK B] RTG-03 menurunkan box impor ke sasis B 9120 KLP.' },
                    { x: -126.5, z: 4.5,    pause: 0,   badge: 'MID-EAST AVE',       theme: 'purple' },
                    { x: -126.5, z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'purple' },
                    { x: out2,   z: 47.0,   pause: 0,   badge: 'JALUR OUT-02',       theme: 'purple' },
                    { x: out2,   z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-02', badge: 'OUT-02 • E-SEAL', theme: 'purple', completeLoop: true, log: '[GATE OUT-02] B 9120 KLP keluar membawa muatan ke Tol.' },
                    { x: out2,   z: hwyOutZ,pause: 0,   badge: 'MERGING TOL',        theme: 'slate' },
                    { x: -265,   z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'TOL CIKAMPEK', theme: 'slate' }
                ]
            },
            {
                // TRK-03: IN-06 (80T Heavy Reefer) -> Reefer R-02 (RS-03) -> X-Ray Bea Cukai -> OUT-04
                id: 'TRK-03', label: 'B 9802 WPA', company: 'ColdChain Logistics',
                iconType: 'reefer', cabColor: 0x0891b2, containerColor: 0xf8fafc, containerNo: 'ONEU9181483', cargoType: 'reefer', fixedCargoColor: 0xf8fafc,
                startWp: 1, speed: 15.0,
                startPos: { x: -165, z: hwyInZ, ry: 0 }, startLoaded: true,
                waypoints: [
                    { x: -265,   z: hwyInZ, pause: 0,   action: 'load',    badge: 'REEFER DARI TOL',    theme: 'cyan' },
                    { x: in6,    z: hwyInZ, pause: 0,   badge: 'OFF-RAMP IN-06',     theme: 'cyan' },
                    { x: in6,    z: gateZ,  pause: 1.4, action: 'inGate',  laneCode: 'IN-06', badge: 'IN-06 • -20°C VGM', theme: 'emerald', log: '[GATE IN-06] Reefer B 9802 WPA lolos VGM -20.0°C.' },
                    { x: in6,    z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',     theme: 'cyan' },
                    { x: in6,    z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'cyan' },
                    { x: -126.5, z: 47.0,   pause: 0,   badge: 'KE ZONA REEFER',     theme: 'cyan' },
                    { x: -126.5, z: 24.0,   pause: 2.8, action: 'unload',  assistEq: 'RS-03', assistMode: 'dropoff', badge: 'RS-03 PLUG -20°C', theme: 'cyan', log: '[REEFER ZONE] RS-03 menurunkan boks Reefer ke Rack 380V.' },
                    { x: -126.5, z: 47.0,   pause: 0,   badge: 'KE CUSTOMS X-RAY',   theme: 'amber' },
                    { x: -183.0, z: 47.0,   pause: 0,   badge: 'EASTERN HAUL AVE',   theme: 'amber' },
                    { x: -183.0, z: 24.0,   pause: 1.8, action: 'xray',    badge: 'X-RAY 6 MeV SCAN',   theme: 'rose', log: '[CEISA X-RAY] Pindai X-Ray 6 MeV bersih: JALUR HIJAU.' },
                    { x: -183.0, z: -47.0,  pause: 0,   badge: 'SOUTH HAUL AVE',     theme: 'purple' },
                    { x: 4.0,    z: -47.0,  pause: 0,   badge: 'BOULEVARD UTARA',    theme: 'purple' },
                    { x: 4.0,    z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'purple' },
                    { x: out4,   z: 47.0,   pause: 0,   badge: 'MENUJU OUT-04',      theme: 'purple' },
                    { x: out4,   z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-04', badge: 'OUT-04 • REEFER OUT', theme: 'purple', completeLoop: true },
                    { x: out4,   z: hwyOutZ,pause: 0,   badge: 'MASUK JALAN TOL',    theme: 'slate' },
                    { x: -265,   z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'TOL MENUJU PORT', theme: 'slate' }
                ]
            },
            {
                // TRK-04: IN-02 (80T VGM) -> Koridor Buffer -> Blok D (RTG-05) -> OUT-01
                id: 'TRK-04', label: 'B 9633 EKO', company: 'Evergreen Freight',
                iconType: 'truck', cabColor: 0x15803d, containerColor: 0x007a3d, containerNo: 'EGLU8810294',
                startWp: 11, speed: 16.2,
                startPos: { x: out1, z: 72, ry: -Math.PI / 2 }, startLoaded: true,
                waypoints: [
                    { x: -265,   z: hwyInZ, pause: 0,   action: 'load',    badge: 'DARI JALAN TOL',     theme: 'emerald' },
                    { x: in2,    z: hwyInZ, pause: 0,   badge: 'MASUK IN-02',        theme: 'blue' },
                    { x: in2,    z: gateZ,  pause: 1.4, action: 'inGate',  laneCode: 'IN-02', badge: 'IN-02 • VGM 28.9T', theme: 'emerald', log: '[GATE IN-02] B 9633 EKO (Evergreen) terdeteksi ANPR & Timbang.' },
                    { x: in2,    z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',     theme: 'blue' },
                    { x: in2,    z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'blue' },
                    { x: -4.0,   z: 47.0,   pause: 0,   badge: 'BOULEVARD SELATAN',  theme: 'blue' },
                    { x: -4.0,   z: -4.5,   pause: 0,   badge: 'JALUR RTG BLOK D',   theme: 'amber' },
                    { x: -64.0,  z: -4.5,   pause: 3.4, action: 'unload',  assistEq: 'RTG-05', assistMode: 'dropoff', badge: 'LIFT-OFF • RTG-05', theme: 'purple', log: '[YARD BLOK D] RTG-05 mengangkat box buffer dari B 9633 EKO.' },
                    { x: -126.5, z: -4.5,   pause: 0,   badge: 'MID-EAST AVE',       theme: 'purple' },
                    { x: -126.5, z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'purple' },
                    { x: out1,   z: 47.0,   pause: 0,   badge: 'JALUR OUT-01',       theme: 'purple' },
                    { x: out1,   z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-01', badge: 'OUT-01 • CLEARED', theme: 'purple', completeLoop: true, log: '[GATE OUT-01] B 9633 EKO keluar menuju Jalan Tol.' },
                    { x: out1,   z: hwyOutZ,pause: 0,   badge: 'MERGING JALAN TOL',  theme: 'slate' },
                    { x: -265,   z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'JALAN TOL',    theme: 'slate' }
                ]
            },
            {
                // TRK-05: IN-05 (Fast OCR) -> Empty Depot (RS-01) -> OUT-03
                id: 'TRK-05', label: 'B 9215 HPN', company: 'Hapag-Lloyd Trans',
                iconType: 'truck', cabColor: 0xb91c1c, containerColor: 0xe05615, containerNo: 'HLCU4419201',
                startWp: 5, speed: 15.5,
                startPos: { x: 40, z: 47.0, ry: 0 }, startLoaded: true,
                waypoints: [
                    { x: -265,  z: hwyInZ, pause: 0,   action: 'load',    badge: 'TOL MENUJU PORT',    theme: 'blue' },
                    { x: in5,   z: hwyInZ, pause: 0,   badge: 'APPROACH IN-05',     theme: 'blue' },
                    { x: in5,   z: gateZ,  pause: 1.2, action: 'inGate',  laneCode: 'IN-05', badge: 'IN-05 • FAST PASS', theme: 'emerald' },
                    { x: in5,   z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',     theme: 'blue' },
                    { x: in5,   z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'blue' },
                    { x: 126.5, z: 47.0,   pause: 0,   badge: 'MID-WEST AVE',       theme: 'amber' },
                    { x: 126.5, z: 12.0,   pause: 2.8, action: 'unload',  assistEq: 'RS-01', assistMode: 'dropoff', badge: 'RS-01 EMPTY DEPOT', theme: 'amber', log: '[EMPTY DEPOT] RS-01 membongkar peti kemas B 9215 HPN ke Empty Yard.' },
                    { x: 126.5, z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'purple' },
                    { x: out3,  z: 47.0,   pause: 0,   badge: 'JALUR OUT-03',       theme: 'purple' },
                    { x: out3,  z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-03', badge: 'OUT-03 • SPPB OK', theme: 'purple', completeLoop: true },
                    { x: out3,  z: hwyOutZ,pause: 0,   badge: 'ON-RAMP TOL',        theme: 'slate' },
                    { x: -265,  z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'TOL CIKAMPEK', theme: 'slate' }
                ]
            },
            {
                // TRK-06: IN-03 (80T VGM) -> Blok C Pick-Up (RTG-04) -> OUT-02
                id: 'TRK-06', label: 'B 9504 MKS', company: 'Maersk Nusantara',
                iconType: 'chassis', cabColor: 0x0369a1, containerColor: 0x0090c0, containerNo: 'MSKU7710294',
                startWp: 10, speed: 16.0,
                startPos: { x: 15, z: 47.0, ry: Math.PI }, startLoaded: true,
                waypoints: [
                    { x: -265,  z: hwyInZ, pause: 0,   action: 'unload',  badge: 'SASIS DARI TOL',     theme: 'amber' },
                    { x: in3,   z: hwyInZ, pause: 0,   badge: 'MASUK IN-03',        theme: 'amber' },
                    { x: in3,   z: gateZ,  pause: 1.4, action: 'inGate',  laneCode: 'IN-03', badge: 'IN-03 • TARE 14.0T', theme: 'emerald' },
                    { x: in3,   z: 76.0,   pause: 0,   badge: 'KORIDOR BUFFER',     theme: 'amber' },
                    { x: in3,   z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'amber' },
                    { x: -4.0,  z: 47.0,   pause: 0,   badge: 'BOULEVARD SELATAN',  theme: 'amber' },
                    { x: -4.0,  z: -4.5,   pause: 0,   badge: 'JALUR RTG BLOK C',   theme: 'amber' },
                    { x: 64.0,  z: -4.5,   pause: 3.4, action: 'load',    assistEq: 'RTG-04', assistMode: 'pickup', badge: 'LIFT-ON • RTG-04', theme: 'emerald', log: '[YARD BLOK C] RTG-04 menurunkan kontainer domestik ke sasis B 9504 MKS.' },
                    { x: 126.5, z: -4.5,   pause: 0,   badge: 'MID-WEST AVE',       theme: 'purple' },
                    { x: 126.5, z: 47.0,   pause: 0,   badge: 'NORTH CROSS AVE',    theme: 'purple' },
                    { x: out2,  z: 47.0,   pause: 0,   badge: 'JALUR OUT-02',       theme: 'purple' },
                    { x: out2,  z: gateZ,  pause: 1.2, action: 'outGate', laneCode: 'OUT-02', badge: 'OUT-02 • DO OK', theme: 'purple', completeLoop: true, log: '[GATE-OUT] B 9504 MKS melewati Plaza OUT-02 menuju Tol.' },
                    { x: out2,  z: hwyOutZ,pause: 0,   badge: 'ON-RAMP TOL',        theme: 'slate' },
                    { x: -265,  z: hwyOutZ,pause: 0,   action: 'warpToStart', badge: 'TOL PRIOK',   theme: 'slate' }
                ]
            }
        ];

        externalTruckConfigs.forEach(cfg => {
            let meshObj = null;
            if (cfg.usePrimary && typeof truckMesh !== 'undefined' && truckMesh) {
                meshObj = {
                    mesh: truckMesh,
                    boxMesh: (typeof truckBoxMesh !== 'undefined') ? truckBoxMesh : null,
                    wheels: attachWheelsDetectorForPrimaryTruck(truckMesh)
                };
            } else {
                meshObj = createTrafficTruckMesh(cfg);
            }
            meshObj.mesh.position.set(cfg.startPos.x, 0, cfg.startPos.z);
            meshObj.mesh.rotation.set(0, cfg.startPos.ry, 0);
            if (meshObj.boxMesh) meshObj.boxMesh.visible = cfg.startLoaded;

            state.trucks.push({
                id: cfg.id, label: cfg.label, company: cfg.company, iconType: cfg.iconType,
                mesh: meshObj.mesh, boxMesh: meshObj.boxMesh, wheels: meshObj.wheels,
                fixedCargoColor: cfg.fixedCargoColor || null,
                isPrimaryTruck: !!cfg.usePrimary, speed: cfg.speed, wpIndex: cfg.startWp,
                pauseRemaining: 0, pauseDuration: 1.5, waitTimer: 0, cooldownMoveTimer: 0,
                badgeText: cfg.waypoints[cfg.startWp].badge || 'BEROPERASI',
                badgeTheme: cfg.waypoints[cfg.startWp].theme || 'blue',
                hasCargo: cfg.startLoaded, waypoints: cfg.waypoints
            });
        });

        // --- 2 INTERNAL TERMINAL SHUTTLES (CFS <-> RAIL SIDING & CUSTOMS/BLOK E <-> BLOK C) ---
        const tShuttle1 = createTrafficTruckMesh({
            id: 'CDP-TT-01', cabColor: 0xeab308, containerColor: 0x1d4ed8,
            containerNo: 'CMAU4410293', cargoType: 'dry', startLoaded: true
        });
        tShuttle1.mesh.position.set(183, 0, -24);
        tShuttle1.mesh.rotation.y = Math.PI / 2;
        state.trucks.push({
            id: 'CDP-TT-01', label: 'CDP-TT-01', company: 'Shuttle CFS ↔ Rail',
            iconType: 'shuttle', mesh: tShuttle1.mesh, boxMesh: tShuttle1.boxMesh, wheels: tShuttle1.wheels,
            isPrimaryTruck: false, speed: 13.8, wpIndex: 1, pauseRemaining: 0, pauseDuration: 2.2,
            waitTimer: 0, cooldownMoveTimer: 0,
            badgeText: 'KE RAIL APRON', badgeTheme: 'amber', hasCargo: true,
            waypoints: [
                { x: 183,  z: 0,     pause: 2.0, action: 'load',   badge: 'MUAT GUDANG CFS', theme: 'cyan', log: '[CFS] CDP-TT-01 memuat konsolidasi LCL dari CFS Dock.' },
                { x: 183,  z: -47.0, pause: 0,   badge: 'SOUTH HAUL AVE',  theme: 'amber' },
                { x: 4.0,  z: -47.0, pause: 0,   badge: 'APRON RAIL KA',   theme: 'amber' },
                { x: 4.0,  z: -66.0, pause: 2.8, action: 'unload', assistEq: 'RTG-01', assistMode: 'dropoff', badge: 'RTG-01 RAIL LIFT', theme: 'emerald', log: '[RAIL SIDING] RTG-01 memindahkan box CDP-TT-01 ke gerbong KA 2518.' },
                { x: 4.0,  z: -47.0, pause: 0,   badge: 'SOUTH HAUL AVE',  theme: 'blue' },
                { x: 183,  z: -47.0, pause: 0,   badge: 'KEMBALI KE CFS',  theme: 'blue' }
            ]
        });

        const tShuttle2 = createTrafficTruckMesh({
            id: 'CDP-TT-02', cabColor: 0x0d9488, containerColor: 0x0b2341,
            containerNo: 'SMDU3329104', cargoType: 'dry', startLoaded: true
        });
        tShuttle2.mesh.position.set(-126.5, 0, -47.0);
        tShuttle2.mesh.rotation.y = Math.PI;
        state.trucks.push({
            id: 'CDP-TT-02', label: 'CDP-TT-02', company: 'Shuttle Customs ↔ Blok E/C',
            iconType: 'shuttle', mesh: tShuttle2.mesh, boxMesh: tShuttle2.boxMesh, wheels: tShuttle2.wheels,
            isPrimaryTruck: false, speed: 13.0, wpIndex: 0, pauseRemaining: 0, pauseDuration: 2.0,
            waitTimer: 0, cooldownMoveTimer: 0,
            badgeText: 'KE BEA CUKAI', badgeTheme: 'cyan', hasCargo: true,
            waypoints: [
                { x: -183.0, z: -47.0, pause: 0,   badge: 'EASTERN HAUL AVE',   theme: 'amber' },
                { x: -183.0, z: 8.0,   pause: 2.0, action: 'unload', badge: 'BEHANDLE BEA CUKAI', theme: 'rose' },
                { x: -183.0, z: -47.0, pause: 0,   badge: 'SOUTH HAUL AVE',     theme: 'blue' },
                { x: -126.5, z: -47.0, pause: 0,   badge: 'MID-EAST AVE',       theme: 'blue' },
                { x: -126.5, z: -24.0, pause: 2.6, action: 'load',   assistEq: 'RS-02', assistMode: 'pickup', badge: 'RS-02 MUAT BLOK E', theme: 'emerald', log: '[BLOK E] RS-02 menaikkan box dari Blok E ke CDP-TT-02.' },
                { x: -126.5, z: -47.0, pause: 0,   badge: 'SOUTH HAUL AVE',     theme: 'blue' },
                { x: 64.0,   z: -47.0, pause: 2.6, action: 'unload', assistEq: 'RS-04', assistMode: 'dropoff', badge: 'RS-04 BLOK C SELATAN', theme: 'amber', log: '[BLOK C] RS-04 membantu bongkar shuttle CDP-TT-02 di koridor selatan.' },
                { x: -126.5, z: -47.0, pause: 0,   badge: 'KEMBALI KE CUSTOMS', theme: 'cyan' }
            ]
        });

        // --- 2 TRUK LINTAS JALAN TOL EKSTERNAL (EXPRESS LANE Z=131.5 & Z=143.5) ---
        const hwy1 = createTrafficTruckMesh({ id: 'TOL-01', cabColor: 0x475569, containerColor: 0xd49b00, startLoaded: true });
        hwy1.mesh.position.set(-200, 0, 131.5);
        hwy1.mesh.rotation.y = 0;
        state.highwayCruisers.push({ id: 'TOL-01', mesh: hwy1.mesh, wheels: hwy1.wheels, dir: 1, speed: 24 });

        const hwy2 = createTrafficTruckMesh({ id: 'TOL-02', cabColor: 0x334155, containerColor: 0x0284c7, startLoaded: true });
        hwy2.mesh.position.set(190, 0, 143.5);
        hwy2.mesh.rotation.y = Math.PI;
        state.highwayCruisers.push({ id: 'TOL-02', mesh: hwy2.mesh, wheels: hwy2.wheels, dir: -1, speed: 26 });
    }

    // =========================================================================
    // 4. ALAT BERAT YARD (RS-01..04, RTG-01..05, FORKLIFT FL-01..02) & TRAIN
    // =========================================================================
    function createSuspendedBox(colorHex) {
        const geo = new THREE.BoxGeometry(12.0, 2.55, 2.42);
        const mat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.45, metalness: 0.15 });
        const m = new THREE.Mesh(geo, mat);
        m.castShadow = true;
        return m;
    }

    function buildEquipmentAndYardAgents() {
        if (typeof equipmentMeshes === 'undefined') return;
        const gm = getGateMetrics();

        // Spawn RS-04 jika belum ada di Koridor Selatan Blok C
        if (!equipmentMeshes['RS-04'] && typeof createReachStackerModel === 'function') {
            equipmentMeshes['RS-04'] = createReachStackerModel('RS-04', 64, 0, -42.5);
        }

        const registerRS = (id, label, colorHex, opts) => {
            const rs = equipmentMeshes[id];
            if (!rs) return;
            const box = createSuspendedBox(colorHex);
            box.position.set(7.5, 4.3, 0);
            box.visible = false;
            rs.add(box);
            state.equipments.push(Object.assign({
                id: id, type: 'rs', label: label,
                badgeText: 'STANDBY YARD', badgeTheme: opts.theme || 'blue',
                mesh: rs, spreaderBox: box, assistTarget: null
            }, opts));
        };

        // RS-01: Koridor Mid-West melayani Empty Depot (+X) & Truk di X=126.5 (-X)
        registerRS('RS-01', 'RS-01 • EMPTY HUB', 0x0090c0, {
            phase: 0, cycleDur: 14, patrolZ: true, minZ: -14, maxZ: 28, fixedX: 130.5, faceAngle: 0, assistYaw: Math.PI, theme: 'blue'
        });

        // RS-02: Koridor Mid-East Selatan melayani Blok E (-X) & Truk di X=-126.5 (+X)
        registerRS('RS-02', 'RS-02 • BLOK E', 0x007a3d, {
            phase: 4, cycleDur: 15, patrolZ: true, minZ: -32, maxZ: -12, fixedX: -130.5, faceAngle: Math.PI, assistYaw: 0, theme: 'rose'
        });

        // RS-03: Koridor Mid-East Utara melayani Reefer (-X) & Truk di X=-126.5 (+X)
        registerRS('RS-03', 'RS-03 • REEFER', 0xf8fafc, {
            phase: 7, cycleDur: 14, patrolZ: true, minZ: 12, maxZ: 32, fixedX: -130.5, faceAngle: Math.PI, assistYaw: 0, theme: 'cyan'
        });

        // RS-04: Koridor Selatan Blok C melayani tumpukan selatan (+Z) & Shuttle di South Haul Z=-47 (-Z)
        registerRS('RS-04', 'RS-04 • SOUTH C', 0xd49b00, {
            phase: 2.5, cycleDur: 16, minX: 18, maxX: 108, fixedZ: -42.5, faceAngle: -Math.PI / 2, assistYaw: Math.PI / 2, theme: 'emerald'
        });

        // Helper registrasi RTG (Rail & 4 E-RTG Blok Yard A, B, C, D)
        const registerRTG = (id, label, colorHex, opts) => {
            const rtg = equipmentMeshes[id];
            if (!rtg) return;
            const rtgSpreader = rtg.getObjectByName('rtgSpreader');
            const box = createSuspendedBox(colorHex);
            box.position.set(0, -1.65, 0);
            box.visible = false;
            if (rtgSpreader) rtgSpreader.add(box);
            state.equipments.push(Object.assign({
                id: id, type: 'rtg', label: label,
                badgeText: 'OPERASIONAL RTG', badgeTheme: opts.theme || 'emerald',
                mesh: rtg, spreaderBox: box, assistTarget: null
            }, opts));
        };

        // RTG-01: Rail Siding Crane (Z = -83)
        if (equipmentMeshes['RTG-01']) {
            equipmentMeshes['RTG-01'].position.z = gm.railZ - 1.0;
        }
        registerRTG('RTG-01', 'RTG-01 • RAIL', 0xd97706, {
            mode: 'rail', minX: -45, maxX: 45, truckLocalZ: 3.8, stackLocalZ: -3.5, phase: 0, cycleDur: 15, theme: 'emerald'
        });

        // RTG-02 & RTG-03: E-RTG Lapangan Blok A & B (Z=20.5, bentangan 39m merangkul 8 Row + jalur truk Z=+4.5)
        registerRTG('RTG-02', 'RTG-02 • BLOK A', 0x0284c7, {
            mode: 'yard', minX: 18, maxX: 110, centerX: 64, truckLocalZ: -16.0, stackLocalZ: 5.5, phase: 2, cycleDur: 14, theme: 'blue'
        });
        registerRTG('RTG-03', 'RTG-03 • BLOK B', 0xea580c, {
            mode: 'yard', minX: -110, maxX: -18, centerX: -64, truckLocalZ: -16.0, stackLocalZ: 5.5, phase: 8, cycleDur: 14, theme: 'amber'
        });

        // RTG-04 & RTG-05: E-RTG Lapangan Blok C & D (Z=-20.5, bentangan 39m merangkul 8 Row + jalur truk Z=-4.5)
        registerRTG('RTG-04', 'RTG-04 • BLOK C', 0x059669, {
            mode: 'yard', minX: 18, maxX: 110, centerX: 64, truckLocalZ: 16.0, stackLocalZ: -5.5, phase: 5, cycleDur: 14, theme: 'emerald'
        });
        registerRTG('RTG-05', 'RTG-05 • BLOK D', 0x7c3aed, {
            mode: 'yard', minX: -110, maxX: -18, centerX: -64, truckLocalZ: 16.0, stackLocalZ: -5.5, phase: 11, cycleDur: 14, theme: 'purple'
        });

        // Kereta Api KA 2518 Pasoso
        if (typeof trainMesh !== 'undefined' && trainMesh) {
            state.trainAgent = {
                id: 'KA-2518', type: 'train', label: 'KA 2518 PASOSO',
                badgeText: 'RAIL LOADING 30 GERBONG', badgeTheme: 'rose',
                mesh: trainMesh, phase: 0, cycleDur: 26
            };
        }

        // 2 Forklift Elektrik Operasional di Gudang CFS (+196) & Karantina/Behandle (-196)
        const fl1 = createForkliftMesh('FL-01', 196, 4);
        const fl2 = createForkliftMesh('FL-02', -196, 8);
        state.forklifts.push(
            { id: 'FL-01', type: 'forklift', iconType: 'forklift', label: 'FL-01 • CFS', badgeText: 'PALLET SSCC-18', badgeTheme: 'amber', mesh: fl1, minX: 188, maxX: 204, fixedZ: 4, phase: 0, dur: 8.5 },
            { id: 'FL-02', type: 'forklift', iconType: 'forklift', label: 'FL-02 • BEHANDLE', badgeText: 'CEISA INSPEKSI', badgeTheme: 'cyan', mesh: fl2, minX: -204, maxX: -188, fixedZ: 8, phase: 4.2, dur: 9.2 }
        );
    }

    // =========================================================================
    // 5. KINEMATIKA LALU LINTAS, ASSIST SINKRON TRUK-CRANE & GATE
    // =========================================================================
    function checkTruckAhead(selfTruck, forwardX, forwardZ) {
        if ((selfTruck.cooldownMoveTimer || 0) > 0) return false;

        const myX = selfTruck.mesh.position.x;
        const myZ = selfTruck.mesh.position.z;
        const sideX = -forwardZ;
        const sideZ = forwardX;

        for (let i = 0; i < state.trucks.length; i++) {
            const other = state.trucks[i];
            if (other === selfTruck) continue;
            const dx = other.mesh.position.x - myX;
            const dz = other.mesh.position.z - myZ;
            const forwardDist = dx * forwardX + dz * forwardZ;
            const lateralDist = Math.abs(dx * sideX + dz * sideZ);

            if (forwardDist > 1.0 && forwardDist < 17.5 && lateralDist < 3.4) {
                const oYaw = other.mesh.rotation.y;
                const oFx = Math.cos(oYaw);
                const oFz = -Math.sin(oYaw);
                const headingMatch = oFx * forwardX + oFz * forwardZ;
                if (other.isWaitingQueue && headingMatch < 0.35 && selfTruck.id < other.id) continue;
                return true;
            }
        }
        return false;
    }

    function dispatchEquipmentAssist(eqId, truck, mode, durationSec) {
        const eq = state.equipments.find(e => e.id === eqId);
        if (!eq) return;
        eq.assistTarget = {
            truck: truck,
            mode: mode || 'dropoff',
            elapsed: 0,
            duration: durationSec || 3.0
        };
    }

    function executeWaypointTrigger(trk, wp) {
        if (wp.badge) trk.badgeText = wp.badge;
        if (wp.theme) trk.badgeTheme = wp.theme;
        trk.pauseDuration = wp.pause || 1.5;

        if (wp.action === 'warpToStart') {
            const startWp = trk.waypoints[0];
            let spawnX = startWp.x;
            while (state.trucks.some(o => o !== trk && Math.hypot(o.mesh.position.x - spawnX, o.mesh.position.z - startWp.z) < 28)) {
                spawnX -= 30;
            }
            trk.mesh.position.set(spawnX, 0, startWp.z);
            const nextWp = trk.waypoints[1];
            if (nextWp) trk.mesh.rotation.y = Math.atan2(-(nextWp.z - startWp.z), nextWp.x - spawnX);
            refreshTruckBoxVisual(trk, startWp.action !== 'unload');
            trk.wpIndex = 1;
            trk.waitTimer = 0;
            trk.cooldownMoveTimer = 0;
            trk.badgeText = trk.waypoints[1].badge || 'MENUJU CIDP';
            trk.badgeTheme = trk.waypoints[1].theme || 'blue';
            return true;
        }

        // Jika ada RTG / Reach Stacker pembantu, tunda pergantian status muatan truk sampai pertengahan aksi crane!
        if (wp.assistEq) {
            trk.pendingCargoSwap = (wp.action === 'load' || wp.action === 'unload') ? wp.action : null;
            trk.cargoSwapDone = false;
            dispatchEquipmentAssist(wp.assistEq, trk, wp.assistMode || 'dropoff', wp.pause || 3.0);
        } else {
            if (wp.action === 'unload') {
                refreshTruckBoxVisual(trk, false);
                state.totalBoxesHandled++;
            } else if (wp.action === 'load') {
                refreshTruckBoxVisual(trk, true);
                state.totalBoxesHandled++;
            }
        }

        if (wp.action === 'inGate') {
            if (wp.laneCode) state.scanBeams['lane_' + wp.laneCode] = wp.pause || 1.4;
            else state.scanBeams.inGateFlash = wp.pause || 1.4;
            state.totalGateInCount++;
        } else if (wp.action === 'outGate') {
            if (wp.laneCode) state.scanBeams['lane_' + wp.laneCode] = wp.pause || 1.2;
            else state.scanBeams.outGateFlash = wp.pause || 1.2;
            state.totalGateOutCount++;
        } else if (wp.action === 'xray') {
            state.scanBeams.xrayFlash = wp.pause || 1.8;
        }

        if (wp.completeLoop) state.totalCompletedLoops++;
        if (wp.log) emitTrafficLog(wp.log);
        return false;
    }

    function updateTruckAgent(trk, dt) {
        if (trk.isPrimaryTruck && isManualActionRunning()) {
            trk.badgeText = 'MANUAL DEMO AKTIF';
            trk.badgeTheme = 'rose';
            trk.progressRatio = null;
            return;
        }

        if ((trk.cooldownMoveTimer || 0) > 0) trk.cooldownMoveTimer = Math.max(0, trk.cooldownMoveTimer - dt);

        const wp = trk.waypoints[trk.wpIndex];
        if (!wp) return;

        if (trk.pauseRemaining > 0) {
            trk.pauseRemaining -= dt;
            trk.waitTimer = 0;
            const prog = Math.min(1, Math.max(0, 1 - (trk.pauseRemaining / (trk.pauseDuration || 1.5))));
            trk.progressRatio = prog;

            // Sinkronkan tepat di tengah durasi pengangkatan crane/RS agar visual peti kemas berpindah mulus!
            if (trk.pendingCargoSwap && !trk.cargoSwapDone && prog >= 0.5) {
                trk.cargoSwapDone = true;
                refreshTruckBoxVisual(trk, trk.pendingCargoSwap === 'load');
                state.totalBoxesHandled++;
                trk.pendingCargoSwap = null;
            }

            if (trk.pauseRemaining <= 0) {
                trk.progressRatio = null;
                trk.wpIndex = (trk.wpIndex + 1) % trk.waypoints.length;
                const nextWp = trk.waypoints[trk.wpIndex];
                if (nextWp && nextWp.badge) {
                    trk.badgeText = nextWp.badge;
                    trk.badgeTheme = nextWp.theme || 'blue';
                }
            }
            return;
        }

        trk.progressRatio = null;
        const pos = trk.mesh.position;
        const dx = wp.x - pos.x;
        const dz = wp.z - pos.z;
        const dist = Math.hypot(dx, dz);

        if (dist < 0.9) {
            pos.x = wp.x;
            pos.z = wp.z;
            trk.waitTimer = 0;
            const wrapped = executeWaypointTrigger(trk, wp);
            if (wrapped) return;
            if (wp.pause && wp.pause > 0) {
                trk.pauseRemaining = wp.pause;
            } else {
                trk.wpIndex = (trk.wpIndex + 1) % trk.waypoints.length;
                const nextWp = trk.waypoints[trk.wpIndex];
                if (nextWp && nextWp.badge) {
                    trk.badgeText = nextWp.badge;
                    trk.badgeTheme = nextWp.theme || 'blue';
                }
            }
            return;
        }

        const dirX = dx / dist;
        const dirZ = dz / dist;

        if (checkTruckAhead(trk, dirX, dirZ)) {
            trk.isWaitingQueue = true;
            trk.waitTimer = (trk.waitTimer || 0) + dt;
            if (trk.waitTimer > 3.2) {
                trk.waitTimer = 0;
                trk.cooldownMoveTimer = 2.0;
                trk.isWaitingQueue = false;
            } else {
                return;
            }
        }
        trk.isWaitingQueue = false;
        trk.waitTimer = 0;

        const targetRotY = Math.atan2(-dirZ, dirX);
        const angleDiff = wrapAngle(targetRotY - trk.mesh.rotation.y);
        const maxTurn = 4.8 * dt;
        if (Math.abs(angleDiff) <= maxTurn) trk.mesh.rotation.y = targetRotY;
        else trk.mesh.rotation.y = wrapAngle(trk.mesh.rotation.y + Math.sign(angleDiff) * maxTurn);

        const turnDamping = Math.abs(angleDiff) > 0.45 ? 0.55 : 1.0;
        const step = Math.min(dist, trk.speed * turnDamping * dt);
        pos.x += dirX * step;
        pos.z += dirZ * step;

        const wheelSpin = step / 0.52;
        for (let w = 0; w < trk.wheels.length; w++) {
            const wh = trk.wheels[w];
            if (wh.isGroup) wh.rotation.z -= wheelSpin;
            else wh.rotation.y -= wheelSpin;
        }
    }

    function updateHighwayCruisers(dt) {
        for (let i = 0; i < state.highwayCruisers.length; i++) {
            const c = state.highwayCruisers[i];
            const step = c.speed * dt * c.dir;
            c.mesh.position.x += step;
            if (c.dir > 0 && c.mesh.position.x > 265) c.mesh.position.x = -265;
            if (c.dir < 0 && c.mesh.position.x < -265) c.mesh.position.x = 265;
            const spin = Math.abs(step) / 0.52;
            for (let w = 0; w < c.wheels.length; w++) c.wheels[w].rotation.z -= spin;
        }
    }

    function updateForklifts(dt) {
        for (let i = 0; i < state.forklifts.length; i++) {
            const fl = state.forklifts[i];
            fl.phase = (fl.phase + dt) % fl.dur;
            const p = fl.phase / fl.dur;
            const wave = Math.sin(p * Math.PI * 2);
            const targetX = (fl.minX + fl.maxX) / 2 + wave * ((fl.maxX - fl.minX) / 2);
            fl.mesh.position.x = lerp(fl.mesh.position.x, targetX, dt * 4);
            fl.mesh.position.z = fl.fixedZ;
            const yaw = Math.cos(p * Math.PI * 2) >= 0 ? 0 : Math.PI;
            fl.mesh.rotation.y += wrapAngle(yaw - fl.mesh.rotation.y) * Math.min(1, dt * 6);

            const forks = fl.mesh.getObjectByName('flForks');
            const pallet = fl.mesh.getObjectByName('flPallet');
            if (forks) forks.position.y = (p > 0.15 && p < 0.45) ? 0.85 : 0.05;
            if (pallet) pallet.visible = (p > 0.05 && p < 0.58);
        }
    }

    function updateEquipmentAgent(eq, dt) {
        if (isManualActionRunning()) return;

        // ---------------------------------------------------------------------
        // MODE 1: AKTIF MEMBANTU TRUK PICK-UP / DROP-OFF SECARA REAL-TIME
        // ---------------------------------------------------------------------
        if (eq.assistTarget) {
            const as = eq.assistTarget;
            as.elapsed += dt;
            const k = Math.min(1, as.elapsed / as.duration);
            const tx = as.truck.mesh.position.x;
            const tz = as.truck.mesh.position.z;

            if (eq.type === 'rtg') {
                // RTG Lapangan (RTG-02..05 Blok A-D, RTG-01 Rail)
                eq.mesh.position.x = lerp(eq.mesh.position.x, tx, dt * 4.5);
                const trolley = eq.mesh.getObjectByName('rtgTrolley');
                const spreader = eq.mesh.getObjectByName('rtgSpreader');

                // Jalur truk di bawah RTG mengikuti koordinat lokal masing-masing blok (Utara: -16.0, Selatan: +16.0)
                const truckLocalZ = (eq.mode === 'yard') ? (tz - eq.mesh.position.z) : (eq.truckLocalZ || 3.8);
                const stackLocalZ = (typeof eq.stackLocalZ === 'number') ? eq.stackLocalZ : -3.5;

                let curLocalZ = truckLocalZ;
                let curSpreaderY = 12.5;
                let boxVis = false;

                if (as.mode === 'dropoff') {
                    // Bongkar dari truk -> angkat ke stack yard!
                    if (k < 0.25) {
                        curLocalZ = truckLocalZ;
                        curSpreaderY = lerp(12.5, 4.7, k / 0.25);
                        boxVis = false;
                        eq.badgeText = 'TURUN LOCK BOX TRUK';
                    } else if (k < 0.55) {
                        curLocalZ = truckLocalZ;
                        curSpreaderY = lerp(4.7, 13.0, (k - 0.25) / 0.30);
                        boxVis = true;
                        eq.badgeText = 'ANGKAT DARI TRUK';
                    } else if (k < 0.85) {
                        curLocalZ = lerp(truckLocalZ, stackLocalZ, (k - 0.55) / 0.30);
                        curSpreaderY = lerp(13.0, 5.5, (k - 0.55) / 0.30);
                        boxVis = true;
                        eq.badgeText = 'TUMPUK KE YARD';
                    } else {
                        curLocalZ = stackLocalZ;
                        curSpreaderY = lerp(5.5, 13.0, (k - 0.85) / 0.15);
                        boxVis = false;
                        eq.badgeText = 'LIFT-OFF SELESAI';
                    }
                } else {
                    // Pick-Up: Ambil dari stack yard -> turunkan tepat ke sasis truk!
                    if (k < 0.28) {
                        curLocalZ = stackLocalZ;
                        curSpreaderY = lerp(12.5, 5.5, k / 0.28);
                        boxVis = k > 0.20;
                        eq.badgeText = 'AMBIL DARI YARD';
                    } else if (k < 0.52) {
                        curLocalZ = lerp(stackLocalZ, truckLocalZ, (k - 0.28) / 0.24);
                        curSpreaderY = 13.0;
                        boxVis = true;
                        eq.badgeText = 'BAWA KE SASIS TRUK';
                    } else if (k < 0.82) {
                        curLocalZ = truckLocalZ;
                        curSpreaderY = lerp(13.0, 4.7, (k - 0.52) / 0.30);
                        boxVis = k < 0.72;
                        eq.badgeText = 'TURUNKAN KE TRUK';
                    } else {
                        curLocalZ = truckLocalZ;
                        curSpreaderY = lerp(4.7, 13.0, (k - 0.82) / 0.18);
                        boxVis = false;
                        eq.badgeText = 'LIFT-ON SELESAI';
                    }
                }

                if (trolley) trolley.position.z = lerp(trolley.position.z, curLocalZ, dt * 8);
                if (spreader) {
                    spreader.position.z = lerp(spreader.position.z, curLocalZ, dt * 8);
                    spreader.position.y = lerp(spreader.position.y, curSpreaderY, dt * 8);
                }
                if (eq.spreaderBox) eq.spreaderBox.visible = boxVis;
            } else if (eq.type === 'rs') {
                // Reach Stacker bergerak merapat & berputar menghadap truk yang menunggu!
                const boom = eq.mesh.getObjectByName('rsBoom');
                const targetX = eq.patrolZ ? eq.fixedX : tx;
                const targetZ = eq.patrolZ ? tz : eq.fixedZ;
                eq.mesh.position.x = lerp(eq.mesh.position.x, targetX, dt * 5);
                eq.mesh.position.z = lerp(eq.mesh.position.z, targetZ, dt * 5);

                const yawToTruck = eq.assistYaw !== undefined ? eq.assistYaw : eq.faceAngle;
                const targetYaw = (k < 0.55) ? yawToTruck : eq.faceAngle;
                eq.mesh.rotation.y += wrapAngle(targetYaw - eq.mesh.rotation.y) * Math.min(1, dt * 6);

                if (boom) {
                    const boomTarget = (k > 0.18 && k < 0.45) || (k > 0.65 && k < 0.90) ? Math.PI / 16 : Math.PI / 7.5;
                    boom.rotation.z = lerp(boom.rotation.z, boomTarget, dt * 6);
                }
                if (eq.spreaderBox) {
                    eq.spreaderBox.visible = (as.mode === 'dropoff') ? (k > 0.45 && k < 0.88) : (k > 0.12 && k < 0.55);
                }
                eq.badgeText = (as.mode === 'dropoff') ? 'BANTU BONGKAR TRUK' : 'BANTU MUAT TRUK';
            }

            if (as.elapsed >= as.duration) {
                eq.assistTarget = null;
                if (eq.spreaderBox) eq.spreaderBox.visible = false;
            }
            return;
        }

        // ---------------------------------------------------------------------
        // MODE 2: PATROLI & OPERASIONAL YARD MANDIRI SAAT TIDAK MELAYANI TRUK
        // ---------------------------------------------------------------------
        eq.phase = (eq.phase + dt) % eq.cycleDur;
        const p = eq.phase / eq.cycleDur;

        if (eq.type === 'rs') {
            const boom = eq.mesh.getObjectByName('rsBoom');
            if (eq.patrolZ) {
                const wave = Math.sin(p * Math.PI * 2);
                const targetZ = (eq.minZ + eq.maxZ) / 2 + wave * ((eq.maxZ - eq.minZ) / 2);
                eq.mesh.position.x = lerp(eq.mesh.position.x, eq.fixedX, dt * 4);
                eq.mesh.position.z = lerp(eq.mesh.position.z, targetZ, dt * 3);
                const travelYaw = Math.cos(p * Math.PI * 2) >= 0 ? -Math.PI / 2 : Math.PI / 2;
                const inStack = (p > 0.22 && p < 0.34) || (p > 0.72 && p < 0.84);
                const targetYaw = inStack ? eq.faceAngle : travelYaw;
                eq.mesh.rotation.y += wrapAngle(targetYaw - eq.mesh.rotation.y) * Math.min(1, dt * 3.8);
                if (boom) boom.rotation.z = lerp(boom.rotation.z, inStack ? Math.PI / 16 : Math.PI / 8.5, dt * 4);
                if (eq.spreaderBox) eq.spreaderBox.visible = (p > 0.28 && p < 0.76);
                eq.badgeText = inStack ? 'STACKING RACK' : 'PATROLI KORIDOR';
            } else {
                const wave = Math.sin(p * Math.PI * 2);
                const targetX = (eq.minX + eq.maxX) / 2 + wave * ((eq.maxX - eq.minX) / 2);
                eq.mesh.position.x = lerp(eq.mesh.position.x, targetX, dt * 3);
                eq.mesh.position.z = lerp(eq.mesh.position.z, eq.fixedZ, dt * 4);
                const inStack = (p > 0.20 && p < 0.34) || (p > 0.70 && p < 0.84);
                const travelYaw = Math.cos(p * Math.PI * 2) >= 0 ? 0 : Math.PI;
                const targetYaw = inStack ? eq.faceAngle : travelYaw;
                eq.mesh.rotation.y += wrapAngle(targetYaw - eq.mesh.rotation.y) * Math.min(1, dt * 3.8);
                if (boom) boom.rotation.z = lerp(boom.rotation.z, inStack ? Math.PI / 16 : Math.PI / 8.5, dt * 4);
                if (eq.spreaderBox) eq.spreaderBox.visible = (p > 0.26 && p < 0.76);
                eq.badgeText = inStack ? 'LIFTING TWISTLOCK' : 'STANDBY PATROLI';
            }
        } else if (eq.type === 'rtg') {
            const midX = (eq.minX + eq.maxX) / 2;
            const ampX = (eq.maxX - eq.minX) / 2;
            const targetX = midX + Math.sin(p * Math.PI * 2) * ampX;
            eq.mesh.position.x = lerp(eq.mesh.position.x, targetX, dt * 2.5);

            const trolley = eq.mesh.getObjectByName('rtgTrolley');
            const spreader = eq.mesh.getObjectByName('rtgSpreader');
            const zLow = (typeof eq.stackLocalZ === 'number') ? eq.stackLocalZ : -3.5;
            const zHigh = (typeof eq.truckLocalZ === 'number') ? eq.truckLocalZ : 3.8;

            let spreaderY = 12.8;
            let trolleyZ = 0;
            if (p < 0.25) {
                trolleyZ = zHigh;
                spreaderY = 5.4 + Math.abs(Math.cos(p / 0.25 * Math.PI)) * 6.5;
                if (eq.spreaderBox) eq.spreaderBox.visible = p > 0.14;
                eq.badgeText = eq.mode === 'yard' ? 'REHANDLE STACK' : 'AMBIL BOX KERETA';
            } else if (p < 0.55) {
                trolleyZ = lerp(zHigh, zLow, (p - 0.25) / 0.30);
                spreaderY = 13.0;
                if (eq.spreaderBox) eq.spreaderBox.visible = true;
                eq.badgeText = 'GESER TROLI RTG';
            } else if (p < 0.80) {
                trolleyZ = zLow;
                spreaderY = 5.4 + Math.abs(Math.cos((p - 0.55) / 0.25 * Math.PI)) * 6.8;
                if (eq.spreaderBox) eq.spreaderBox.visible = p < 0.68;
                eq.badgeText = 'TUMPUK BAY BARU';
            } else {
                trolleyZ = lerp(zLow, zHigh, (p - 0.80) / 0.20);
                spreaderY = 13.0;
                if (eq.spreaderBox) eq.spreaderBox.visible = false;
                eq.badgeText = 'GANTRY BERGESER';
            }
            if (trolley) trolley.position.z = lerp(trolley.position.z, trolleyZ, dt * 4);
            if (spreader) {
                spreader.position.z = lerp(spreader.position.z, trolleyZ, dt * 4);
                spreader.position.y = lerp(spreader.position.y, spreaderY, dt * 4);
            }
        }
    }

    function updateTrainAgent(dt) {
        const tr = state.trainAgent;
        if (!tr || !tr.mesh || isManualActionRunning()) return;
        tr.phase = (tr.phase + dt) % tr.cycleDur;
        const p = tr.phase / tr.cycleDur;
        let targetX = 5;
        if (p < 0.20) {
            targetX = lerp(-24, 24, p / 0.20);
            tr.badgeText = 'MASUK RAIL SIDING';
            tr.badgeTheme = 'amber';
        } else if (p < 0.58) {
            targetX = 24;
            tr.badgeText = 'KA 2518 • BONGKAR MUAT';
            tr.badgeTheme = 'emerald';
        } else if (p < 0.78) {
            targetX = lerp(24, -24, (p - 0.58) / 0.20);
            tr.badgeText = 'LANGSIR KEBERANGKATAN';
            tr.badgeTheme = 'rose';
        } else {
            targetX = -24;
            tr.badgeText = 'KA 2518 • SIAP JALAN';
            tr.badgeTheme = 'blue';
        }
        tr.mesh.position.x = lerp(tr.mesh.position.x, targetX, dt * 2.2);
    }

    function updateGatesAndEffects(dt, nowMs) {
        const gm = getGateMetrics();
        if (!isManualActionRunning()) {
            const laneMap = (typeof window !== 'undefined' && window.boomBarriers) ? window.boomBarriers : {};
            if (!state.barrierState.lanes) state.barrierState.lanes = {};

            for (let li = 0; li < gm.lanes.length; li++) {
                const lane = gm.lanes[li];
                let needed = false;
                for (let ti = 0; ti < state.trucks.length; ti++) {
                    const p = state.trucks[ti].mesh.position;
                    if (Math.abs(p.x - lane.x) < 4.0 && p.z >= gm.barrierZ - 8 && p.z <= gm.gateZ + 9) {
                        needed = true;
                        break;
                    }
                }
                const prev = state.barrierState.lanes[lane.code] || 0;
                const next = lerp(prev, needed ? 1 : 0, dt * 6.0);
                state.barrierState.lanes[lane.code] = next;

                const entry = laneMap[lane.code];
                if (entry && typeof setGateBarrierOpenRatio === 'function') {
                    entry.t = next;
                    setGateBarrierOpenRatio(entry, next);
                } else {
                    const isIn = (lane.direction === 'INBOUND');
                    if (lane.code === 'IN-01' && typeof boomBarrierMesh !== 'undefined' && boomBarrierMesh) {
                        const ang = next * 1.22;
                        boomBarrierMesh.rotation.x = 0;
                        boomBarrierMesh.rotation.z = (Math.PI / 2) - ang;
                        boomBarrierMesh.position.x = (lane.x - 2.2) + 2.2 * Math.cos(ang);
                        boomBarrierMesh.position.y = 1.2 + 2.2 * Math.sin(ang);
                    } else if (lane.code === 'OUT-01' && typeof outboundBarrierMesh !== 'undefined' && outboundBarrierMesh) {
                        const ang = next * 1.22;
                        outboundBarrierMesh.rotation.x = 0;
                        outboundBarrierMesh.rotation.z = (Math.PI / 2) + ang;
                        outboundBarrierMesh.position.x = (lane.x + 2.2) - 2.2 * Math.cos(ang);
                        outboundBarrierMesh.position.y = 1.2 + 2.2 * Math.sin(ang);
                    }
                    void isIn;
                }

                const ledMat = state.gateLeds.lanes && state.gateLeds.lanes[lane.code];
                if (ledMat) {
                    const busyColor = (lane.direction === 'INBOUND') ? 0xf59e0b : 0x38bdf8;
                    ledMat.color.setHex(needed ? busyColor : 0x22c55e);
                }
            }
        }

        const pulseBeam = (mesh, key) => {
            if (!mesh) return;
            if ((state.scanBeams[key] || 0) > 0) {
                state.scanBeams[key] = Math.max(0, state.scanBeams[key] - dt);
                mesh.material.opacity = 0.25 + 0.22 * Math.sin(nowMs * 0.02);
            } else {
                mesh.material.opacity = lerp(mesh.material.opacity, 0, dt * 6);
            }
        };
        pulseBeam(state.scanBeams.inGate,  'inGateFlash');
        pulseBeam(state.scanBeams.outGate, 'outGateFlash');
        pulseBeam(state.scanBeams.xray,    'xrayFlash');
        if (state.scanBeams.lanes) {
            Object.keys(state.scanBeams.lanes).forEach(code => {
                const key = 'lane_' + code;
                pulseBeam(state.scanBeams.lanes[code], key);
            });
        }

        for (let i = 0; i < state.bayHalos.length; i++) {
            const h = state.bayHalos[i];
            const s = 1 + 0.08 * Math.sin(nowMs * 0.004 + i);
            h.scale.set(s, s, 1);
            h.material.opacity = 0.25 + 0.2 * Math.sin(nowMs * 0.004 + i);
        }
    }

    // =========================================================================
    // 6. FLOATING SVG VECTOR BADGES & OPT-IN CAMERA TRACKING
    // =========================================================================
    // Pre-allocated Vectors & Cached Overlay Viewport Size (Zero Per-Frame GC / Layout Thrashing!)
    const _badgeWp = new THREE.Vector3();
    let _overlayEl = null;
    let _overlayW = 0;
    let _overlayH = 0;

    function initOverlayResizeCache() {
        _overlayEl = document.getElementById('trafficBillboardOverlay');
        if (!_overlayEl) return;
        const measure = () => {
            if (_overlayEl) {
                _overlayW = _overlayEl.clientWidth;
                _overlayH = _overlayEl.clientHeight;
            }
        };
        measure();
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(measure);
            ro.observe(_overlayEl);
        } else {
            window.addEventListener('resize', measure);
        }
    }

    function getWorldTrackingPoint(agent) {
        if (!agent || !agent.mesh) return null;
        agent.mesh.getWorldPosition(_badgeWp);
        if (agent.type === 'train') {
            _badgeWp.x += (agent.mesh.parent && agent.mesh.parent.scale.x < 0) ? 28 : -28;
            _badgeWp.y += 7.5;
        } else if (agent.type === 'rtg') {
            _badgeWp.y += 22.0;
        } else if (agent.type === 'rs') {
            _badgeWp.y += 8.8;
        } else if (agent.type === 'forklift') {
            _badgeWp.y += 4.2;
        } else {
            _badgeWp.y += 5.8;
        }
        return _badgeWp;
    }

    function ensureBadgeElement(agent) {
        if (state.badgeElements[agent.id]) return state.badgeElements[agent.id];
        if (!_overlayEl) _overlayEl = document.getElementById('trafficBillboardOverlay');
        if (!_overlayEl) return null;

        const svgIcon = VECTOR_ICONS[agent.iconType || agent.type] || VECTOR_ICONS.truck;
        const btn = document.createElement('button');
        btn.type = 'button';
        // Hapus backdrop-blur-md agar Chromium tidak melakukan 20 offscreen blur compositor pass tiap frame!
        btn.className = 'traffic-unit-badge absolute top-0 left-0 pointer-events-auto cursor-pointer select-none focus:outline-none will-change-transform';
        btn.title = 'Klik untuk mengikuti unit ini secara real-time';
        btn.innerHTML =
            '<div class="rounded-xl shadow-md px-2.5 py-1 text-[10px] leading-tight whitespace-nowrap flex flex-col gap-0.5">' +
                '<div class="flex items-center gap-1.5">' +
                    '<span class="w-1.5 h-1.5 rounded-full badge-dot shrink-0"></span>' +
                    svgIcon +
                    '<span class="font-extrabold font-mono tracking-tight badge-title"></span>' +
                    '<span class="opacity-60 hidden sm:inline">•</span>' +
                    '<span class="font-semibold text-[9.5px] opacity-95 hidden sm:inline badge-status"></span>' +
                '</div>' +
                '<div class="w-full h-0.5 bg-white/15 rounded-full overflow-hidden badge-prog-wrap hidden">' +
                    '<div class="h-full badge-prog-bar" style="width:0%"></div>' +
                '</div>' +
            '</div>';

        btn.addEventListener('click', function (ev) {
            ev.stopPropagation();
            if (state.followTargetId === agent.id) followAgent(null);
            else followAgent(agent.id);
        });

        // Cache child node references ONCE (hindari querySelector berulang di RAF loop!)
        btn._pill     = btn.firstElementChild;
        btn._dot      = btn.querySelector('.badge-dot');
        btn._title    = btn.querySelector('.badge-title');
        btn._status   = btn.querySelector('.badge-status');
        btn._progWrap = btn.querySelector('.badge-prog-wrap');
        btn._progBar  = btn.querySelector('.badge-prog-bar');
        btn._cache    = {};

        if (btn._title) btn._title.textContent = agent.label || agent.id;
        if (btn._status) btn._status.textContent = agent.badgeText || 'AKTIF';

        _overlayEl.appendChild(btn);
        state.badgeElements[agent.id] = btn;
        return btn;
    }

    function getAllTrackableAgents() {
        const list = state.trucks.concat(state.equipments).concat(state.forklifts);
        if (state.trainAgent) list.push(state.trainAgent);
        return list;
    }

    function updateFloatingBadges() {
        if (typeof camera === 'undefined' || !camera) return;
        if (!_overlayEl) _overlayEl = document.getElementById('trafficBillboardOverlay');
        if (!_overlayEl) return;

        if (!_overlayW || !_overlayH) {
            _overlayW = _overlayEl.clientWidth;
            _overlayH = _overlayEl.clientHeight;
            if (!_overlayW || !_overlayH) return;
        }

        const allAgents = getAllTrackableAgents();

        if (!state.showBadges) {
            const els = Object.values(state.badgeElements);
            for (let k = 0; k < els.length; k++) {
                const el = els[k];
                if (el._cache && el._cache.visible !== false) {
                    el.style.display = 'none';
                    el._cache.visible = false;
                }
            }
            return;
        }

        const w = _overlayW;
        const h = _overlayH;

        for (let i = 0; i < allAgents.length; i++) {
            const agent = allAgents[i];
            const el = ensureBadgeElement(agent);
            if (!el) continue;
            const c = el._cache;

            const wp = getWorldTrackingPoint(agent);
            if (!wp) {
                if (c.visible !== false) { el.style.display = 'none'; c.visible = false; }
                continue;
            }

            const camDist = camera.position.distanceTo(wp);
            wp.project(camera);

            if (wp.z > 1 || wp.x < -1.06 || wp.x > 1.06 || wp.y < -1.06 || wp.y > 1.06) {
                if (c.visible !== false) { el.style.display = 'none'; c.visible = false; }
                continue;
            }

            if (c.visible !== true) {
                el.style.display = 'block';
                c.visible = true;
            }

            const sx = (wp.x * 0.5 + 0.5) * w;
            const sy = (-wp.y * 0.5 + 0.5) * h;
            const scale = Math.max(0.74, Math.min(1.00, 1.15 - camDist * 0.0014));
            const rx = Math.round(sx * 2) / 2;
            const ry = Math.round(sy * 2) / 2;
            const rs = Math.round(scale * 100) / 100;

            if (c.rx !== rx || c.ry !== ry || c.rs !== rs) {
                c.rx = rx; c.ry = ry; c.rs = rs;
                el.style.transform = `translate3d(${rx}px, ${ry}px, 0) translate(-50%, -100%) scale(${rs})`;
            }

            const isFollow = (state.followTargetId === agent.id);
            const zIdx = isFollow ? '9' : String(Math.max(1, Math.min(8, Math.round(9 - camDist * 0.015))));
            if (c.zIdx !== zIdx) {
                c.zIdx = zIdx;
                el.style.zIndex = zIdx;
            }

            const themeKey = agent.badgeTheme || 'blue';
            if (c.themeKey !== themeKey || c.isFollow !== isFollow) {
                c.themeKey = themeKey;
                c.isFollow = isFollow;
                const theme = BADGE_THEMES[themeKey] || BADGE_THEMES.blue;
                el._pill.style.background = theme.bg;
                el._pill.style.border = '1px solid ' + (isFollow ? '#ffffff' : theme.border);
                el._pill.style.color = theme.text;
                el._pill.style.boxShadow = isFollow ? '0 0 12px rgba(56,189,248,0.8)' : '0 2px 6px rgba(2,6,23,0.35)';
                if (el._dot) el._dot.style.backgroundColor = theme.dot;
                if (el._progBar) el._progBar.style.backgroundColor = theme.bar;
            }

            const statusTxt = agent.isWaitingQueue ? 'ANTRI LAJUR' : agent.badgeText;
            if (c.statusTxt !== statusTxt && el._status) {
                c.statusTxt = statusTxt;
                el._status.textContent = statusTxt;
            }

            const hasProg = typeof agent.progressRatio === 'number';
            const progPct = hasProg ? Math.round(agent.progressRatio * 100) : -1;
            if (c.progPct !== progPct && el._progWrap && el._progBar) {
                c.progPct = progPct;
                if (hasProg) {
                    if (c.progHidden !== false) { el._progWrap.classList.remove('hidden'); c.progHidden = false; }
                    el._progBar.style.width = progPct + '%';
                } else if (c.progHidden !== true) {
                    el._progWrap.classList.add('hidden');
                    c.progHidden = true;
                }
            }
        }
    }

    function updateOptInCameraFollow() {
        if (!state.followTargetId || typeof controls === 'undefined' || !controls || typeof camera === 'undefined') {
            if (state.followRing) state.followRing.visible = false;
            return;
        }

        const allAgents = getAllTrackableAgents();
        const target = allAgents.find(a => a.id === state.followTargetId);
        if (!target || !target.mesh) { followAgent(null); return; }

        const wp = new THREE.Vector3();
        target.mesh.getWorldPosition(wp);
        if (target.type === 'train') wp.x += (target.mesh.parent && target.mesh.parent.scale.x < 0) ? 28 : -28;

        if (state.followRing) {
            state.followRing.visible = true;
            state.followRing.position.set(wp.x, 0.25, wp.z);
            state.followRing.rotation.z += 0.025;
        }

        if (state.lastFollowPos) {
            const dx = wp.x - state.lastFollowPos.x;
            const dz = wp.z - state.lastFollowPos.z;
            if (Math.hypot(dx, dz) > 45) {
                controls.target.set(wp.x, 2, wp.z);
                camera.position.set(wp.x + 28, 22, wp.z - 32);
            } else {
                controls.target.x += dx;
                controls.target.z += dz;
                camera.position.x += dx;
                camera.position.z += dz;
            }
        }
        state.lastFollowPos = { x: wp.x, z: wp.z };
    }

    function updateToolbarMetrics() {
        const now = performance.now();
        if (now - state.lastKpiUpdateAt < 350) return;
        state.lastKpiUpdateAt = now;

        const inEl = document.getElementById('trafficGateInCount');
        if (inEl) inEl.textContent = String(state.totalGateInCount);
        const outEl = document.getElementById('trafficGateOutCount');
        if (outEl) outEl.textContent = String(state.totalGateOutCount);
        const loopsEl = document.getElementById('trafficLoopCount');
        if (loopsEl) loopsEl.textContent = String(state.totalCompletedLoops);
        const boxesEl = document.getElementById('trafficBoxCount');
        if (boxesEl) boxesEl.textContent = String(state.totalBoxesHandled);
    }

    function syncControlButtonsUI() {
        const playBtn = document.getElementById('btnTrafficPlayPause');
        if (playBtn) {
            playBtn.innerHTML = state.running
                ? '<i class="fa-solid fa-pause text-xs"></i><span>Jeda Traffic</span>'
                : '<i class="fa-solid fa-play text-xs"></i><span>Mulai Traffic</span>';
            playBtn.className = state.running
                ? 'px-2.5 py-1.5 text-xs rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-xs cursor-pointer'
                : 'px-2.5 py-1.5 text-xs rounded-xl font-bold bg-amber-500 text-white hover:bg-amber-600 transition flex items-center gap-1.5 shadow-xs cursor-pointer';
        }

        const speedBtn = document.getElementById('trafficSpeedLabel');
        if (speedBtn) speedBtn.textContent = state.speedMultiplier + 'x';

        const badgeBtn = document.getElementById('btnToggleTrafficBadges');
        if (badgeBtn) {
            badgeBtn.className = state.showBadges
                ? 'px-2.5 py-1.5 text-xs rounded-xl font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200 hover:bg-cyan-100 transition flex items-center gap-1.5 cursor-pointer'
                : 'px-2.5 py-1.5 text-xs rounded-xl font-semibold bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200 transition flex items-center gap-1.5 cursor-pointer';
        }

        document.querySelectorAll('.traffic-focus-pill').forEach(btn => {
            const target = btn.getAttribute('data-target') || '';
            const active = (target === '' && state.followTargetId === null) || (target === state.followTargetId);
            btn.className = active
                ? 'traffic-focus-pill px-2.5 py-1 text-[11px] rounded-lg font-bold bg-[#0170b9] text-white shadow-2xs transition cursor-pointer flex items-center gap-1'
                : 'traffic-focus-pill px-2.5 py-1 text-[11px] rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition cursor-pointer flex items-center gap-1';
        });
    }

    function followAgent(id) {
        state.followTargetId = id || null;
        state.lastFollowPos = null;

        if (!id) {
            if (state.followRing) state.followRing.visible = false;
            syncControlButtonsUI();
            return;
        }

        const allAgents = getAllTrackableAgents();
        const agent = allAgents.find(a => a.id === id);
        if (agent && agent.mesh && typeof camera !== 'undefined' && typeof controls !== 'undefined' && typeof TWEEN !== 'undefined') {
            const wp = new THREE.Vector3();
            agent.mesh.getWorldPosition(wp);
            if (agent.type === 'train') wp.x += (agent.mesh.parent && agent.mesh.parent.scale.x < 0) ? 28 : -28;
            state.lastFollowPos = { x: wp.x, z: wp.z };

            const offset = agent.type === 'rtg' || agent.type === 'train'
                ? { x: 38, y: 34, z: -42 }
                : { x: 26, y: 20, z: -30 };

            new TWEEN.Tween(controls.target).to({ x: wp.x, y: 2, z: wp.z }, 650).easing(TWEEN.Easing.Cubic.Out).start();
            new TWEEN.Tween(camera.position).to({ x: wp.x + offset.x, y: offset.y, z: wp.z + offset.z }, 650).easing(TWEEN.Easing.Cubic.Out).start();
            emitTrafficLog('[KAMERA] Fokus mengikuti ' + agent.label + ' (Klik "Bebas" untuk melepas).', true);
        }
        syncControlButtonsUI();
    }

    const CIDPTrafficEngine = {
        version: TRAFFIC_VERSION,

        init: function () {
            if (state.initialized) return;
            if (typeof scene === 'undefined' || !scene) return;

            buildEnhancedDryPortMap();
            buildFleetAgents();
            buildEquipmentAndYardAgents();
            initOverlayResizeCache();

            state.initialized = true;
            state.lastTimeMs = performance.now();
            syncControlButtonsUI();
            emitTrafficLog('[TRAFFIC v4.1] Peta 480m x 296m • Plaza 10 Lajur Gate (6 In / 4 Out) aktif: 5 E-RTG + 4 RS + 8 Truk kontinu.', true);
        },

        update: function (timeMs) {
            if (!state.initialized) return;

            const now = timeMs || performance.now();
            const rawDt = Math.min(0.05, Math.max(0.001, (now - (state.lastTimeMs || now)) * 0.001));
            state.lastTimeMs = now;

            const manualActive = isManualActionRunning();
            if (state.wasManualAnimating && !manualActive) {
                const primary = state.trucks.find(t => t.isPrimaryTruck);
                if (primary) {
                    primary.wpIndex = 1;
                    primary.pauseRemaining = 0;
                    refreshTruckBoxVisual(primary, true);
                }
            }
            state.wasManualAnimating = manualActive;

            if (state.running) {
                const dt = rawDt * state.speedMultiplier;
                for (let i = 0; i < state.trucks.length; i++) updateTruckAgent(state.trucks[i], dt);
                updateHighwayCruisers(dt);
                updateForklifts(dt);
                for (let j = 0; j < state.equipments.length; j++) updateEquipmentAgent(state.equipments[j], dt);
                updateTrainAgent(dt);
                updateGatesAndEffects(dt, now);
            }

            updateOptInCameraFollow();
            updateFloatingBadges();
            updateToolbarMetrics();
        },

        togglePlay: function () {
            state.running = !state.running;
            syncControlButtonsUI();
            emitTrafficLog(state.running ? '[TRAFFIC ENGINE] Sirkulasi dilanjutkan.' : '[TRAFFIC ENGINE] Sirkulasi dijeda.', true);
        },

        cycleSpeed: function () {
            const speeds = [0.5, 1, 2, 3];
            const idx = speeds.indexOf(state.speedMultiplier);
            state.speedMultiplier = speeds[(idx + 1) % speeds.length];
            syncControlButtonsUI();
        },

        toggleBadges: function () {
            state.showBadges = !state.showBadges;
            syncControlButtonsUI();
        },

        followAgent: followAgent
    };

    global.CIDPTrafficEngine = CIDPTrafficEngine;
})(window);
