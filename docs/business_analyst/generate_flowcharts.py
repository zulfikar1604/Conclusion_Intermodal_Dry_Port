import os
import sys
from playwright.sync_api import sync_playwright

output_dir = r"C:\xampp\htdocs\Conclusion_Intermodal_Dry_Port\docs\business_analyst"
os.makedirs(output_dir, exist_ok=True)

# =============================================================================
# 1. FLOWCHART 1: MASTER END-TO-END TERMINAL DECISION FLOWCHART
# =============================================================================
html_e2e_flowchart = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Flowchart End-to-End Proses Bisnis CIDP</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #090e17;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    color: #f8fafc;
    width: 2500px;
    height: 1550px;
    padding: 30px;
  }
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #1e293b;
    padding-bottom: 16px;
    margin-bottom: 20px;
  }
  .title-box h1 {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.5px;
  }
  .title-box p {
    font-size: 16px;
    color: #38bdf8;
    font-weight: 600;
    margin-top: 4px;
  }
  .legend-bar {
    display: flex;
    gap: 16px;
    background: #0f172a;
    border: 1px solid #1e293b;
    padding: 10px 18px;
    border-radius: 8px;
  }
  .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #cbd5e1;
  }
  .leg-shape {
    width: 18px;
    height: 14px;
    border-radius: 3px;
  }
</style>
</head>
<body>

  <div class="header">
    <div class="title-box">
      <h1>FLOWCHART RESMI ALUR PROSES BISNIS END-TO-END CIDP 35 HA</h1>
      <p>STANDAR SILABUS: BUSINESS ROLE, BUSINESS RULES TIMBANGAN (LULUS VS GAGAL), PENGANGKATAN 40FT/20FT, CFS &amp; INTEGRASI 5 MODUL</p>
    </div>
    <div class="legend-bar">
      <div class="legend-item"><div class="leg-shape" style="background:#0284c7; border-radius:10px;"></div><b>Terminator (Start/End)</b></div>
      <div class="legend-item"><div class="leg-shape" style="background:#1e293b; border:1px solid #38bdf8;"></div><b>Proses / Aktivitas</b></div>
      <div class="legend-item"><div class="leg-shape" style="background:#1e293b; border:1px solid #f59e0b; transform:rotate(45deg); width:12px; height:12px;"></div><b>Decision (Keputusan)</b></div>
      <div class="legend-item"><div class="leg-shape" style="background:#059669;"></div><b>Cabang Lulus (YA)</b></div>
      <div class="legend-item"><div class="leg-shape" style="background:#dc2626;"></div><b>Cabang Gagal (TIDAK)</b></div>
    </div>
  </div>

  <!-- SVG CANVAS FLOWCHART -->
  <svg width="2440" height="1420" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <!-- Arrow Markers -->
      <marker id="arr-blue" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto">
        <path d="M 0 1 L 9 5 L 0 9 z" fill="#38bdf8" />
      </marker>
      <marker id="arr-green" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto">
        <path d="M 0 1 L 9 5 L 0 9 z" fill="#10b981" />
      </marker>
      <marker id="arr-red" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto">
        <path d="M 0 1 L 9 5 L 0 9 z" fill="#ef4444" />
      </marker>
      <marker id="arr-amber" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto">
        <path d="M 0 1 L 9 5 L 0 9 z" fill="#f59e0b" />
      </marker>
    </defs>

    <!-- =================================================================== -->
    <!-- STAGE 1: GATE-IN & WEIGHBRIDGE (X: 40 to 480) -->
    <!-- =================================================================== -->
    <rect x="40" y="20" width="460" height="1380" rx="12" fill="#0f172a" stroke="#1e293b" stroke-width="1.5" />
    <text x="270" y="55" fill="#38bdf8" font-size="16" font-weight="800" text-anchor="middle">TAHAP 1: GATE-IN &amp; TIMBANGAN DINAMIS</text>
    <text x="270" y="75" fill="#94a3b8" font-size="12" text-anchor="middle">Modul WCS + YMS (pages/gate.php)</text>

    <!-- 1.1 Start Node -->
    <rect x="120" y="100" width="300" height="50" rx="25" fill="#0284c7" stroke="#38bdf8" stroke-width="2" />
    <text x="270" y="131" fill="#ffffff" font-size="14" font-weight="800" text-anchor="middle">START: Truk Tiba di Gerbang Masuk</text>

    <!-- Arrow 1.1 -> 1.2 -->
    <line x1="270" y1="150" x2="270" y2="190" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr-blue)" />

    <!-- 1.2 Process: Data Capture WCS -->
    <rect x="90" y="190" width="360" height="90" rx="8" fill="#1e293b" stroke="#334155" stroke-width="1.5" />
    <text x="270" y="215" fill="#f8fafc" font-size="13" font-weight="700" text-anchor="middle">Perekaman Data Sensor Otomatis (WCS):</text>
    <text x="270" y="235" fill="#94a3b8" font-size="12" text-anchor="middle">&bull; Timbangan Avery 80T: Baca Gross Weight</text>
    <text x="270" y="253" fill="#94a3b8" font-size="12" text-anchor="middle">&bull; Kamera ANPR (Plat Truk) &amp; OCR (Kode ISO 6346)</text>
    <text x="270" y="271" fill="#38bdf8" font-size="11.5" text-anchor="middle">Aktor: Supir Truk Tap RFID &amp; Gate Clerk Memantau</text>

    <!-- Arrow 1.2 -> 1.3 -->
    <line x1="270" y1="280" x2="270" y2="330" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr-blue)" />

    <!-- 1.3 Decision Diamond 1: Validasi Timbangan -->
    <polygon points="270,330 440,430 270,530 100,430" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="270" y="405" fill="#fbbf24" font-size="13" font-weight="800" text-anchor="middle">VALIDASI TIMBANGAN &amp; ISO:</text>
    <text x="270" y="425" fill="#f8fafc" font-size="12" text-anchor="middle">1. Gross Weight &le; 34.000 kg?</text>
    <text x="270" y="443" fill="#f8fafc" font-size="12" text-anchor="middle">2. Toleransi Selisih &le; &plusmn; 2.0% vs VGM?</text>
    <text x="270" y="461" fill="#f8fafc" font-size="12" text-anchor="middle">3. Check Digit Modulo 11 Valid?</text>

    <!-- ================== BRANCH FAIL (RED) ================== -->
    <!-- Line from Diamond Left to Reject Lane -->
    <line x1="100" y1="430" x2="60" y2="430" stroke="#ef4444" stroke-width="2.5" />
    <line x1="60" y1="430" x2="60" y2="600" stroke="#ef4444" stroke-width="2.5" />
    <line x1="60" y1="600" x2="90" y2="600" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr-red)" />
    <!-- Label TIDAK -->
    <rect x="50" y="470" width="70" height="24" rx="4" fill="#7f1d1d" stroke="#ef4444" />
    <text x="85" y="486" fill="#fecaca" font-size="11" font-weight="800" text-anchor="middle">TIDAK (GAGAL)</text>

    <!-- Reject Box 1 -->
    <rect x="90" y="560" width="360" height="110" rx="8" fill="#2d1215" stroke="#ef4444" stroke-width="1.5" />
    <text x="270" y="585" fill="#fca5a5" font-size="13" font-weight="800" text-anchor="middle">PENOLAKAN GERBANG MASUK (LOCK):</text>
    <text x="270" y="605" fill="#fecaca" font-size="12" text-anchor="middle">&bull; Palang Barrier TETAP TERKUNCI (LOCK)</text>
    <text x="270" y="623" fill="#fecaca" font-size="12" text-anchor="middle">&bull; Alarm Sirine Menyala &amp; Lampu Merah Aktif</text>
    <text x="270" y="641" fill="#fecaca" font-size="12" text-anchor="middle">&bull; Status Database: OVERLOAD_REJECTED</text>
    <text x="270" y="659" fill="#f87171" font-size="11.5" font-weight="700" text-anchor="middle">&rarr; Truk Belok ke REJECT LANE / HOLDING BAY</text>

    <!-- Arrow to Reject Decision -->
    <line x1="270" y1="670" x2="270" y2="710" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr-red)" />

    <!-- Decision Reject -->
    <polygon points="270,710 420,780 270,850 120,780" fill="#2d1215" stroke="#ef4444" stroke-width="1.5" />
    <text x="270" y="770" fill="#fca5a5" font-size="12.5" font-weight="800" text-anchor="middle">Investigasi Supervisor:</text>
    <text x="270" y="790" fill="#fecaca" font-size="11.5" text-anchor="middle">Koreksi Dokumen / Overload Mutlak?</text>

    <!-- Exit via U-turn -->
    <line x1="270" y1="850" x2="270" y2="900" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr-red)" />
    <rect x="110" y="900" width="320" height="70" rx="8" fill="#1e293b" stroke="#ef4444" stroke-width="1.5" />
    <text x="270" y="925" fill="#fca5a5" font-size="12.5" font-weight="700" text-anchor="middle">U-Turn Bypass Exit / Partial Stripping</text>
    <text x="270" y="945" fill="#94a3b8" font-size="11.5" text-anchor="middle">Truk putar balik keluar terminal (cegah jalan rusak)</text>
    <text x="270" y="960" fill="#f87171" font-size="11" text-anchor="middle">atau stripping kelebihan kargo di CFS</text>

    <line x1="270" y1="970" x2="270" y2="1010" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr-red)" />
    <rect x="150" y="1010" width="240" height="40" rx="20" fill="#7f1d1d" stroke="#ef4444" stroke-width="1.5" />
    <text x="270" y="1035" fill="#fecaca" font-size="12" font-weight="800" text-anchor="middle">TERMINATOR: Truk Keluar</text>

    <!-- ================== BRANCH PASS (GREEN) ================== -->
    <!-- Line from Diamond Bottom to Approved -->
    <line x1="440" y1="430" x2="480" y2="430" stroke="#10b981" stroke-width="2.5" />
    <line x1="480" y1="430" x2="520" y2="430" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr-green)" />
    <!-- Label YA -->
    <rect x="445" y="405" width="60" height="24" rx="4" fill="#064e3b" stroke="#10b981" />
    <text x="475" y="421" fill="#a7f3d0" font-size="11" font-weight="800" text-anchor="middle">YA (LULUS)</text>

    <!-- =================================================================== -->
    <!-- STAGE 2: CLASSIFICATION & LIFTING ASSIGNMENT (X: 520 to 980) -->
    <!-- =================================================================== -->
    <rect x="520" y="20" width="460" height="1380" rx="12" fill="#0f172a" stroke="#1e293b" stroke-width="1.5" />
    <text x="750" y="55" fill="#38bdf8" font-size="16" font-weight="800" text-anchor="middle">TAHAP 2: KLASIFIKASI &amp; ALOKASI ALAT</text>
    <text x="750" y="75" fill="#94a3b8" font-size="12" text-anchor="middle">Menjawab Notulensi: Siapa yang Ngangkat &amp; Barangnya Apa?</text>

    <!-- 2.1 Approved Node -->
    <rect x="550" y="380" width="400" height="95" rx="8" fill="#064e3b" stroke="#10b981" stroke-width="1.5" />
    <text x="750" y="405" fill="#34d399" font-size="13" font-weight="800" text-anchor="middle">GERBANG DIBUKA (GATE_IN_APPROVED):</text>
    <text x="750" y="425" fill="#ecfdf5" font-size="12" text-anchor="middle">&bull; Palang Barrier Otomatis Terangkat</text>
    <text x="750" y="443" fill="#ecfdf5" font-size="12" text-anchor="middle">&bull; Kiosk Cetak Tiket CMS (Container Movement Slip) QR</text>
    <text x="750" y="461" fill="#a7f3d0" font-size="11.5" text-anchor="middle">&bull; YMS Generate Work Instruction ke VMT Operator</text>

    <!-- Arrow 2.1 to Classification Diamond -->
    <line x1="750" y1="475" x2="750" y2="520" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr-green)" />

    <!-- 2.2 Classification Diamond -->
    <polygon points="750,520 920,600 750,680 580,600" fill="#1a2333" stroke="#38bdf8" stroke-width="2" />
    <text x="750" y="585" fill="#38bdf8" font-size="13" font-weight="800" text-anchor="middle">IDENTIFIKASI MUATAN:</text>
    <text x="750" y="605" fill="#f8fafc" font-size="12" text-anchor="middle">Kategori Boks &amp; Jenis Kargo</text>
    <text x="750" y="623" fill="#94a3b8" font-size="11.5" text-anchor="middle">(Dokumen Manifest &amp; PO ERP)</text>

    <!-- Branches from Classification -->
    <!-- Branch 1: Kontainer 40 Feet Laden -->
    <path d="M 580 600 L 550 600 L 550 720 L 570 720" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr-blue)" />
    <rect x="570" y="690" width="370" height="90" rx="8" fill="#132338" stroke="#0284c7" stroke-width="1.5" />
    <text x="585" y="715" fill="#38bdf8" font-size="12.5" font-weight="800">1. KONTAINER 40 FEET LADEN (ISI BERAT):</text>
    <text x="585" y="735" fill="#e2e8f0" font-size="11.5">&bull; <b>SIAPA YANG NGANGKAT:</b> RTG Crane 45T / Reach Stacker 45T</text>
    <text x="585" y="753" fill="#e2e8f0" font-size="11.5">&bull; <b>OPERATOR:</b> RTG Operator SIO via VMT Layar Kabin</text>
    <text x="585" y="770" fill="#93c5fd" font-size="11">&bull; Rute: Lapangan Penumpukan Blok Yard A/B/C/D</text>

    <!-- Branch 2: Kontainer 20 Feet Empty -->
    <path d="M 580 600 L 550 600 L 550 820 L 570 820" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr-blue)" />
    <rect x="570" y="790" width="370" height="85" rx="8" fill="#132338" stroke="#0284c7" stroke-width="1.5" />
    <text x="585" y="813" fill="#38bdf8" font-size="12.5" font-weight="800">2. KONTAINER 20 FEET EMPTY (KOSONG):</text>
    <text x="585" y="833" fill="#e2e8f0" font-size="11.5">&bull; <b>SIAPA YANG NGANGKAT:</b> Empty Container Handler (ECH) 10T</text>
    <text x="585" y="851" fill="#e2e8f0" font-size="11.5">&bull; <b>OPERATOR:</b> ECH Operator (stacking cepat s/d Tier 8)</text>
    <text x="585" y="868" fill="#93c5fd" font-size="11">&bull; Rute: Empty Container Depot Blok D</text>

    <!-- Branch 3: Reefer Container -->
    <path d="M 580 600 L 550 600 L 550 920 L 570 920" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr-blue)" />
    <rect x="570" y="885" width="370" height="85" rx="8" fill="#082f49" stroke="#06b6d4" stroke-width="1.5" />
    <text x="585" y="908" fill="#22d3ee" font-size="12.5" font-weight="800">3. REEFER CONTAINER (DINGIN / BEKU):</text>
    <text x="585" y="928" fill="#e2e8f0" font-size="11.5">&bull; <b>SIAPA YANG NGANGKAT:</b> RTG 45T / RS 45T + Teknisi Genset</text>
    <text x="585" y="946" fill="#e2e8f0" font-size="11.5">&bull; <b>RULE:</b> Max Exposure &le; 30 Menit, sambung colokan 380V</text>
    <text x="585" y="963" fill="#67e8f9" font-size="11">&bull; Rute: Blok E (Reefer Yard 300 Plugs)</text>

    <!-- Branch 4: LCL CFS Cargo -->
    <path d="M 580 600 L 550 600 L 550 1020 L 570 1020" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr-blue)" />
    <rect x="570" y="980" width="370" height="85" rx="8" fill="#192638" stroke="#60a5fa" stroke-width="1.5" />
    <text x="585" y="1003" fill="#60a5fa" font-size="12.5" font-weight="800">4. KARGO CURAH / PALET LCL CFS:</text>
    <text x="585" y="1023" fill="#e2e8f0" font-size="11.5">&bull; <b>SIAPA YANG NGANGKAT:</b> Forklift 3-5 Ton &amp; Reach Truck</text>
    <text x="585" y="1041" fill="#e2e8f0" font-size="11.5">&bull; <b>OPERATOR:</b> Forklift Driver &amp; Tally Gudang CFS</text>
    <text x="585" y="1058" fill="#93c5fd" font-size="11">&bull; Rute: Gudang CFS Dock Leveler D1-D5</text>

    <!-- Arrow to Stage 3 -->
    <line x1="940" y1="735" x2="1000" y2="735" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr-blue)" />
    <line x1="940" y1="928" x2="1000" y2="928" stroke="#06b6d4" stroke-width="2.5" marker-end="url(#arr-blue)" />
    <line x1="940" y1="1023" x2="1000" y2="1023" stroke="#60a5fa" stroke-width="2.5" marker-end="url(#arr-blue)" />

    <!-- =================================================================== -->
    <!-- STAGE 3: STACKING & STORAGE EXECUTION (X: 1000 to 1480) -->
    <!-- =================================================================== -->
    <rect x="1000" y="20" width="480" height="1380" rx="12" fill="#0f172a" stroke="#1e293b" stroke-width="1.5" />
    <text x="1240" y="55" fill="#38bdf8" font-size="16" font-weight="800" text-anchor="middle">TAHAP 3: ATURAN PENEMPATAN (STACKING &amp; WMS)</text>
    <text x="1240" y="75" fill="#94a3b8" font-size="12" text-anchor="middle">Modul YMS + WMS (pages/yard.php &amp; pages/cfs.php)</text>

    <!-- Stacking Rules Box (Yard) -->
    <rect x="1030" y="100" width="420" height="340" rx="8" fill="#141f36" stroke="#2563eb" stroke-width="1.5" />
    <text x="1050" y="130" fill="#93c5fd" font-size="14" font-weight="800">ATURAN PENEMPATAN YARD (STACKING RULES):</text>
    <text x="1050" y="155" fill="#f8fafc" font-size="12" font-weight="700">A. Koordinat 4D: [Blok] - [Bay] - [Row] - [Tier]</text>
    
    <text x="1050" y="185" fill="#38bdf8" font-size="12" font-weight="700">B. Aturan Ukuran Bay (20ft vs 40ft):</text>
    <text x="1065" y="205" fill="#cbd5e1" font-size="11.5">&bull; Bay Ganjil (01, 03, 05): Khusus 2x Kontainer 20ft</text>
    <text x="1065" y="223" fill="#cbd5e1" font-size="11.5">&bull; Bay Genap (02, 06, 10): Khusus 1x Kontainer 40ft</text>
    <text x="1065" y="241" fill="#fca5a5" font-size="11.5">&bull; Larangan: 40ft di atas 20ft tanpa twistlock dilarang!</text>

    <text x="1050" y="270" fill="#38bdf8" font-size="12" font-weight="700">C. Aturan Distribusi Berat (Weight Tier Rule):</text>
    <text x="1065" y="290" fill="#cbd5e1" font-size="11.5">&bull; Tier 1-2 (Bawah): Heavy (&gt; 24T) untuk Center of Gravity</text>
    <text x="1065" y="308" fill="#cbd5e1" font-size="11.5">&bull; Tier 3 (Tengah): Medium (15 - 24 Ton)</text>
    <text x="1065" y="326" fill="#cbd5e1" font-size="11.5">&bull; Tier 4-5 (Atas): Light (&lt; 15T) / Empty (Anti Angin Roboh)</text>

    <text x="1050" y="355" fill="#38bdf8" font-size="12" font-weight="700">D. Zonasi Khusus:</text>
    <text x="1065" y="375" fill="#cbd5e1" font-size="11.5">&bull; HazMat B3 di Blok F (berjarak &ge; 50m, tanggul penampung)</text>
    <text x="1065" y="393" fill="#cbd5e1" font-size="11.5">&bull; Ekspor dekat Rel Siding KA; Impor dekat Gate Out</text>
    <text x="1065" y="415" fill="#34d399" font-size="11" font-weight="700">&bull; Hasil: Slot Terkunci di Database YMS</text>

    <!-- WMS CFS Rules Box -->
    <rect x="1030" y="460" width="420" height="250" rx="8" fill="#132337" stroke="#0284c7" stroke-width="1.5" />
    <text x="1050" y="490" fill="#38bdf8" font-size="14" font-weight="800">ATURAN WMS CFS &amp; COLD STORAGE:</text>
    
    <text x="1050" y="515" fill="#f8fafc" font-size="12" font-weight="700">A. ASN Matching (Advance Shipping Notice):</text>
    <text x="1065" y="535" fill="#cbd5e1" font-size="11.5">&bull; Cek Qty Fisik vs PO ERP. Jika Qty &gt; PO &rarr;</text>
    <text x="1065" y="553" fill="#fca5a5" font-size="11.5" font-weight="700">&bull; Status: OVER-RECEIPT ALERT (Blokir Stok)</text>

    <text x="1050" y="580" fill="#f8fafc" font-size="12" font-weight="700">B. Directed Putaway &amp; Rak Aisle-Rack-Bin:</text>
    <text x="1065" y="600" fill="#cbd5e1" font-size="11.5">&bull; Traceability 100% per palet/karton</text>
    <text x="1065" y="618" fill="#cbd5e1" font-size="11.5">&bull; Rotasi Stok: FEFO (First Expired) &amp; FIFO Otomatis</text>
    <text x="1065" y="636" fill="#cbd5e1" font-size="11.5">&bull; Cycle Counting: Blind counting tanpa stop gudang</text>

    <text x="1050" y="663" fill="#f8fafc" font-size="12" font-weight="700">C. WCS Conveyor &amp; Mass Scanner:</text>
    <text x="1065" y="683" fill="#cbd5e1" font-size="11.5">&bull; Scan &lt; 500ms. Jika No-Read Barcode &rarr; Diverter Belokkan</text>
    <text x="1065" y="700" fill="#fca5a5" font-size="11.5">&bull; ke Reject Lane + Alert PDA Operator</text>

    <!-- Reefer Telemetry Box -->
    <rect x="1030" y="730" width="420" height="150" rx="8" fill="#082f49" stroke="#06b6d4" stroke-width="1.5" />
    <text x="1050" y="755" fill="#22d3ee" font-size="13.5" font-weight="800">MONITORING SUHU REEFER REAL-TIME:</text>
    <text x="1050" y="780" fill="#cbd5e1" font-size="11.5">&bull; Suhu Chilled (2&deg;C s/d 8&deg;C) atau Frozen (-18&deg;C)</text>
    <text x="1050" y="800" fill="#cbd5e1" font-size="11.5">&bull; Sensor WCS catat telemetri suhu per 15 menit</text>
    <text x="1050" y="820" fill="#fca5a5" font-size="11.5">&bull; Jika Suhu &gt; Threshold &rarr; Sirene Alarm + Alert Teknisi</text>
    <text x="1050" y="840" fill="#cbd5e1" font-size="11.5">&bull; Jam colok listrik tercatat presisi untuk billing</text>

    <!-- Exception Handling Diamond -->
    <polygon points="1240,900 1390,960 1240,1020 1090,960" fill="#1a2333" stroke="#f59e0b" stroke-width="1.5" />
    <text x="1240" y="955" fill="#fbbf24" font-size="12.5" font-weight="800" text-anchor="middle">Validasi Slot &amp; Stok:</text>
    <text x="1240" y="975" fill="#f8fafc" font-size="11.5" text-anchor="middle">Slot Rusak / Shortage Stok?</text>

    <line x1="1390" y1="960" x2="1430" y2="960" stroke="#f59e0b" stroke-width="2" />
    <line x1="1430" y1="960" x2="1430" y2="1050" stroke="#f59e0b" stroke-width="2" />
    <line x1="1430" y1="1050" x2="1370" y2="1050" stroke="#f59e0b" stroke-width="2" marker-end="url(#arr-amber)" />
    <rect x="1070" y="1030" width="300" height="50" rx="6" fill="#1e293b" stroke="#f59e0b" />
    <text x="1220" y="1050" fill="#fde047" font-size="11" font-weight="700" text-anchor="middle">Auto Re-assign Slot Cadangan</text>
    <text x="1220" y="1068" fill="#cbd5e1" font-size="10.5" text-anchor="middle">&amp; Trigger Blind Cycle Count</text>

    <!-- Arrow to Stage 4 -->
    <line x1="1240" y1="1020" x2="1240" y2="1110" stroke="#38bdf8" stroke-width="2" />
    <line x1="1240" y1="1110" x2="1500" y2="1110" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr-blue)" />

    <!-- =================================================================== -->
    <!-- STAGE 4: INTERMODAL, CUSTOMS & DWELLING TIME (X: 1500 to 1960) -->
    <!-- =================================================================== -->
    <rect x="1500" y="20" width="460" height="1380" rx="12" fill="#0f172a" stroke="#1e293b" stroke-width="1.5" />
    <text x="1730" y="55" fill="#38bdf8" font-size="16" font-weight="800" text-anchor="middle">TAHAP 4: INTERMODAL &amp; KEPABEANAN</text>
    <text x="1730" y="75" fill="#94a3b8" font-size="12" text-anchor="middle">Modul TMS + Customs CEISA 4.0</text>

    <!-- Rail Intermodal Box -->
    <rect x="1530" y="100" width="400" height="200" rx="8" fill="#1e2238" stroke="#6366f1" stroke-width="1.5" />
    <text x="1550" y="130" fill="#818cf8" font-size="13.5" font-weight="800">REL SIDING KERETA API 600M (TMS):</text>
    <text x="1550" y="155" fill="#cbd5e1" font-size="11.5">&bull; Jalur rel melayani 30 gerbong datar (flatcar) KA</text>
    <text x="1550" y="175" fill="#cbd5e1" font-size="11.5">&bull; <b>ALAT ANGKAT:</b> RMG Crane 45T / RS Intermodal</text>
    <text x="1550" y="195" fill="#cbd5e1" font-size="11.5">&bull; <b>SPEED:</b> Target transfer &lt; 4 menit/kontainer</text>
    <text x="1550" y="215" fill="#cbd5e1" font-size="11.5">&bull; <b>BENEFIT:</b> Pangkas biaya transport 40% vs trucking</text>
    <text x="1550" y="235" fill="#cbd5e1" font-size="11.5">&bull; Bebas macet Tol Cikampek &amp; kurangi karbon 65%</text>
    <text x="1550" y="258" fill="#a5b4fc" font-size="11" font-weight="700">&bull; pages/intermodal.php</text>

    <!-- Trucking TMS Box -->
    <rect x="1530" y="320" width="400" height="150" rx="8" fill="#162238" stroke="#38bdf8" stroke-width="1.5" />
    <text x="1550" y="345" fill="#38bdf8" font-size="13.5" font-weight="800">TRUCKING FLEET &amp; e-POD (TMS):</text>
    <text x="1550" y="370" fill="#cbd5e1" font-size="11.5">&bull; Dynamic Route: Kalkulasi rute hindari macet &amp; ganjil-genap</text>
    <text x="1550" y="390" fill="#cbd5e1" font-size="11.5">&bull; GPS Geofencing: Alert saat armada keluar rute resmi</text>
    <text x="1550" y="410" fill="#cbd5e1" font-size="11.5">&bull; Driver Mobile App: Foto serah terima, TTD digital, &amp; GPS</text>
    <text x="1550" y="435" fill="#93c5fd" font-size="11" font-weight="700">&bull; pages/trucking.php</text>

    <!-- Customs CEISA Diamond -->
    <polygon points="1730,500 1880,570 1730,640 1580,570" fill="#1a2333" stroke="#eab308" stroke-width="2" />
    <text x="1730" y="560" fill="#facc15" font-size="13" font-weight="800" text-anchor="middle">VALIDASI PABEAN CEISA 4.0:</text>
    <text x="1730" y="580" fill="#f8fafc" font-size="12" text-anchor="middle">Respon SPPB Bea Cukai Terbit?</text>

    <!-- Jalur Merah Branch -->
    <line x1="1580" y1="570" x2="1540" y2="570" stroke="#ef4444" stroke-width="2" />
    <line x1="1540" y1="570" x2="1540" y2="670" stroke="#ef4444" stroke-width="2" />
    <line x1="1540" y1="670" x2="1570" y2="670" stroke="#ef4444" stroke-width="2" marker-end="url(#arr-red)" />
    <rect x="1570" y="650" width="340" height="70" rx="6" fill="#2d1215" stroke="#ef4444" />
    <text x="1740" y="675" fill="#fca5a5" font-size="12" font-weight="800" text-anchor="middle">JALUR MERAH / HOLD PABEAN:</text>
    <text x="1740" y="695" fill="#fecaca" font-size="11" text-anchor="middle">Boks dikunci (CUSTOMS_LOCKED), wajib behandle fisik</text>

    <!-- Jalur Hijau Branch -->
    <line x1="1730" y1="640" x2="1730" y2="740" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr-green)" />
    <rect x="1560" y="740" width="340" height="65" rx="6" fill="#064e3b" stroke="#10b981" />
    <text x="1730" y="765" fill="#34d399" font-size="12" font-weight="800" text-anchor="middle">JALUR HIJAU (SPPB APPROVED):</text>
    <text x="1730" y="785" fill="#ecfdf5" font-size="11" text-anchor="middle">Kunci Pabean Terbuka (CUSTOMS_CLEARED)</text>

    <!-- Dwelling Time Monitoring -->
    <rect x="1530" y="830" width="400" height="130" rx="8" fill="#1c1917" stroke="#f59e0b" stroke-width="1.5" />
    <text x="1550" y="855" fill="#fbbf24" font-size="13" font-weight="800">DWELLING TIME &amp; DEMURRAGE RULE:</text>
    <text x="1550" y="880" fill="#cbd5e1" font-size="11.5">&bull; Free Time Penumpukan = 3 Hari</text>
    <text x="1550" y="900" fill="#cbd5e1" font-size="11.5">&bull; Hari ke-4 s/d 7 = Rp 250.000 / hari</text>
    <text x="1550" y="920" fill="#cbd5e1" font-size="11.5">&bull; Hari ke-8 ke atas = Rp 500.000 / hari (Tarif Progresif)</text>
    <text x="1550" y="940" fill="#fde047" font-size="11" font-weight="700">&bull; Early warning otomatis terkirim pada Hari ke-2</text>

    <!-- Arrow to Stage 5 -->
    <line x1="1730" y1="960" x2="1730" y2="1010" stroke="#38bdf8" stroke-width="2" />
    <line x1="1730" y1="1010" x2="1980" y2="1010" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr-blue)" />

    <!-- =================================================================== -->
    <!-- STAGE 5: BILLING & GATE-OUT (X: 1980 to 2420) -->
    <!-- =================================================================== -->
    <rect x="1980" y="20" width="440" height="1380" rx="12" fill="#0f172a" stroke="#1e293b" stroke-width="1.5" />
    <text x="2200" y="55" fill="#38bdf8" font-size="16" font-weight="800" text-anchor="middle">TAHAP 5: BILLING &amp; GATE-OUT</text>
    <text x="2200" y="75" fill="#94a3b8" font-size="12" text-anchor="middle">Modul ERP + YMS Gate (pages/billing.php)</text>

    <!-- Tariff Engine Box -->
    <rect x="2010" y="100" width="380" height="200" rx="8" fill="#142338" stroke="#0284c7" stroke-width="1.5" />
    <text x="2030" y="130" fill="#38bdf8" font-size="13.5" font-weight="800">TERMINAL TARIFF ENGINE (ERP):</text>
    <text x="2030" y="155" fill="#cbd5e1" font-size="11.5">&bull; Lift-On / Lift-Off (THC Crane 45T)</text>
    <text x="2030" y="175" fill="#cbd5e1" font-size="11.5">&bull; Storage Fee (Biaya Sewa Lahan Yard)</text>
    <text x="2030" y="195" fill="#cbd5e1" font-size="11.5">&bull; Denda Overstay Demurrage (Tarif Progresif)</text>
    <text x="2030" y="215" fill="#cbd5e1" font-size="11.5">&bull; Reefer Electricity &amp; Monitoring Fee</text>
    <text x="2030" y="235" fill="#cbd5e1" font-size="11.5">&bull; CFS Stripping &amp; Handling Charge</text>
    <text x="2030" y="260" fill="#93c5fd" font-size="11" font-weight="700">&bull; Invoice Otomatis Terbit (Sync Odoo ERP)</text>

    <!-- Payment Diamond -->
    <polygon points="2200,340 2360,420 2200,500 2040,420" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="2200" y="405" fill="#fbbf24" font-size="13" font-weight="800" text-anchor="middle">VALIDASI PEMBAYARAN:</text>
    <text x="2200" y="425" fill="#f8fafc" font-size="12" text-anchor="middle">Status Invoice = PAID?</text>
    <text x="2200" y="443" fill="#94a3b8" font-size="11" text-anchor="middle">&amp; SPPB Valid?</text>

    <!-- Unpaid Branch -->
    <line x1="2040" y1="420" x2="2010" y2="420" stroke="#ef4444" stroke-width="2" />
    <line x1="2010" y1="420" x2="2010" y2="530" stroke="#ef4444" stroke-width="2" />
    <line x1="2010" y1="530" x2="2040" y2="530" stroke="#ef4444" stroke-width="2" marker-end="url(#arr-red)" />
    <rect x="2040" y="510" width="340" height="85" rx="6" fill="#2d1215" stroke="#ef4444" />
    <text x="2210" y="533" fill="#fca5a5" font-size="12" font-weight="800" text-anchor="middle">GATE-OUT TERKUNCI (LOCK):</text>
    <text x="2210" y="553" fill="#fecaca" font-size="11" text-anchor="middle">Palang barrier tidak terbuka!</text>
    <text x="2210" y="570" fill="#fecaca" font-size="11" text-anchor="middle">Truk diarahkan ke Parkir Kasir Billing</text>
    <text x="2210" y="585" fill="#f87171" font-size="10.5" font-weight="700" text-anchor="middle">(Zero Revenue Leakage Guarantee)</text>

    <!-- Paid Branch -->
    <line x1="2200" y1="500" x2="2200" y2="620" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr-green)" />
    <rect x="2020" y="620" width="360" height="95" rx="8" fill="#064e3b" stroke="#10b981" stroke-width="1.5" />
    <text x="2200" y="645" fill="#34d399" font-size="13" font-weight="800" text-anchor="middle">GERBANG KELUAR TERBUKA:</text>
    <text x="2200" y="665" fill="#ecfdf5" font-size="11.5" text-anchor="middle">&bull; Status Database: TERMINAL_EXITED</text>
    <text x="2200" y="683" fill="#ecfdf5" font-size="11.5" text-anchor="middle">&bull; Slot Yard Otomatis Kosong (Tersedia Kembali)</text>
    <text x="2200" y="700" fill="#a7f3d0" font-size="11" text-anchor="middle">&bull; e-Receipt &amp; e-Gate Pass terkirim ke email Shipper</text>

    <!-- End Terminator -->
    <line x1="2200" y1="715" x2="2200" y2="760" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr-green)" />
    <rect x="2050" y="760" width="300" height="50" rx="25" fill="#059669" stroke="#34d399" stroke-width="2" />
    <text x="2200" y="791" fill="#ffffff" font-size="14" font-weight="800" text-anchor="middle">END: Truk Selesai Keluar Terminal</text>

    <!-- Bottom Architecture Box -->
    <rect x="2010" y="850" width="380" height="230" rx="8" fill="#111827" stroke="#374151" stroke-width="1.5" />
    <text x="2200" y="880" fill="#94a3b8" font-size="12.5" font-weight="800" text-anchor="middle">SIKLUS LENGKAP TUNTAS</text>
    <text x="2030" y="910" fill="#e2e8f0" font-size="11.5">&bull; Data tersimpan permanen di database CIDP</text>
    <text x="2030" y="930" fill="#e2e8f0" font-size="11.5">&bull; Traceability audit trail tercatat 100%</text>
    <text x="2030" y="950" fill="#e2e8f0" font-size="11.5">&bull; Tidak ada celah muatan gelap / over-capacity</text>
    <text x="2030" y="970" fill="#e2e8f0" font-size="11.5">&bull; Kepatuhan UU LLAJ &amp; SOLAS VGM terjamin</text>
    <text x="2200" y="1010" fill="#38bdf8" font-size="11" font-weight="700" text-anchor="middle">Prototype Live: http://localhost/.../dashboard.php</text>

  </svg>

</body>
</html>
"""

# =============================================================================
# 2. FLOWCHART 2: DETAIL TIMBANGAN GATE-IN (LULUS KE MANA VS GAGAL KE MANA)
# =============================================================================
html_gate_flowchart = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Flowchart Detail Timbangan Gate-In</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #090e17;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    color: #f8fafc;
    width: 2000px;
    height: 1400px;
    padding: 30px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 16px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>FLOWCHART KEPUTUSAN TIMBANGAN DINAMIS GATE-IN (WCS + YMS)</h1>
      <p>MENJAWAB NOTULENSI: "TRUK MASUK TIMBANGAN, JIKA LULUS KE MANA, JIKA TIDAK KE MANA?"</p>
    </div>
    <div style="background:#0f172a; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Implementasi: pages/gate.php
    </div>
  </div>

  <svg width="1940" height="1280" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <marker id="arr2-blue" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#38bdf8" /></marker>
      <marker id="arr2-green" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#10b981" /></marker>
      <marker id="arr2-red" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#ef4444" /></marker>
    </defs>

    <!-- Start Node -->
    <rect x="820" y="30" width="300" height="50" rx="25" fill="#0284c7" stroke="#38bdf8" stroke-width="2" />
    <text x="970" y="61" fill="#ffffff" font-size="14" font-weight="800" text-anchor="middle">START: Truk Berhenti di Platform Timbangan</text>

    <!-- Line to Sensor -->
    <line x1="970" y1="80" x2="970" y2="120" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr2-blue)" />

    <!-- Sensor Ingestion Process -->
    <rect x="760" y="120" width="420" height="90" rx="8" fill="#1e293b" stroke="#334155" stroke-width="1.5" />
    <text x="970" y="145" fill="#f8fafc" font-size="13" font-weight="700" text-anchor="middle">Sensor WCS Menangkap Data (&lt; 3 Detik):</text>
    <text x="970" y="165" fill="#cbd5e1" font-size="12" text-anchor="middle">&bull; Timbangan Avery 80T Load Cell: Gross Weight</text>
    <text x="970" y="183" fill="#cbd5e1" font-size="12" text-anchor="middle">&bull; Kamera ANPR (Plat Truk) &amp; Kamera OCR (ISO 6346 Boks)</text>
    <text x="970" y="200" fill="#38bdf8" font-size="11.5" text-anchor="middle">&bull; ERP Lookup: Tare Weight Truk &amp; VGM SOLAS Dokumen</text>

    <!-- Line to Diamond 1 -->
    <line x1="970" y1="210" x2="970" y2="260" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr2-blue)" />

    <!-- Diamond 1: Batas Legal 34 Ton -->
    <polygon points="970,260 1150,330 970,400 790,330" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="970" y="320" fill="#fbbf24" font-size="13" font-weight="800" text-anchor="middle">ATURAN 1 (BATAS LEGAL JALAN):</text>
    <text x="970" y="340" fill="#f8fafc" font-size="12" text-anchor="middle">Gross Weight &le; 34.000 kg?</text>
    <text x="970" y="358" fill="#94a3b8" font-size="11" text-anchor="middle">(UU RI No. 22/2009 Jalan Kelas 1)</text>

    <!-- Red Branch from D1 -->
    <line x1="790" y1="330" x2="450" y2="330" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr2-red)" />
    <rect x="580" y="305" width="70" height="24" rx="4" fill="#7f1d1d" stroke="#ef4444" />
    <text x="615" y="321" fill="#fecaca" font-size="11" font-weight="800" text-anchor="middle">TIDAK (&gt; 34T)</text>

    <!-- Green Branch to D2 -->
    <line x1="970" y1="400" x2="970" y2="450" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr2-green)" />
    <rect x="980" y="415" width="50" height="22" rx="4" fill="#064e3b" stroke="#10b981" />
    <text x="1005" y="430" fill="#a7f3d0" font-size="10.5" font-weight="800" text-anchor="middle">YA</text>

    <!-- Diamond 2: Toleransi VGM +-2% -->
    <polygon points="970,450 1150,520 970,590 790,520" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="970" y="510" fill="#fbbf24" font-size="13" font-weight="800" text-anchor="middle">ATURAN 2 (TOLERANSI SOLAS VGM):</text>
    <text x="970" y="530" fill="#f8fafc" font-size="12" text-anchor="middle">|Net Weight - VGM| / VGM &le; &plusmn; 2.0%?</text>
    <text x="970" y="548" fill="#94a3b8" font-size="11" text-anchor="middle">(Kepatuhan IMO SOLAS Bab VI)</text>

    <!-- Red Branch from D2 -->
    <line x1="790" y1="520" x2="450" y2="520" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr2-red)" />
    <rect x="580" y="495" width="70" height="24" rx="4" fill="#7f1d1d" stroke="#ef4444" />
    <text x="615" y="511" fill="#fecaca" font-size="11" font-weight="800" text-anchor="middle">TIDAK (&gt; 2%)</text>

    <!-- Green Branch to D3 -->
    <line x1="970" y1="590" x2="970" y2="640" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr2-green)" />
    <rect x="980" y="605" width="50" height="22" rx="4" fill="#064e3b" stroke="#10b981" />
    <text x="1005" y="620" fill="#a7f3d0" font-size="10.5" font-weight="800" text-anchor="middle">YA</text>

    <!-- Diamond 3: Check Digit ISO 6346 -->
    <polygon points="970,640 1150,710 970,780 790,710" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="970" y="700" fill="#fbbf24" font-size="13" font-weight="800" text-anchor="middle">ATURAN 3 (INTEGRITAS IDENTITAS):</text>
    <text x="970" y="720" fill="#f8fafc" font-size="12" text-anchor="middle">Check Digit ISO 6346 Modulo 11 Valid?</text>
    <text x="970" y="738" fill="#94a3b8" font-size="11" text-anchor="middle">(Format 4 Huruf Kapital + 7 Angka)</text>

    <!-- Red Branch from D3 -->
    <line x1="790" y1="710" x2="450" y2="710" stroke="#ef4444" stroke-width="2.5" marker-end="url(#arr2-red)" />
    <rect x="580" y="685" width="80" height="24" rx="4" fill="#7f1d1d" stroke="#ef4444" />
    <text x="620" y="701" fill="#fecaca" font-size="11" font-weight="800" text-anchor="middle">TIDAK (INVALID)</text>

    <!-- ==================== SISI KIRI: JIKA TIDAK LULUS ==================== -->
    <rect x="80" y="270" width="370" height="520" rx="10" fill="#1f1315" stroke="#ef4444" stroke-width="2" />
    <text x="265" y="305" fill="#fca5a5" font-size="16" font-weight="800" text-anchor="middle">❌ JIKA TIDAK LULUS (REJECT):</text>
    <text x="265" y="330" fill="#fecaca" font-size="12.5" text-anchor="middle">Ke Mana Truk Diarahkan?</text>

    <text x="100" y="365" fill="#fca5a5" font-size="12" font-weight="700">1. RESPON SISTEM INSTAN:</text>
    <text x="115" y="385" fill="#cbd5e1" font-size="11.5">&bull; Palang Barrier TETAP TERKUNCI (LOCK)</text>
    <text x="115" y="403" fill="#cbd5e1" font-size="11.5">&bull; Sirene Bunyi &amp; Lampu Merah Menyala</text>
    <text x="115" y="421" fill="#cbd5e1" font-size="11.5">&bull; Status DB: <code>OVERLOAD_REJECTED</code></text>

    <text x="100" y="455" fill="#fca5a5" font-size="12" font-weight="700">2. RUTE FISIK LAPANGAN:</text>
    <text x="115" y="475" fill="#cbd5e1" font-size="11.5">&bull; Truk <b>DILARANG MASUK</b> ke Yard 35 Ha</text>
    <text x="115" y="493" fill="#cbd5e1" font-size="11.5">&bull; Truk belok ke <b>REJECT LANE / HOLDING BAY</b></text>
    <text x="115" y="511" fill="#cbd5e1" font-size="11.5">&bull; Area Karantina Timbangan samping gate</text>

    <text x="100" y="545" fill="#fca5a5" font-size="12" font-weight="700">3. TINDAKAN PENYELESAIAN:</text>
    <text x="115" y="565" fill="#cbd5e1" font-size="11.5">&bull; Audit Dokumen oleh Supervisor Gate</text>
    <text x="115" y="583" fill="#cbd5e1" font-size="11.5">&bull; Jika salah ketik data &rarr; Otorisasi revisi</text>
    <text x="115" y="601" fill="#cbd5e1" font-size="11.5">&bull; Jika overload murni &rarr; <b>Bypass U-Turn Exit</b></text>
    <text x="115" y="619" fill="#cbd5e1" font-size="11.5">&bull; (Putar balik keluar terminal) atau</text>
    <text x="115" y="637" fill="#cbd5e1" font-size="11.5">&bull; Stripping sebagian kargo di CFS</text>

    <text x="265" y="685" fill="#f87171" font-size="12" font-weight="700" text-anchor="middle">Alasan: Melindungi jalan beton K-450</text>
    <text x="265" y="705" fill="#f87171" font-size="11.5" text-anchor="middle">&amp; terhindar dari sanksi IMO SOLAS</text>

    <!-- ==================== SISI KANAN & BAWAH: JIKA LULUS ==================== -->
    <!-- Green Line from D3 to Approved Box -->
    <line x1="970" y1="780" x2="970" y2="830" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr2-green)" />
    <rect x="980" y="795" width="50" height="22" rx="4" fill="#064e3b" stroke="#10b981" />
    <text x="1005" y="810" fill="#a7f3d0" font-size="10.5" font-weight="800" text-anchor="middle">YA</text>

    <!-- Approved Main Node -->
    <rect x="730" y="830" width="480" height="85" rx="8" fill="#064e3b" stroke="#10b981" stroke-width="2" />
    <text x="970" y="855" fill="#34d399" font-size="15" font-weight="800" text-anchor="middle">✅ JIKA LULUS (SEMUA 3 ATURAN TERPENUHI)</text>
    <text x="970" y="878" fill="#ecfdf5" font-size="12.5" text-anchor="middle">&bull; Palang Barrier Otomatis Terangkat &bull; Status: GATE_IN_APPROVED</text>
    <text x="970" y="898" fill="#a7f3d0" font-size="12" text-anchor="middle">&bull; Kiosk Cetak Container Movement Slip (CMS) QR Code</text>

    <text x="970" y="945" fill="#38bdf8" font-size="14" font-weight="800" text-anchor="middle">KE MANA TRUK DIARAHKAN? (DISPATCH OTOMATIS YMS):</text>

    <!-- Multi-way Destination Grid -->
    <!-- Dest 1: FCL Dry -->
    <line x1="800" y1="915" x2="600" y2="970" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr2-blue)" />
    <rect x="420" y="970" width="340" height="110" rx="8" fill="#132338" stroke="#0284c7" stroke-width="1.5" />
    <text x="435" y="995" fill="#38bdf8" font-size="13" font-weight="800">1. FCL KERING 20FT / 40FT:</text>
    <text x="435" y="1015" fill="#cbd5e1" font-size="11.5">&bull; <b>Tujuan:</b> Lapangan Penumpukan Blok A/B/C/D</text>
    <text x="435" y="1033" fill="#cbd5e1" font-size="11.5">&bull; <b>Alat Angkat:</b> RTG Crane 45T / Reach Stacker 45T</text>
    <text x="435" y="1051" fill="#cbd5e1" font-size="11.5">&bull; <b>Aturan:</b> Bay Genap (40ft), Tier 1-2 jika berat &gt; 24T</text>
    <text x="435" y="1068" fill="#93c5fd" font-size="11">&bull; pages/yard.php</text>

    <!-- Dest 2: Reefer -->
    <line x1="900" y1="915" x2="880" y2="970" stroke="#06b6d4" stroke-width="2" marker-end="url(#arr2-blue)" />
    <rect x="780" y="970" width="350" height="110" rx="8" fill="#082f49" stroke="#06b6d4" stroke-width="1.5" />
    <text x="795" y="995" fill="#22d3ee" font-size="13" font-weight="800">2. REEFER (KARGO DINGIN):</text>
    <text x="795" y="1015" fill="#cbd5e1" font-size="11.5">&bull; <b>Tujuan:</b> Blok E (Reefer Yard - 300 Plugs 380V)</text>
    <text x="795" y="1033" fill="#cbd5e1" font-size="11.5">&bull; <b>Aturan:</b> Max Exposure 30 Menit wajib dicolok</text>
    <text x="795" y="1051" fill="#cbd5e1" font-size="11.5">&bull; <b>Monitoring:</b> Suhu Chilled 2-8&deg;C / Frozen -18&deg;C</text>
    <text x="795" y="1068" fill="#67e8f9" font-size="11">&bull; pages/reefer.php</text>

    <!-- Dest 3: LCL CFS -->
    <line x1="1040" y1="915" x2="1240" y2="970" stroke="#60a5fa" stroke-width="2" marker-end="url(#arr2-blue)" />
    <rect x="1150" y="970" width="350" height="110" rx="8" fill="#17253b" stroke="#60a5fa" stroke-width="1.5" />
    <text x="1165" y="995" fill="#60a5fa" font-size="13" font-weight="800">3. KARGO CURAH / PALET LCL:</text>
    <text x="1165" y="1015" fill="#cbd5e1" font-size="11.5">&bull; <b>Tujuan:</b> Gudang CFS Pintu Dock D1-D5</text>
    <text x="1165" y="1033" fill="#cbd5e1" font-size="11.5">&bull; <b>Alat Angkat:</b> Forklift 3-5 Ton untuk stripping</text>
    <text x="1165" y="1051" fill="#cbd5e1" font-size="11.5">&bull; <b>Aturan:</b> ASN PO Matching &amp; FEFO/FIFO di rak</text>
    <text x="1165" y="1068" fill="#93c5fd" font-size="11">&bull; pages/cfs.php</text>

    <!-- Dest 4: Customs & Hazmat -->
    <line x1="1140" y1="915" x2="1600" y2="970" stroke="#f59e0b" stroke-width="2" marker-end="url(#arr2-blue)" />
    <rect x="1520" y="970" width="350" height="110" rx="8" fill="#2d2015" stroke="#f59e0b" stroke-width="1.5" />
    <text x="1535" y="995" fill="#fbbf24" font-size="13" font-weight="800">4. JALUR MERAH &amp; HAZMAT:</text>
    <text x="1535" y="1015" fill="#cbd5e1" font-size="11.5">&bull; <b>Pabean Jalur Merah:</b> Hanggar Behandle Bea Cukai</text>
    <text x="1535" y="1033" fill="#cbd5e1" font-size="11.5">&bull; <b>HazMat B3:</b> Blok F (Tanggul Isolasi Kimia &ge; 50m)</text>
    <text x="1535" y="1051" fill="#cbd5e1" font-size="11.5">&bull; <b>Aturan:</b> Terkunci hingga SPPB terbit / inspektur lolos</text>
    <text x="1535" y="1068" fill="#fde047" font-size="11">&bull; pages/customs.php</text>

  </svg>
</body>
</html>
"""

# =============================================================================
# 3. FLOWCHART 3: DETAIL PENGANGKATAN & PENEMPATAN 40FT/20FT
# =============================================================================
html_lifting_flowchart = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Flowchart Pengangkatan & Penempatan Kontainer</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #090e17;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    color: #f8fafc;
    width: 2000px;
    height: 1400px;
    padding: 30px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 16px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>FLOWCHART RESMI ALOKASI ALAT PENGANGKAT &amp; STACKING RULES 3D</h1>
      <p>MENJAWAB NOTULENSI: "SIAPA YANG NGANGKAT, KONTAINER 40 FEET SIAPA YANG ANGKAT, PENEMPATANNYA SEPERTI APA?"</p>
    </div>
    <div style="background:#0f172a; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Implementasi: pages/alat.php &amp; pages/yard.php
    </div>
  </div>

  <svg width="1940" height="1280" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <marker id="arr3-blue" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#38bdf8" /></marker>
      <marker id="arr3-amber" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#f59e0b" /></marker>
    </defs>

    <!-- Start Node -->
    <rect x="800" y="20" width="340" height="50" rx="25" fill="#0284c7" stroke="#38bdf8" stroke-width="2" />
    <text x="970" y="51" fill="#ffffff" font-size="14" font-weight="800" text-anchor="middle">Truk Masuk ke Yard Membawa e-Pass CMS QR</text>

    <line x1="970" y1="70" x2="970" y2="110" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr3-blue)" />

    <!-- Decision Diamond: Cek Jenis Kontainer -->
    <polygon points="970,110 1180,180 970,250 760,180" fill="#1a2333" stroke="#f59e0b" stroke-width="2" />
    <text x="970" y="170" fill="#fbbf24" font-size="13.5" font-weight="800" text-anchor="middle">CEK SPESIFIKASI PETI KEMAS:</text>
    <text x="970" y="190" fill="#f8fafc" font-size="12" text-anchor="middle">Ukuran, Bobot, &amp; Kondisi Muatan?</text>

    <!-- Branch 1: 40 Feet Laden -->
    <path d="M 760 180 L 320 180 L 320 280" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr3-blue)" />
    <rect x="150" y="280" width="340" height="130" rx="8" fill="#132338" stroke="#0284c7" stroke-width="1.5" />
    <text x="165" y="305" fill="#38bdf8" font-size="13" font-weight="800">1. KONTAINER 40 FEET LADEN (BERAT):</text>
    <text x="165" y="328" fill="#f8fafc" font-size="11.5">&bull; <b>ALAT ANGKAT:</b> <b>RTG Crane 45 Ton</b> /</text>
    <text x="165" y="346" fill="#f8fafc" font-size="11.5">&nbsp;&nbsp;<b>Reach Stacker 45 Ton</b> (Telescopic Spreader)</text>
    <text x="165" y="366" fill="#cbd5e1" font-size="11.5">&bull; <b>OPERATOR:</b> Driver RTG berlisensi SIO Kemnaker</text>
    <text x="165" y="384" fill="#cbd5e1" font-size="11.5">&bull; <b>INTERFACE:</b> Work Instruction via Layar VMT</text>
    <text x="165" y="401" fill="#93c5fd" font-size="11">&bull; pages/alat.php</text>

    <!-- Branch 2: 20 Feet Empty -->
    <path d="M 865 215 L 750 240 L 750 280" fill="none" stroke="#38bdf8" stroke-width="2" marker-end="url(#arr3-blue)" />
    <rect x="580" y="280" width="340" height="130" rx="8" fill="#132338" stroke="#0284c7" stroke-width="1.5" />
    <text x="595" y="305" fill="#38bdf8" font-size="13" font-weight="800">2. KONTAINER 20 FEET EMPTY (KOSONG):</text>
    <text x="595" y="328" fill="#f8fafc" font-size="11.5">&bull; <b>ALAT ANGKAT:</b> <b>Empty Container Handler (ECH) 10T</b></text>
    <text x="595" y="348" fill="#cbd5e1" font-size="11.5">&bull; <b>OPERATOR:</b> Driver ECH berlisensi SIO</text>
    <text x="595" y="368" fill="#cbd5e1" font-size="11.5">&bull; <b>KECEPATAN:</b> Stacking cepat hingga 8 tier</text>
    <text x="595" y="388" fill="#cbd5e1" font-size="11.5">&bull; <b>TUJUAN:</b> Empty Container Depot Blok D</text>
    <text x="595" y="401" fill="#93c5fd" font-size="11">&bull; pages/alat.php</text>

    <!-- Branch 3: Rel Siding KA -->
    <path d="M 1075 215 L 1190 240 L 1190 280" fill="none" stroke="#6366f1" stroke-width="2" marker-end="url(#arr3-blue)" />
    <rect x="1020" y="280" width="340" height="130" rx="8" fill="#1e2238" stroke="#6366f1" stroke-width="1.5" />
    <text x="1035" y="305" fill="#818cf8" font-size="13" font-weight="800">3. REL SIDING KERETA API 600M:</text>
    <text x="1035" y="328" fill="#f8fafc" font-size="11.5">&bull; <b>ALAT ANGKAT:</b> <b>RMG Crane 45 Ton</b> /</text>
    <text x="1035" y="346" fill="#f8fafc" font-size="11.5">&nbsp;&nbsp;<b>Reach Stacker Intermodal Leg</b></text>
    <text x="1035" y="366" fill="#cbd5e1" font-size="11.5">&bull; <b>OPERATOR:</b> Intermodal Crane Operator</text>
    <text x="1035" y="384" fill="#cbd5e1" font-size="11.5">&bull; <b>SPEED:</b> Target &lt; 4 menit per peti kemas</text>
    <text x="1035" y="401" fill="#a5b4fc" font-size="11">&bull; pages/intermodal.php</text>

    <!-- Branch 4: CFS Palet -->
    <path d="M 1180 180 L 1620 180 L 1620 280" fill="none" stroke="#60a5fa" stroke-width="2" marker-end="url(#arr3-blue)" />
    <rect x="1450" y="280" width="340" height="130" rx="8" fill="#162438" stroke="#60a5fa" stroke-width="1.5" />
    <text x="1465" y="305" fill="#60a5fa" font-size="13" font-weight="800">4. KARGO CURAH / PALET DI CFS:</text>
    <text x="1465" y="328" fill="#f8fafc" font-size="11.5">&bull; <b>ALAT ANGKAT:</b> <b>Forklift 3-5 Ton</b> (Diesel/Electric)</text>
    <text x="1465" y="348" fill="#cbd5e1" font-size="11.5">&bull; <b>OPERATOR:</b> CFS Forklift Driver &amp; Tally Gudang</text>
    <text x="1465" y="368" fill="#cbd5e1" font-size="11.5">&bull; <b>RAK CFS:</b> Aisle-Rack-Shelf-Bin Selective</text>
    <text x="1465" y="388" fill="#cbd5e1" font-size="11.5">&bull; <b>ATURAN:</b> FEFO &amp; FIFO Automated Locking</text>
    <text x="1465" y="401" fill="#93c5fd" font-size="11">&bull; pages/cfs.php</text>

    <!-- Horizontal Bar to Stacking Rules Engine -->
    <line x1="320" y1="410" x2="320" y2="470" stroke="#38bdf8" stroke-width="2" />
    <line x1="750" y1="410" x2="750" y2="470" stroke="#38bdf8" stroke-width="2" />
    <line x1="1190" y1="410" x2="1190" y2="470" stroke="#6366f1" stroke-width="2" />
    <line x1="1620" y1="410" x2="1620" y2="470" stroke="#60a5fa" stroke-width="2" />
    
    <line x1="320" y1="470" x2="1620" y2="470" stroke="#38bdf8" stroke-width="2" />
    <line x1="970" y1="470" x2="970" y2="520" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr3-blue)" />

    <!-- STACKING RULES 3D ENGINE (YMS) -->
    <rect x="420" y="520" width="1100" height="520" rx="12" fill="#0f172a" stroke="#2563eb" stroke-width="2" />
    <text x="970" y="555" fill="#38bdf8" font-size="17" font-weight="800" text-anchor="middle">ATURAN PENEMPATAN SEPERTI APA? (YMS 3D STACKING RULES)</text>
    <text x="970" y="575" fill="#94a3b8" font-size="12" text-anchor="middle">Hierarki Koordinat 4 Dimensi: [BLOK] - [BAY] - [ROW] - [TIER] (pages/yard.php)</text>

    <!-- Sub-Box A: Aturan Bay -->
    <rect x="450" y="600" width="500" height="180" rx="8" fill="#141f36" stroke="#1d4ed8" />
    <text x="470" y="625" fill="#93c5fd" font-size="13.5" font-weight="800">ATURAN UKURAN (BAY RULE 20FT VS 40FT):</text>
    <text x="470" y="650" fill="#f8fafc" font-size="12">&bull; <b>Bay Ganjil (01, 03, 05, 07):</b> Khusus 2 kontainer 20 Feet</text>
    <text x="470" y="670" fill="#f8fafc" font-size="12">&bull; <b>Bay Genap (02, 06, 10, 14):</b> Khusus 1 kontainer 40 Feet</text>
    <text x="470" y="690" fill="#94a3b8" font-size="11.5">&nbsp;&nbsp;(Kontainer 40ft menempati bentang 2 unit bay 20ft)</text>
    <text x="470" y="715" fill="#fca5a5" font-size="12" font-weight="700">&bull; ATURAN KESELAMATAN (SAFETY LOCK):</text>
    <text x="470" y="735" fill="#fecaca" font-size="11.5">&nbsp;&nbsp;Dilarang keras menumpuk 40ft di atas 20ft tanpa twistlock.</text>
    <text x="470" y="755" fill="#fecaca" font-size="11.5">&nbsp;&nbsp;Dilarang menumpuk 20ft di atas 40ft di lapangan terbuka.</text>

    <!-- Sub-Box B: Aturan Berat Tier -->
    <rect x="990" y="600" width="500" height="180" rx="8" fill="#141f36" stroke="#1d4ed8" />
    <text x="1010" y="625" fill="#93c5fd" font-size="13.5" font-weight="800">ATURAN DISTRIBUSI BERAT (WEIGHT TIER RULE):</text>
    <text x="1010" y="650" fill="#f8fafc" font-size="12">&bull; <b>Tier 1 &amp; Tier 2 (Lantai Dasar):</b> Berat Berat (&gt; 24 Ton)</text>
    <text x="1010" y="670" fill="#94a3b8" font-size="11.5">&nbsp;&nbsp;Menjaga kestabilan Center of Gravity tumpukan.</text>
    <text x="1010" y="695" fill="#f8fafc" font-size="12">&bull; <b>Tier 3 (Tengah):</b> Berat Sedang (15 s/d 24 Ton)</text>
    <text x="1010" y="720" fill="#f8fafc" font-size="12">&bull; <b>Tier 4 &amp; Tier 5 (Paling Atas):</b> Berat Ringan (&lt; 15 Ton) / Kosong</text>
    <text x="1010" y="740" fill="#fde047" font-size="11.5" font-weight="700">&bull; Tujuan Bisnis: Mencegah keruntuhan tumpukan akibat</text>
    <text x="1010" y="758" fill="#fde047" font-size="11.5">&nbsp;&nbsp;hembusan angin badai di pelabuhan kering terbuka.</text>

    <!-- Sub-Box C: Zonasi Karakteristik -->
    <rect x="450" y="800" width="1040" height="120" rx="8" fill="#132338" stroke="#0284c7" />
    <text x="470" y="825" fill="#38bdf8" font-size="13.5" font-weight="800">ATURAN ZONASI KHUSUS &amp; KELAYAKAN TATA LETAK:</text>
    <text x="470" y="848" fill="#cbd5e1" font-size="12">&bull; <b>Reefer (Dingin):</b> Blok E hanya pada tiang bercolokan 380V. Maksimal Tier 3 agar mudah dicek teknisi.</text>
    <text x="470" y="868" fill="#cbd5e1" font-size="12">&bull; <b>HazMat B3:</b> Blok F tanggul penampung tumpahan kimia, jarak aman &ge; 50 meter dari gudang CFS dan kantor.</text>
    <text x="470" y="888" fill="#cbd5e1" font-size="12">&bull; <b>Ekspor vs Impor:</b> Boks siap muat KA dekat rel siding; boks impor siap ambil dekat gate-out.</text>
    <text x="470" y="908" fill="#34d399" font-size="11.5" font-weight="700">&bull; Hasil Akhir: Sistem mengunci alamat slot (Contoh: YD-A-04-03-02) &amp; operator RTG mengeksekusi angkat.</text>

    <!-- Exception Handling -->
    <rect x="450" y="940" width="1040" height="80" rx="8" fill="#1c1917" stroke="#f59e0b" />
    <text x="470" y="965" fill="#fbbf24" font-size="12.5" font-weight="800">PENANGANAN ANOMALI (EXCEPTION HANDLING):</text>
    <text x="470" y="985" fill="#cbd5e1" font-size="11.5">Jika slot yang dituju rusak / terisi tidak sah &rarr; YMS secara otomatis mengalokasikan Slot Sekunder dalam hitungan detik</text>
    <text x="470" y="1003" fill="#cbd5e1" font-size="11.5">dan mengirim sinyal update ke VMT layar kabin operator alat tanpa menghentikan lalu lintas yard.</text>

    <line x1="970" y1="1040" x2="970" y2="1090" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr3-blue)" />

    <!-- Final Output -->
    <rect x="750" y="1090" width="440" height="55" rx="27" fill="#059669" stroke="#34d399" stroke-width="2" />
    <text x="970" y="1123" fill="#ffffff" font-size="13.5" font-weight="800" text-anchor="middle">STATUS: KONTAINER TERSUSUN AMAN DI YARD (LOCKED)</text>

  </svg>
</body>
</html>
"""

# =============================================================================
# 4. FLOWCHART 4: ALUR INTEGRASI 5 SISTEM SESUAI BAB 12 & 13 SILABUS
# =============================================================================
html_integration_flowchart = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Flowchart Integrasi 5 Sistem Logistik</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #090e17;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    color: #f8fafc;
    width: 2200px;
    height: 1400px;
    padding: 30px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 16px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>ALUR INTEGRASI 5 MODUL LOGISTIK TERPADU (BAB 12 &amp; 13 SILABUS)</h1>
      <p>ERP &rarr; WMS &rarr; WCS &rarr; YMS &rarr; TMS &rarr; INVOICING (COLD CHAIN &amp; DRY PORT BLUEPRINT)</p>
    </div>
    <div style="background:#0f172a; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Arsitektur CIDP: 5 Modul Terintegrasi
    </div>
  </div>

  <svg width="2140" height="1280" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <marker id="arr4-blue" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 9 5 L 0 9 z" fill="#38bdf8" /></marker>
    </defs>

    <!-- 5 Module Stage Rectangles Horizontal Sequence -->
    <!-- MODUL 1: ERP -->
    <rect x="50" y="100" width="370" height="980" rx="10" fill="#0f172a" stroke="#0284c7" stroke-width="2" />
    <text x="235" y="140" fill="#38bdf8" font-size="18" font-weight="800" text-anchor="middle">1. ERP (ENTERPRISE)</text>
    <text x="235" y="165" fill="#94a3b8" font-size="12" text-anchor="middle">pages/billing.php &amp; Odoo ERP</text>
    
    <rect x="70" y="200" width="330" height="220" rx="8" fill="#132338" stroke="#334155" />
    <text x="90" y="230" fill="#93c5fd" font-size="13" font-weight="700">Fokus: Pesanan &amp; Master Data</text>
    <text x="90" y="260" fill="#cbd5e1" font-size="12">&bull; Menerima Sales Order / PO</text>
    <text x="90" y="280" fill="#cbd5e1" font-size="12">&bull; Threshold Suhu: Chilled 2-8&deg;C</text>
    <text x="90" y="300" fill="#cbd5e1" font-size="12">&bull; Frozen: -18&deg;C (Kargo Beku)</text>
    <text x="90" y="320" fill="#cbd5e1" font-size="12">&bull; Tarif Terminal THC Odoo</text>
    <text x="90" y="340" fill="#cbd5e1" font-size="12">&bull; Validasi SPPB CEISA 4.0</text>
    <text x="90" y="365" fill="#34d399" font-size="11.5" font-weight="700">&rarr; Kirim SO via REST API ke WMS</text>

    <!-- Line ERP to WMS -->
    <line x1="420" y1="310" x2="480" y2="310" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr4-blue)" />

    <!-- MODUL 2: WMS -->
    <rect x="480" y="100" width="370" height="980" rx="10" fill="#0f172a" stroke="#0284c7" stroke-width="2" />
    <text x="665" y="140" fill="#38bdf8" font-size="18" font-weight="800" text-anchor="middle">2. WMS (WAREHOUSE)</text>
    <text x="665" y="165" fill="#94a3b8" font-size="12" text-anchor="middle">pages/cfs.php &amp; pages/reefer.php</text>

    <rect x="500" y="200" width="330" height="240" rx="8" fill="#132338" stroke="#334155" />
    <text x="520" y="230" fill="#93c5fd" font-size="13" font-weight="700">Fokus: Cold Storage &amp; CFS</text>
    <text x="520" y="260" fill="#cbd5e1" font-size="12">&bull; Pecah Pick Task per Zona Suhu</text>
    <text x="520" y="280" fill="#cbd5e1" font-size="12">&bull; Kunci FEFO (First Expired Out)</text>
    <text x="520" y="300" fill="#cbd5e1" font-size="12">&bull; Batasi Max Exposure Time</text>
    <text x="520" y="320" fill="#cbd5e1" font-size="12">&bull; Rak Aisle-Rack-Shelf-Bin</text>
    <text x="520" y="340" fill="#cbd5e1" font-size="12">&bull; ASN Matching Over-receipt</text>
    <text x="520" y="365" fill="#34d399" font-size="11.5" font-weight="700">&rarr; Kirim Task via Socket ke WCS</text>

    <!-- Line WMS to WCS -->
    <line x1="850" y1="310" x2="910" y2="310" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr4-blue)" />

    <!-- MODUL 3: WCS -->
    <rect x="910" y="100" width="370" height="980" rx="10" fill="#0f172a" stroke="#0284c7" stroke-width="2" />
    <text x="1095" y="140" fill="#38bdf8" font-size="18" font-weight="800" text-anchor="middle">3. WCS (HARDWARE CONTROL)</text>
    <text x="1095" y="165" fill="#94a3b8" font-size="12" text-anchor="middle">pages/gate.php &amp; pages/scanner.php</text>

    <rect x="930" y="200" width="330" height="240" rx="8" fill="#132338" stroke="#334155" />
    <text x="950" y="230" fill="#93c5fd" font-size="13" font-weight="700">Fokus: Eksekusi Otomasi Fisik</text>
    <text x="950" y="260" fill="#cbd5e1" font-size="12">&bull; Jembatan Timbang Avery 80T</text>
    <text x="950" y="280" fill="#cbd5e1" font-size="12">&bull; Sensor ANPR &amp; OCR ISO 6346</text>
    <text x="950" y="300" fill="#cbd5e1" font-size="12">&bull; Palang Barrier Gate Buka/Kunci</text>
    <text x="950" y="320" fill="#cbd5e1" font-size="12">&bull; Kontrol AS/RS di Freezer -18&deg;C</text>
    <text x="950" y="340" fill="#cbd5e1" font-size="12">&bull; Conveyor Diverter ke Reject Lane</text>
    <text x="950" y="365" fill="#34d399" font-size="11.5" font-weight="700">&rarr; Sinyal Berat &amp; Posisi ke YMS</text>

    <!-- Line WCS to YMS -->
    <line x1="1280" y1="310" x2="1340" y2="310" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr4-blue)" />

    <!-- MODUL 4: YMS -->
    <rect x="1340" y="100" width="370" height="980" rx="10" fill="#0f172a" stroke="#0284c7" stroke-width="2" />
    <text x="1525" y="140" fill="#38bdf8" font-size="18" font-weight="800" text-anchor="middle">4. YMS (YARD MANAGEMENT)</text>
    <text x="1525" y="165" fill="#94a3b8" font-size="12" text-anchor="middle">pages/yard.php &amp; pages/alat.php</text>

    <rect x="1360" y="200" width="330" height="240" rx="8" fill="#132338" stroke="#334155" />
    <text x="1380" y="230" fill="#93c5fd" font-size="13" font-weight="700">Fokus: Yard 35 Ha &amp; Dock</text>
    <text x="1380" y="260" fill="#cbd5e1" font-size="12">&bull; Penjadwalan Dock Inflatable Seal</text>
    <text x="1380" y="280" fill="#cbd5e1" font-size="12">&bull; Stacking 3D Bay-Row-Tier</text>
    <text x="1380" y="300" fill="#cbd5e1" font-size="12">&bull; Dispatch RTG 45T &amp; RS 45T</text>
    <text x="1380" y="320" fill="#cbd5e1" font-size="12">&bull; Monitoring Dwell Time &amp; Demurrage</text>
    <text x="1380" y="340" fill="#cbd5e1" font-size="12">&bull; Blok Reefer Plugs 380V</text>
    <text x="1380" y="365" fill="#34d399" font-size="11.5" font-weight="700">&rarr; Dock Assigned ke TMS</text>

    <!-- Line YMS to TMS -->
    <line x1="1710" y1="310" x2="1770" y2="310" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#arr4-blue)" />

    <!-- MODUL 5: TMS -->
    <rect x="1770" y="100" width="330" height="980" rx="10" fill="#0f172a" stroke="#0284c7" stroke-width="2" />
    <text x="1935" y="140" fill="#38bdf8" font-size="18" font-weight="800" text-anchor="middle">5. TMS (TRANSPORT)</text>
    <text x="1935" y="165" fill="#94a3b8" font-size="12" text-anchor="middle">pages/trucking.php &amp; intermodal.php</text>

    <rect x="1790" y="200" width="290" height="240" rx="8" fill="#132338" stroke="#334155" />
    <text x="1810" y="230" fill="#93c5fd" font-size="13" font-weight="700">Fokus: Truk &amp; Kereta Api</text>
    <text x="1810" y="260" fill="#cbd5e1" font-size="12">&bull; Dynamic Route Optimization</text>
    <text x="1810" y="280" fill="#cbd5e1" font-size="12">&bull; Pelacakan GPS Suhu Real-time</text>
    <text x="1810" y="300" fill="#cbd5e1" font-size="12">&bull; e-POD Bukti Digital</text>
    <text x="1810" y="320" fill="#cbd5e1" font-size="12">&bull; Sinkronisasi Rel Siding KA 600m</text>
    <text x="1810" y="340" fill="#cbd5e1" font-size="12">&bull; Audit Rate Carrier Settlement</text>
    <text x="1810" y="365" fill="#34d399" font-size="11.5" font-weight="700">&rarr; Invoicing Selesai ke ERP</text>

    <!-- Return Line to ERP Invoicing -->
    <path d="M 1935 440 L 1935 520 L 235 520 L 235 420" fill="none" stroke="#10b981" stroke-width="2.5" marker-end="url(#arr4-blue)" />
    <rect x="950" y="505" width="280" height="30" rx="4" fill="#064e3b" stroke="#10b981" />
    <text x="1090" y="525" fill="#a7f3d0" font-size="11.5" font-weight="800" text-anchor="middle">SINKRONISASI INVOICING &amp; ZERO LEAKAGE</text>

  </svg>
</body>
</html>
"""

def generate_flowchart_jpgs():
    with sync_playwright() as p:
        browser = p.chromium.launch()

        # 1. End to End Flowchart
        p1 = browser.new_page(viewport={"width": 2500, "height": 1550}, device_scale_factor=1.5)
        p1.set_content(html_e2e_flowchart)
        out_e2e = os.path.join(output_dir, "FLOWCHART_END_TO_END_PROSES_BISNIS_CIDP.jpg")
        p1.screenshot(path=out_e2e, type="jpeg", quality=95)
        print("Generated Flowchart:", out_e2e)
        
        # Also overwrite ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg so it is an authentic flowchart!
        out_e2e_orig = os.path.join(output_dir, "ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg")
        p1.screenshot(path=out_e2e_orig, type="jpeg", quality=95)
        print("Updated Flowchart:", out_e2e_orig)
        p1.close()

        # 2. Gate Decision Flowchart
        p2 = browser.new_page(viewport={"width": 2000, "height": 1400}, device_scale_factor=1.5)
        p2.set_content(html_gate_flowchart)
        out_gate = os.path.join(output_dir, "FLOWCHART_TIMBANGAN_GATE_IN_DECISION.jpg")
        p2.screenshot(path=out_gate, type="jpeg", quality=95)
        print("Generated Flowchart:", out_gate)

        out_gate_orig = os.path.join(output_dir, "ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg")
        p2.screenshot(path=out_gate_orig, type="jpeg", quality=95)
        print("Updated Flowchart:", out_gate_orig)
        p2.close()

        # 3. Lifting & Stacking Flowchart
        p3 = browser.new_page(viewport={"width": 2000, "height": 1400}, device_scale_factor=1.5)
        p3.set_content(html_lifting_flowchart)
        out_lift = os.path.join(output_dir, "FLOWCHART_PENGANGKATAN_DAN_STACKING_KONTAINER.jpg")
        p3.screenshot(path=out_lift, type="jpeg", quality=95)
        print("Generated Flowchart:", out_lift)

        out_lift_orig = os.path.join(output_dir, "ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg")
        p3.screenshot(path=out_lift_orig, type="jpeg", quality=95)
        print("Updated Flowchart:", out_lift_orig)
        p3.close()

        # 4. Integration Flowchart
        p4 = browser.new_page(viewport={"width": 2200, "height": 1400}, device_scale_factor=1.5)
        p4.set_content(html_integration_flowchart)
        out_integ = os.path.join(output_dir, "FLOWCHART_INTEGRASI_5_MODUL_LOGISTIK.jpg")
        p4.screenshot(path=out_integ, type="jpeg", quality=95)
        print("Generated Flowchart:", out_integ)
        p4.close()

        browser.close()

if __name__ == "__main__":
    generate_flowchart_jpgs()
