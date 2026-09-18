<?php
// Pastikan file ini di-include dari dashboard.php, bukan diakses langsung
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
?>
<style>
    /* Styling khusus untuk Denah Terminal */
    .denah-layout { display: flex; gap: 1rem; height: calc(100vh - 140px); min-height: 600px; }
    .map-container { flex: 3; display: flex; flex-direction: column; background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; overflow: hidden; }
    .panel-container { flex: 1; display: flex; flex-direction: column; gap: 1rem; overflow-y: auto; }
    
    .map-header { padding: 1rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
    .map-wrapper { flex: 1; overflow: auto; background-color: #e5e7eb; position: relative; cursor: grab; }
    .map-wrapper:active { cursor: grabbing; }
    
    /* Yard Map Base */
    .yard-map { width: 1200px; height: 750px; background-color: #f4f8fc; position: relative; transform-origin: 0 0; transition: transform 0.2s ease; box-sizing: border-box; }
    
    /* Sections */
    .yard-section { border: 2px dashed #9ca3af; border-radius: 8px; position: absolute; background: white; padding: 10px; display: flex; flex-direction: column; }
    .section-title { font-size: 12px; font-weight: bold; color: #002f5e; margin-bottom: 8px; text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
    
    /* Grid & Slots */
    .block-grid { display: grid; gap: 4px; align-self: center; }
    .yard-slot { width: 30px; height: 22px; border: 1px solid #d1d5db; border-radius: 3px; cursor: pointer; transition: all 0.2s; position: relative; background: #f9fafb; display: flex; align-items: center; justify-content: center; }
    .yard-slot:hover { border-color: #0170b9; box-shadow: 0 0 0 2px rgba(1, 112, 185, 0.2); z-index: 10; }
    .yard-slot.highlight-move { background: #dcfce7; border-color: #22c55e; animation: pulse-green 1.5s infinite; cursor: crosshair; }
    
    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    /* Containers */
    .container-box { width: 100%; height: 100%; border-radius: 2px; display: flex; align-items: center; justify-content: center; font-size: 7px; font-weight: bold; color: white; cursor: pointer; overflow: hidden; white-space: nowrap; position: absolute; top: 0; left: 0; transition: transform 0.3s ease; }
    .container-box:hover { transform: scale(1.15); z-index: 20; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
    .container-box.dry { background: #3b82f6; } /* Blue */
    .container-box.reefer { background: #06b6d4; } /* Cyan */
    .container-box.dg { background: #ef4444; } /* Red */
    .container-box.empty-type { background: #9ca3af; } /* Gray */
    .container-box.selected { box-shadow: 0 0 0 3px #f59e0b; z-index: 30; }
    
    /* Equipment / Reach Stacker */
    .reach-stacker { position: absolute; transition: all 1.5s ease-in-out; z-index: 40; pointer-events: none; margin-left: -18px; margin-top: -18px; }
    .rs-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.2); border: 2px solid white; }
    .rs-icon.active { animation: pulse-orange 2s infinite; }
    .rs-icon.bg-rs { background: #f97316; } /* Orange */
    .rs-icon.bg-rtg { background: #8b5cf6; } /* Purple */
    
    @keyframes pulse-orange {
        0% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(249, 115, 22, 0); }
        100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
    }
    
    /* Absolute Positioning of Yard Sections */
    #gate-area { top: 20px; left: 20px; width: 1160px; height: 60px; flex-direction: row; justify-content: space-between; align-items: center; background: #f3f4f6; }
    #weighbridge { top: 90px; left: 20px; width: 1160px; height: 40px; justify-content: center; align-items: center; background: #e5e7eb; }
    
    #block-A { top: 140px; left: 20px; width: 280px; height: 160px; }
    #block-B { top: 140px; left: 310px; width: 280px; height: 160px; }
    #block-C { top: 140px; left: 600px; width: 280px; height: 160px; }
    #block-R { top: 140px; left: 890px; width: 290px; height: 160px; background: #ecfeff; border-color: #06b6d4; }
    
    #block-D { top: 310px; left: 20px; width: 280px; height: 160px; }
    #block-DG { top: 310px; left: 890px; width: 290px; height: 160px; background: #fef2f2; border-color: #ef4444; }
    
    #rail-siding { top: 480px; left: 20px; width: 1160px; height: 90px; flex-direction: row; justify-content: space-around; align-items: center; background: #f3f4f6; border: 2px solid #6b7280; }
    #empty-depot { top: 580px; left: 20px; width: 1160px; height: 140px; }
    
    /* Rail train cars */
    .train-car { width: 120px; height: 40px; background: #4b5563; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold; }
    
    /* Scrollbar for Map Wrapper */
    .map-wrapper::-webkit-scrollbar { width: 10px; height: 10px; }
    .map-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
    .map-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 5px; }
    .map-wrapper::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-[#002f5e]">Denah Terminal (Yard Layout)</h2>
        <p class="text-gray-500 text-sm mt-1">Pemantauan visual waktu-nyata posisi kontainer dan alat berat</p>
    </div>
    <div class="flex gap-2">
        <button onclick="refreshData()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50 text-sm font-medium">
            <i class="fa-solid fa-sync mr-2"></i> Segarkan
        </button>
    </div>
</div>

<div class="denah-layout">
    <!-- Map Area (Left) -->
    <div class="map-container">
        <div class="map-header">
            <div class="flex gap-4 text-xs font-medium text-gray-600 items-center">
                <span class="font-bold text-[#002f5e] mr-2">Legenda:</span>
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-500 rounded-sm"></span> Dry</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-cyan-500 rounded-sm"></span> Reefer</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded-sm"></span> DG</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-gray-400 rounded-sm"></span> Empty</div>
                <div class="flex items-center gap-1 ml-4"><span class="w-3 h-3 bg-orange-500 rounded-full border border-white"></span> Reach Stacker</div>
            </div>
            <div class="flex gap-2">
                <button onclick="setZoom(zoomLevel - 0.1)" class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 rounded hover:bg-gray-100"><i class="fa-solid fa-minus"></i></button>
                <button onclick="setZoom(1)" class="px-3 h-8 flex items-center justify-center bg-white border border-gray-300 rounded hover:bg-gray-100 text-xs font-bold" id="zoom-text">100%</button>
                <button onclick="setZoom(zoomLevel + 0.1)" class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 rounded hover:bg-gray-100"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
        
        <div class="map-wrapper" id="map-wrapper">
            <div class="yard-map" id="yard-map">
                <!-- Static Areas -->
                <div class="yard-section" id="gate-area">
                    <div class="font-bold text-[#002f5e]"><i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> GATE IN</div>
                    <div class="text-xs text-gray-500">Jalur Truk Masuk & Keluar</div>
                    <div class="font-bold text-[#002f5e]">GATE OUT <i class="fa-solid fa-arrow-right-from-bracket ml-2"></i></div>
                </div>
                
                <div class="yard-section" id="weighbridge">
                    <div class="font-bold text-gray-700 text-sm"><i class="fa-solid fa-scale-balanced mr-2"></i> WEIGHBRIDGE (Jembatan Timbang)</div>
                </div>
                
                <!-- Blocks will be generated by JS -->
                <div class="yard-section" id="block-A">
                    <div class="section-title">BLOCK A (Dry)</div>
                </div>
                <div class="yard-section" id="block-B">
                    <div class="section-title">BLOCK B (Dry)</div>
                </div>
                <div class="yard-section" id="block-C">
                    <div class="section-title">BLOCK C (Dry)</div>
                </div>
                <div class="yard-section" id="block-R">
                    <div class="section-title text-cyan-700"><i class="fa-solid fa-snowflake mr-1"></i> REEFER ZONE</div>
                </div>
                <div class="yard-section" id="block-D">
                    <div class="section-title">BLOCK D (Dry)</div>
                </div>
                <div class="yard-section" id="block-DG">
                    <div class="section-title text-red-700"><i class="fa-solid fa-triangle-exclamation mr-1"></i> DG YARD (Bahan Berbahaya)</div>
                </div>
                
                <div class="yard-section" id="rail-siding">
                    <div class="absolute top-1 left-2 text-xs font-bold text-gray-600">RAIL SIDING (Jalur Kereta Api)</div>
                    <div class="w-full flex justify-around mt-4">
                        <div class="train-car">Gerbong 1</div>
                        <div class="train-car">Gerbong 2</div>
                        <div class="train-car">Gerbong 3</div>
                        <div class="train-car">Gerbong 4</div>
                        <div class="train-car">Gerbong 5</div>
                    </div>
                </div>
                
                <div class="yard-section" id="empty-depot">
                    <div class="section-title">EMPTY CONTAINER DEPOT & M&R</div>
                    <!-- Empty container grid -->
                </div>
                
                <!-- Equipment Overlay Layer -->
                <div id="equipment-layer"></div>
            </div>
        </div>
    </div>
    
    <!-- Right Panel -->
    <div class="panel-container">
        <!-- Panel Detail Kontainer -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hidden" id="detail-panel">
            <div class="bg-[#002f5e] text-white p-3 flex justify-between items-center">
                <h3 class="font-bold text-sm" id="detail-title">Detail Kontainer</h3>
                <button onclick="closeDetail()" class="text-white hover:text-gray-300"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-xl font-black text-gray-800" id="detail-no">MSKU9182374</h4>
                        <span class="inline-block px-2 py-1 rounded text-xs font-bold mt-1" id="detail-badge">Dry - 40ft</span>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Posisi Saat Ini</div>
                        <div class="font-bold text-lg text-[#0170b9]" id="detail-pos">B-08-03-02</div>
                    </div>
                </div>
                
                <div class="space-y-3 text-sm border-t border-gray-100 pt-3 mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Berat (VGM)</span>
                        <span class="font-medium" id="detail-weight">26.850 kg</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pemilik / Agen</span>
                        <span class="font-medium" id="detail-owner">PT Samudera Logistik</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Tag RFID UHF</span>
                        <span class="font-mono text-[11px] text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.5 rounded font-bold" id="detail-rfid">E280117000000001</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">SSCC-18 (GS1)</span>
                        <span class="font-mono text-[11px] text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded" id="detail-sscc">(00)389912345000000001</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Waktu Masuk</span>
                        <span class="font-medium">18 Sep 2026, 14:32</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Dwell Time</span>
                        <span class="font-medium text-orange-600 font-bold">1 hari 2 jam</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status Bea Cukai</span>
                        <span class="font-medium text-green-600"><i class="fa-solid fa-check-circle mr-1"></i> SPPB Cleared</span>
                    </div>
                </div>
                
                <?php if($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'staf'): ?>
                <div class="border-t border-gray-100 pt-4" id="action-area">
                    <button id="btn-move-mode" onclick="toggleMoveMode()" class="w-full bg-[#0170b9] hover:bg-[#004b87] text-white py-2 rounded-md font-medium transition-colors text-sm mb-2">
                        <i class="fa-solid fa-dolly mr-2"></i> Pindahkan Kontainer
                    </button>
                    
                    <div id="move-form" class="hidden bg-gray-50 p-3 rounded border border-gray-200 mt-2">
                        <p class="text-xs text-gray-600 mb-2 font-medium">Klik slot kosong berkedip hijau pada peta untuk memindahkan, atau batalkan.</p>
                        
                        <div class="mb-3">
                            <label class="block text-xs text-gray-500 mb-1">Pilih Alat Berat (Eksekutor)</label>
                            <select id="select-equipment" class="w-full text-sm border-gray-300 rounded p-1.5 focus:ring-[#0170b9] focus:border-[#0170b9]">
                                <option value="RS-01">RS-01 (Budi S.)</option>
                                <option value="RS-02">RS-02 (Ahmad T.)</option>
                                <option value="RS-03">RS-03 (Joko W.)</option>
                                <option value="RTG-01">RTG-01 (Otomatis)</option>
                            </select>
                        </div>
                        
                        <div class="flex gap-2">
                            <button onclick="toggleMoveMode()" class="flex-1 bg-white border border-gray-300 text-gray-700 py-1.5 rounded text-xs font-medium hover:bg-gray-50">Batal</button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Panel Alat Berat (Muncul saat hover/klik RS) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hidden" id="eq-panel">
             <div class="bg-orange-500 text-white p-3 flex justify-between items-center">
                <h3 class="font-bold text-sm">Status Alat Berat</h3>
                <button onclick="closeEqPanel()" class="text-white hover:text-orange-200"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="p-4 text-sm">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-lg font-black" id="eq-id">RS-01</h4>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Aktif</span>
                </div>
                <div class="space-y-2 text-gray-600">
                    <p><i class="fa-solid fa-user w-5"></i> Operator: <span class="font-medium text-gray-900" id="eq-op">Budi S.</span></p>
                    <p><i class="fa-solid fa-gas-pump w-5"></i> Bahan Bakar: <span class="font-medium text-gray-900">78%</span></p>
                    <p><i class="fa-solid fa-stopwatch w-5"></i> Jam Kerja: <span class="font-medium text-gray-900">4j 15m</span></p>
                    <p><i class="fa-solid fa-tasks w-5"></i> Tugas Aktif: <span class="font-medium text-gray-900" id="eq-task">Standby</span></p>
                </div>
            </div>
        </div>

        <!-- Event Log -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex-1 flex flex-col">
            <div class="bg-gray-50 border-b border-gray-200 p-3">
                <h3 class="font-bold text-sm text-gray-700"><i class="fa-solid fa-list-ul mr-2"></i> Aktivitas Terbaru</h3>
            </div>
            <div class="p-0 overflow-y-auto flex-1">
                <ul class="divide-y divide-gray-100 text-sm" id="event-log">
                    <li class="p-3 hover:bg-gray-50">
                        <div class="flex justify-between mb-1">
                            <span class="font-medium text-[#002f5e]">TRX-8821 Masuk</span>
                            <span class="text-xs text-gray-500">Baru saja</span>
                        </div>
                        <p class="text-xs text-gray-600">Truk B 9123 TE memuat kontainer TGHU8123491 (Gate In)</p>
                    </li>
                    <li class="p-3 hover:bg-gray-50">
                        <div class="flex justify-between mb-1">
                            <span class="font-medium text-green-600">Move Selesai</span>
                            <span class="text-xs text-gray-500">2 mnt lalu</span>
                        </div>
                        <p class="text-xs text-gray-600">RS-02 memindahkan MSCU7718234 dari B-01-01-01 ke Truk.</p>
                    </li>
                    <li class="p-3 hover:bg-gray-50">
                        <div class="flex justify-between mb-1">
                            <span class="font-medium text-orange-500">Pemeriksaan SPPB</span>
                            <span class="text-xs text-gray-500">15 mnt lalu</span>
                        </div>
                        <p class="text-xs text-gray-600">Kontainer DG HLXU1192837 dokumen clearance disetujui.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pindah -->
<div id="modal-confirm-move" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-[400px] overflow-hidden">
        <div class="bg-[#002f5e] text-white p-4">
            <h3 class="font-bold"><i class="fa-solid fa-exclamation-circle mr-2"></i> Konfirmasi Pergerakan</h3>
        </div>
        <div class="p-4">
            <p class="text-sm text-gray-600 mb-4">Anda akan menjadwalkan pergerakan kontainer berikut:</p>
            <div class="bg-gray-50 p-3 rounded border border-gray-200 mb-4 text-sm space-y-2">
                <p><strong>Kontainer:</strong> <span id="confirm-no" class="text-[#0170b9]"></span></p>
                <p><strong>Dari:</strong> <span id="confirm-from"></span></p>
                <p><strong>Ke:</strong> <span id="confirm-to" class="text-green-600 font-bold"></span></p>
                <p><strong>Eksekutor:</strong> <span id="confirm-eq"></span></p>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button onclick="cancelMove()" class="px-4 py-2 bg-white border border-gray-300 rounded text-gray-700 text-sm font-medium hover:bg-gray-50">Batal</button>
                <button onclick="executeMove()" class="px-4 py-2 bg-[#0170b9] text-white rounded text-sm font-medium hover:bg-[#004b87]">Jalankan (Kirim ke RS)</button>
            </div>
        </div>
    </div>
</div>

<!-- Script Logika Interactive Yard -->
<script>
    // State Aplikasi
    let zoomLevel = 1;
    let selectedContainer = null;
    let isMoveMode = false;
    let yardData = [];
    let equipmentData = [];
    
    // Konfigurasi Yard
    const YARD_CONFIG = {
        'A': { bays: 8, rows: 4, maxTier: 3 },
        'B': { bays: 8, rows: 4, maxTier: 3 },
        'C': { bays: 8, rows: 4, maxTier: 3 },
        'R': { bays: 8, rows: 2, maxTier: 2 }, // Reefer
        'D': { bays: 6, rows: 4, maxTier: 3 },
        'DG': { bays: 8, rows: 2, maxTier: 2 } // DG
    };

    // Zoom Controls
    const mapWrapper = document.getElementById('map-wrapper');
    const yardMap = document.getElementById('yard-map');
    
    function setZoom(level) {
        zoomLevel = Math.max(0.5, Math.min(level, 2));
        yardMap.style.transform = `scale(${zoomLevel})`;
        document.getElementById('zoom-text').innerText = Math.round(zoomLevel * 100) + '%';
    }

    // Panning (Drag Map)
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;

    mapWrapper.addEventListener('mousedown', (e) => {
        if(e.target.closest('.yard-slot') || e.target.closest('.container-box') || e.target.closest('.reach-stacker')) return;
        isDragging = true;
        startX = e.pageX - mapWrapper.offsetLeft;
        startY = e.pageY - mapWrapper.offsetTop;
        scrollLeft = mapWrapper.scrollLeft;
        scrollTop = mapWrapper.scrollTop;
    });

    mapWrapper.addEventListener('mouseleave', () => isDragging = false);
    mapWrapper.addEventListener('mouseup', () => isDragging = false);
    mapWrapper.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - mapWrapper.offsetLeft;
        const y = e.pageY - mapWrapper.offsetTop;
        mapWrapper.scrollLeft = scrollLeft - (x - startX);
        mapWrapper.scrollTop = scrollTop - (y - startY);
    });

    // Generate Yard Grid HTML
    function buildYardGrid() {
        for (const [blockId, config] of Object.entries(YARD_CONFIG)) {
            const blockEl = document.getElementById(`block-${blockId}`);
            if (!blockEl) continue;
            
            const gridEl = document.createElement('div');
            gridEl.className = 'block-grid';
            gridEl.style.gridTemplateColumns = `repeat(${config.bays}, 1fr)`;
            
            // Generate slots (rows * bays)
            for (let r = 1; r <= config.rows; r++) {
                for (let b = 1; b <= config.bays; b++) {
                    const bayStr = b.toString().padStart(2, '0');
                    const rowStr = r.toString().padStart(2, '0');
                    const slotId = `${blockId}-${bayStr}-${rowStr}`;
                    
                    const slot = document.createElement('div');
                    slot.className = 'yard-slot';
                    slot.id = `slot-${slotId}`;
                    slot.dataset.block = blockId;
                    slot.dataset.bay = bayStr;
                    slot.dataset.row = rowStr;
                    
                    // Indikator Tier via title tooltip sementara
                    slot.title = `Slot ${slotId} (Tier: 0/${config.maxTier})`;
                    
                    slot.addEventListener('click', (e) => handleSlotClick(slot, e));
                    
                    gridEl.appendChild(slot);
                }
            }
            blockEl.appendChild(gridEl);
        }
        
        // Empty depot (simpel grid)
        const emptyDepot = document.getElementById('empty-depot');
        const emptyGrid = document.createElement('div');
        emptyGrid.className = 'block-grid mt-2';
        emptyGrid.style.gridTemplateColumns = `repeat(30, 1fr)`;
        for(let i=1; i<=60; i++) {
            const slot = document.createElement('div');
            slot.className = 'yard-slot';
            slot.style.width = '24px';
            slot.style.height = '18px';
            emptyGrid.appendChild(slot);
        }
        emptyDepot.appendChild(emptyGrid);
    }

    // Dummy Data Generator (Simulasi API)
    function generateDummyData() {
        const types = [
            { t: 'dry', c: 'blue-500', name: '20ft Dry' },
            { t: 'dry', c: 'blue-500', name: '40ft HC' },
            { t: 'reefer', c: 'cyan-500', name: '40ft Reefer' },
            { t: 'dg', c: 'red-500', name: '20ft DG (Class 3)' }
        ];
        
        const data = [];
        let idCounter = 1;
        
        // Populate random slots
        for (const [blockId, config] of Object.entries(YARD_CONFIG)) {
            // Fill rate approx 40%
            for (let r = 1; r <= config.rows; r++) {
                for (let b = 1; b <= config.bays; b++) {
                    if (Math.random() > 0.6) {
                        const tier = Math.floor(Math.random() * config.maxTier) + 1;
                        let typeObj = types[0];
                        if(blockId === 'R') typeObj = types[2];
                        else if(blockId === 'DG') typeObj = types[3];
                        else typeObj = types[Math.floor(Math.random() * 2)];
                        
                        const prefix = ['MSKU','TGHU','CMAU','HLXU','MEDU'][Math.floor(Math.random()*5)];
                        const no = `${prefix}${Math.floor(1000000 + Math.random() * 8999999)}`;
                        
                        data.push({
                            id: idCounter++,
                            no: no,
                            block: blockId,
                            bay: b.toString().padStart(2, '0'),
                            row: r.toString().padStart(2, '0'),
                            tier: tier,
                            type: typeObj.t,
                            typeName: typeObj.name,
                            weight: Math.floor(15000 + Math.random() * 15000) + ' kg',
                            dwell: Math.floor(Math.random() * 5) + ' hari ' + Math.floor(Math.random() * 24) + ' jam'
                        });
                    }
                }
            }
        }
        return data;
    }

    // Render Containers to Grid
    function renderContainers() {
        // Clear old containers
        document.querySelectorAll('.container-box').forEach(el => el.remove());
        
        yardData.forEach(c => {
            const slotId = `slot-${c.block}-${c.bay}-${c.row}`;
            const slot = document.getElementById(slotId);
            if(slot) {
                // Update slot tooltip
                slot.title = `Slot ${c.block}-${c.bay}-${c.row} (Tier: ${c.tier})`;
                
                const box = document.createElement('div');
                box.className = `container-box ${c.type}`;
                box.dataset.id = c.id;
                box.innerText = c.no.substring(0,4) + '...';
                
                // Z-index based on tier to simulate stacking if needed, here we just show top tier
                // In a real 3D top-down, we only see the highest tier.
                // We'll append it. If multiple containers are in same bay-row, the last one (highest tier) covers.
                
                if(selectedContainer && selectedContainer.id === c.id) {
                    box.classList.add('selected');
                }
                
                slot.appendChild(box);
            }
        });
    }

    // Handle Slot / Container Click
    function handleSlotClick(slotEl, event) {
        const box = slotEl.querySelector('.container-box');
        
        if (isMoveMode) {
            // Destinasi klik
            if (box && box.dataset.id !== selectedContainer.id.toString()) {
                alert("Slot sudah terisi oleh kontainer lain pada tier atas!");
                return;
            }
            
            const targetPos = `${slotEl.dataset.block}-${slotEl.dataset.bay}-${slotEl.dataset.row}`;
            showConfirmMoveModal(targetPos);
            
        } else {
            // Seleksi kontainer
            if (box) {
                const cid = parseInt(box.dataset.id);
                selectContainer(cid);
            } else {
                // Klik slot kosong
                document.querySelectorAll('.container-box').forEach(el => el.classList.remove('selected'));
                document.getElementById('detail-panel').classList.add('hidden');
                selectedContainer = null;
            }
        }
    }

    function selectContainer(id) {
        const c = yardData.find(x => x.id === id);
        if(!c) return;
        selectedContainer = c;
        
        // UI Highlight
        document.querySelectorAll('.container-box').forEach(el => el.classList.remove('selected'));
        const box = document.querySelector(`.container-box[data-id="${id}"]`);
        if(box) box.classList.add('selected');
        
        // Update Panel
        document.getElementById('detail-panel').classList.remove('hidden');
        document.getElementById('detail-no').innerText = c.no;
        
        let badgeColor = c.type === 'dry' ? 'bg-blue-100 text-blue-700' : 
                         (c.type === 'reefer' ? 'bg-cyan-100 text-cyan-700' : 'bg-red-100 text-red-700');
        
        document.getElementById('detail-badge').className = `inline-block px-2 py-1 rounded text-xs font-bold mt-1 ${badgeColor}`;
        document.getElementById('detail-badge').innerText = c.typeName;
        
        document.getElementById('detail-pos').innerText = `${c.block}-${c.bay}-${c.row} (T${c.tier})`;
        document.getElementById('detail-weight').innerText = c.weight;
        if(document.getElementById('detail-owner')) document.getElementById('detail-owner').innerText = c.owner || 'PT Samudera Logistik Prima';
        if(document.getElementById('detail-rfid')) document.getElementById('detail-rfid').innerText = c.rfid || ('E280117000' + (c.id || 1).toString().padStart(6, '0'));
        if(document.getElementById('detail-sscc')) document.getElementById('detail-sscc').innerText = c.sscc || ('(00)389912345' + (c.id || 1).toString().padStart(7, '0'));
        
        // Reset move mode if active
        if(isMoveMode) toggleMoveMode();
    }

    function closeDetail() {
        document.getElementById('detail-panel').classList.add('hidden');
        document.querySelectorAll('.container-box').forEach(el => el.classList.remove('selected'));
        selectedContainer = null;
        if(isMoveMode) toggleMoveMode();
    }

    // Move Mode Logic
    function toggleMoveMode() {
        isMoveMode = !isMoveMode;
        const btn = document.getElementById('btn-move-mode');
        const form = document.getElementById('move-form');
        
        if(isMoveMode) {
            btn.classList.add('hidden');
            form.classList.remove('hidden');
            
            // Highlight empty slots
            document.querySelectorAll('.yard-slot').forEach(slot => {
                if(!slot.querySelector('.container-box')) {
                    slot.classList.add('highlight-move');
                }
            });
        } else {
            btn.classList.remove('hidden');
            form.classList.add('hidden');
            document.querySelectorAll('.yard-slot').forEach(slot => {
                slot.classList.remove('highlight-move');
            });
        }
    }

    // Modal Konfirmasi
    let moveTarget = '';
    function showConfirmMoveModal(target) {
        moveTarget = target;
        const eq = document.getElementById('select-equipment');
        const eqText = eq.options[eq.selectedIndex].text;
        
        document.getElementById('confirm-no').innerText = selectedContainer.no;
        document.getElementById('confirm-from').innerText = `${selectedContainer.block}-${selectedContainer.bay}-${selectedContainer.row}`;
        document.getElementById('confirm-to').innerText = target;
        document.getElementById('confirm-eq').innerText = eqText;
        
        document.getElementById('modal-confirm-move').classList.remove('hidden');
    }
    
    function cancelMove() {
        document.getElementById('modal-confirm-move').classList.add('hidden');
    }
    
    function executeMove() {
        document.getElementById('modal-confirm-move').classList.add('hidden');
        
        // Simulasi update data (AJAX)
        const parts = moveTarget.split('-'); // B-01-02
        selectedContainer.block = parts[0];
        selectedContainer.bay = parts[1];
        selectedContainer.row = parts[2];
        selectedContainer.tier = 1; // asumsikan tier 1 kalau kosong
        
        // Tambah log
        const eq = document.getElementById('select-equipment').value;
        addEventLog(`Move Selesai (${eq})`, `${selectedContainer.no} dipindahkan ke ${moveTarget}`, 'text-green-600');
        
        // Kirim sinkronisasi ke backend API database
        fetch('api/move_container.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                container_id: selectedContainer.id,
                equipment_id: eq,
                to_block: parts[0],
                to_bay: parts[1],
                to_row: parts[2],
                to_tier: '01'
            })
        }).then(r => r.json()).then(res => {
            if(res.success) console.log('DB sync OK:', res.message);
        }).catch(e => console.warn('Lokal mode aktif:', e));
        
        toggleMoveMode();
        renderContainers();
        selectContainer(selectedContainer.id); // refresh panel
        
        // Animate RS (opsional, untuk demo)
        const rs = document.querySelector(`.reach-stacker[data-eq="${eq}"]`);
        if(rs) {
            const slot = document.getElementById(`slot-${moveTarget}`);
            if(slot) {
                const mapRect = yardMap.getBoundingClientRect();
                // Because we are moving the absolute positioned RS relative to yard-map
                rs.style.left = (slot.offsetLeft + slot.offsetWidth/2) + 'px';
                rs.style.top = (slot.offsetTop + slot.parentElement.parentElement.offsetTop + slot.offsetHeight/2) + 'px';
                
                // Tambah efek aktif
                const icon = rs.querySelector('.rs-icon');
                icon.classList.add('active');
                setTimeout(() => icon.classList.remove('active'), 3000);
            }
        }
    }

    // Equipment Logic
    function renderEquipment() {
        const layer = document.getElementById('equipment-layer');
        layer.innerHTML = '';
        
        const eqs = [
            { id: 'RS-01', x: 250, y: 350, type: 'rs', op: 'Budi S.' },
            { id: 'RS-02', x: 650, y: 150, type: 'rs', op: 'Ahmad T.' },
            { id: 'RS-03', x: 950, y: 400, type: 'rs', op: 'Joko W.' },
            { id: 'RTG-01', x: 100, y: 500, type: 'rtg', op: 'Otomatis' }
        ];
        
        eqs.forEach(eq => {
            const el = document.createElement('div');
            el.className = 'reach-stacker pointer-events-auto cursor-pointer';
            el.dataset.eq = eq.id;
            el.style.left = eq.x + 'px';
            el.style.top = eq.y + 'px';
            
            const bg = eq.type === 'rtg' ? 'bg-rtg' : 'bg-rs';
            el.innerHTML = `<div class="rs-icon ${bg}" title="${eq.id}">${eq.id.replace('RS-','R').replace('RTG-','T')}</div>`;
            
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                showEqPanel(eq);
            });
            
            layer.appendChild(el);
        });
    }
    
    function showEqPanel(eq) {
        document.getElementById('eq-panel').classList.remove('hidden');
        document.getElementById('eq-id').innerText = eq.id;
        document.getElementById('eq-op').innerText = eq.op;
        document.getElementById('eq-task').innerText = "Standby di area " + (eq.x > 500 ? "Timur" : "Barat");
    }
    
    function closeEqPanel() {
        document.getElementById('eq-panel').classList.add('hidden');
    }
    
    function addEventLog(title, desc, colorClass) {
        const ul = document.getElementById('event-log');
        const li = document.createElement('li');
        li.className = 'p-3 hover:bg-gray-50';
        li.innerHTML = `
            <div class="flex justify-between mb-1">
                <span class="font-medium ${colorClass}">${title}</span>
                <span class="text-xs text-gray-500">Baru saja</span>
            </div>
            <p class="text-xs text-gray-600">${desc}</p>
        `;
        ul.insertBefore(li, ul.firstChild);
        if(ul.children.length > 10) ul.removeChild(ul.lastChild);
    }

    function refreshData() {
        // Simulasi fetch ulang
        yardData = generateDummyData();
        renderContainers();
        
        // Random move RS
        document.querySelectorAll('.reach-stacker').forEach(rs => {
            const x = 100 + Math.random() * 900;
            const y = 100 + Math.random() * 500;
            rs.style.left = x + 'px';
            rs.style.top = y + 'px';
        });
    }

    function loadYardFromAPI() {
        fetch('api/get_yard_data.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.containers && data.containers.length > 0) {
                    const dbContainers = data.containers.map(c => {
                        let type = c.cargo_type || 'dry';
                        let typeName = c.size_type || '40ft HC';
                        return {
                            id: parseInt(c.id),
                            no: c.container_number,
                            block: c.block,
                            bay: (c.bay || '01').toString().padStart(2, '0'),
                            row: (c.row || '01').toString().padStart(2, '0'),
                            tier: parseInt(c.tier || 1),
                            type: type,
                            typeName: typeName,
                            weight: (parseFloat(c.gross_weight_kg) || 20000).toLocaleString('id-ID') + ' kg',
                            dwell: '1 hari 4 jam',
                            owner: c.owner_company || 'PT Samudera Logistik Prima',
                            rfid: c.rfid_tag || '',
                            sscc: c.sscc_code || ''
                        };
                    });
                    if (dbContainers.length > 0) {
                        yardData = dbContainers;
                        renderContainers();
                    }
                }
            })
            .catch(err => console.warn('Info: Menggunakan data simulasi default.'));
    }

    // Init
    buildYardGrid();
    yardData = generateDummyData();
    renderContainers();
    renderEquipment();
    loadYardFromAPI();

</script>
