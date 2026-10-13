import os
import sys
from playwright.sync_api import sync_playwright

output_dir = r"C:\xampp\htdocs\Conclusion_Intermodal_Dry_Port\docs\business_analyst"
os.makedirs(output_dir, exist_ok=True)

html_content_end_to_end = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Alur End-to-End Proses Bisnis CIDP</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    background: #090d16;
    color: #f1f5f9;
    padding: 35px;
    width: 2400px;
    height: 1400px;
  }
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #1e293b;
    padding-bottom: 18px;
    margin-bottom: 25px;
  }
  .header-left h1 {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.5px;
  }
  .header-left p {
    font-size: 16px;
    color: #38bdf8;
    font-weight: 600;
    margin-top: 4px;
  }
  .header-right {
    display: flex;
    gap: 15px;
  }
  .tag {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .tag-blue { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid #0284c7; }
  .tag-emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #059669; }
  .tag-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid #d97706; }

  /* 5 Module Architecture Ribbon */
  .system-ribbon {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 25px;
  }
  .sys-badge {
    padding: 10px 14px;
    border-radius: 8px;
    background: #111827;
    border: 1px solid #1f2937;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .sys-badge .name { font-size: 13px; font-weight: 800; color: #93c5fd; }
  .sys-badge .role { font-size: 12px; color: #94a3b8; }
  .sys-badge .app { font-size: 11px; color: #38bdf8; font-family: monospace; font-weight: 600; }

  /* Main Workflow Canvas: 5 Steps Horizontal */
  .workflow-grid {
    display: grid;
    grid-template-columns: 460px 480px 480px 430px 430px;
    gap: 18px;
    height: 1150px;
  }

  .stage-col {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: relative;
  }
  .stage-col.highlight {
    border-color: #0284c7;
    background: linear-gradient(180deg, rgba(14, 165, 233, 0.05) 0%, rgba(15, 23, 42, 0.9) 100%);
  }
  .stage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #334155;
    padding-bottom: 10px;
  }
  .stage-num {
    background: #0284c7;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
  }
  .stage-title {
    font-size: 17px;
    font-weight: 800;
    color: #ffffff;
    flex-grow: 1;
    margin-left: 10px;
  }
  .stage-sys {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    background: #1e293b;
    color: #38bdf8;
    border: 1px solid #334155;
  }

  /* Card components */
  .card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13px;
  }
  .card-role {
    background: #132038;
    border-color: #1e3a5f;
  }
  .card-role .title {
    color: #38bdf8;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .card-role .content {
    color: #e2e8f0;
    font-size: 12.5px;
    line-height: 1.4;
  }

  .card-decision {
    background: #1a2234;
    border: 1px solid #3b82f6;
    border-left: 5px solid #3b82f6;
    padding: 12px;
  }
  .card-decision .title {
    color: #60a5fa;
    font-weight: 800;
    font-size: 13px;
    margin-bottom: 6px;
  }
  .card-decision .condition {
    background: #090d16;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: #cbd5e1;
    margin-bottom: 8px;
    border: 1px dashed #475569;
  }

  .decision-branch {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .branch-pass {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid #059669;
    border-radius: 6px;
    padding: 8px 10px;
  }
  .branch-pass .label {
    color: #34d399;
    font-weight: 800;
    font-size: 11.5px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .branch-pass .desc {
    color: #e2e8f0;
    font-size: 11.5px;
    margin-top: 3px;
    line-height: 1.35;
  }

  .branch-fail {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid #b91c1c;
    border-radius: 6px;
    padding: 8px 10px;
  }
  .branch-fail .label {
    color: #f87171;
    font-weight: 800;
    font-size: 11.5px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .branch-fail .desc {
    color: #e2e8f0;
    font-size: 11.5px;
    margin-top: 3px;
    line-height: 1.35;
  }

  .equipment-box {
    background: #172033;
    border: 1px solid #2d3b55;
    border-radius: 8px;
    padding: 10px 12px;
  }
  .equipment-box .eq-header {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    color: #f59e0b;
    font-size: 12px;
    margin-bottom: 4px;
  }
  .equipment-box .eq-desc {
    font-size: 12px;
    color: #cbd5e1;
    line-height: 1.35;
  }

  .stack-rule-box {
    background: #141f36;
    border: 1px solid #2563eb;
    border-radius: 8px;
    padding: 10px 12px;
  }
  .stack-rule-box .title {
    color: #93c5fd;
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 5px;
  }
  .stack-rule-box ul {
    list-style: none;
    font-size: 11.5px;
    color: #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .stack-rule-box li span {
    color: #38bdf8;
    font-weight: 700;
  }

  .footer-note {
    background: #0f172a;
    border-top: 1px solid #1e293b;
    padding-top: 12px;
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #64748b;
  }
</style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <div class="header-left">
      <h1>BLUEPRINT ALUR PROSES BISNIS END-TO-END CIDP 35 HA</h1>
      <p>PEMETAAN BUSINESS ROLE, BUSINESS RULES, EQUIPMENT ASSIGNMENT, VALIDASI TIMBANGAN &amp; ARSITEKTUR 5 SISTEM</p>
    </div>
    <div class="header-right">
      <div class="tag tag-blue">Modul BA &amp; QA Silabus Resmi</div>
      <div class="tag tag-emerald">Kepatuhan SOLAS VGM &amp; ISO 6346</div>
      <div class="tag tag-amber">ITL Trisakti — Topik 7</div>
    </div>
  </div>

  <!-- 5 SYSTEM ARCHITECTURE RIBBON -->
  <div class="system-ribbon">
    <div class="sys-badge">
      <span class="name">1. ERP (Odoo / Master)</span>
      <span class="role">Master Data, Kontrak, THC, Demurrage &amp; Invoicing</span>
      <span class="app">Lokasi: pages/billing.php</span>
    </div>
    <div class="sys-badge">
      <span class="name">2. WMS (Warehouse)</span>
      <span class="role">CFS Inbound/Outbound, Aisle-Rack-Bin, FEFO/FIFO</span>
      <span class="app">Lokasi: pages/cfs.php, reefer.php</span>
    </div>
    <div class="sys-badge">
      <span class="name">3. WCS (Hardware Control)</span>
      <span class="role">Timbangan 80T, ANPR, OCR ISO, Palang Barrier, Diverter</span>
      <span class="app">Lokasi: pages/gate.php, scanner.php</span>
    </div>
    <div class="sys-badge">
      <span class="name">4. YMS (Yard Management)</span>
      <span class="role">3D Bay-Row-Tier, Stacking Rules, RTG/RS Dispatch, Dwell</span>
      <span class="app">Lokasi: pages/yard.php, alat.php</span>
    </div>
    <div class="sys-badge">
      <span class="name">5. TMS (Transport Management)</span>
      <span class="role">Fleet Dispatch, Dynamic Route, e-POD, Rel Siding KA</span>
      <span class="app">Lokasi: pages/trucking.php, intermodal.php</span>
    </div>
  </div>

  <!-- 5 WORKFLOW STAGES HORIZONTAL -->
  <div class="workflow-grid">

    <!-- STAGE 1: GATE-IN & WEIGHBRIDGE -->
    <div class="stage-col highlight">
      <div class="stage-header">
        <div class="stage-num">1</div>
        <div class="stage-title">Gate-In &amp; Timbangan</div>
        <div class="stage-sys">WCS + YMS</div>
      </div>

      <!-- ROLE & HARDWARE -->
      <div class="card card-role">
        <div class="title">👤 Role Manusia &amp; 🛠️ Alat</div>
        <div class="content">
          <b>Role:</b> Driver Truk Eksternal, Gate Clerk / Tallyman Inbound.<br>
          <b>Alat:</b> Jembatan Timbang Pitless 80 Ton (Load Cell Avery), Kamera ANPR Plat Truk, Kamera OCR Boks ISO 6346, Palang Barrier Otomatis.
        </div>
      </div>

      <!-- BUSINESS RULE & DECISION -->
      <div class="card-decision">
        <div class="title">⚖️ Business Rule Timbangan:</div>
        <div class="condition">
          <b>Parameter Input:</b> Gross Weight, Tare Weight, Net Weight, VGM SOLAS Dokumen.<br>
          <b>Validasi:</b><br>
          1. Berat Kotor Total &le; 34.000 kg?<br>
          2. Toleransi Berat &plusmn; 2% vs Dokumen VGM?<br>
          3. Check Digit ISO 6346 Modulo 11 Valid?
        </div>

        <div class="decision-branch">
          <!-- JIKA LULUS -->
          <div class="branch-pass">
            <div class="label">✅ JIKA LULUS (Lolos Timbang &amp; Valid):</div>
            <div class="desc">
              &bull; Palang Barrier Otomatis Terbuka.<br>
              &bull; Status Database: <code>GATE_IN_APPROVED</code>.<br>
              &bull; Kiosk Cetak <b>Container Movement Slip (CMS) QR</b>.<br>
              &bull; <b>Truk diarahkan ke:</b><br>
              &nbsp;&nbsp;- FCL Dry &rarr; Blok Yard A/B/C/D.<br>
              &nbsp;&nbsp;- Reefer &rarr; Blok E (Colokan Listrik 380V).<br>
              &nbsp;&nbsp;- LCL Cargo &rarr; Gudang CFS Dock D1-D5.<br>
              &nbsp;&nbsp;- HazMat &rarr; Blok F (Bunded Area).<br>
              &nbsp;&nbsp;- Jalur Merah &rarr; Behandle Bea Cukai.
            </div>
          </div>

          <!-- JIKA TIDAK LULUS -->
          <div class="branch-fail">
            <div class="label">❌ JIKA GAGAL (Overweight / Anomali):</div>
            <div class="desc">
              &bull; Palang Barrier <b>TETAP TERKUNCI (LOCK)</b>.<br>
              &bull; Lampu Merah &amp; Alarm Sirine Otomatis Aktif.<br>
              &bull; Status Database: <code>OVERLOAD_REJECTED</code>.<br>
              &bull; <b>Truk diarahkan ke mana?</b><br>
              &nbsp;&nbsp;&rarr; Masuk <b>REJECT LANE / HOLDING BAY</b> (Area Karantina Timbangan samping gate).<br>
              &nbsp;&nbsp;&rarr; Investigasi dokumen oleh Supervisor.<br>
              &nbsp;&nbsp;&rarr; Jika over-capacity mutlak &rarr; Belok U-Turn keluar terminal untuk proteksi jalan beton K-450.
            </div>
          </div>
        </div>
      </div>

      <div class="equipment-box">
        <div class="eq-header">
          <span>📦 Barangnya Apa?</span>
          <span>Status Inbound</span>
        </div>
        <div class="eq-desc">
          Dry Container 20ft/40ft, Reefer (Perishable/Cold), DG/HazMat B3, atau Kargo Curah Palet LCL CFS.
        </div>
      </div>
    </div>

    <!-- STAGE 2: PENGANGKATAN & STACKING YARD -->
    <div class="stage-col">
      <div class="stage-header">
        <div class="stage-num">2</div>
        <div class="stage-title">Pengangkatan &amp; Yard</div>
        <div class="stage-sys">YMS + WCS</div>
      </div>

      <!-- SIAPA YANG NGANGKAT? -->
      <div class="card card-role">
        <div class="title">🚜 SIAPA YANG NGANGKAT KONTAINER?</div>
        <div class="content">
          <b>Kontainer 40 Feet Laden (Isi Berat):</b><br>
          &bull; <b>Alat:</b> <b>RTG Crane 45 Ton</b> (Rubber Tyred Gantry) atau <b>Reach Stacker 45 Ton</b> (Kalmar/Konecranes).<br>
          &bull; <b>Operator:</b> RTG/RS Operator berlisensi SIO Kemnaker via VMT di kabin.<br><br>
          <b>Kontainer 20 Feet Kosong (Empty):</b><br>
          &bull; <b>Alat:</b> <b>Empty Container Handler (ECH) 10 Ton</b>.<br>
          &bull; <b>Operator:</b> ECH Operator (stacking cepat s/d Tier 6).<br><br>
          <b>Barang Palet di Gudang CFS:</b><br>
          &bull; <b>Alat:</b> <b>Forklift 3-5 Ton</b> (Diesel/Electric).<br>
          &bull; <b>Operator:</b> CFS Forklift Operator.
        </div>
      </div>

      <!-- PENEMPATANNYA SEPERTI APA? -->
      <div class="stack-rule-box">
        <div class="title">📐 PENEMPATANNYA SEPERTI APA? (STACKING RULES)</div>
        <ul>
          <li><span>1. Struktur 3D:</span> Block &rarr; Bay &rarr; Row &rarr; Tier.</li>
          <li><span>2. Aturan Ukuran (Bay Rule):</span><br>
            &bull; Kontainer 20ft: Ditaruh di Bay Ganjil (01, 03, 05...).<br>
            &bull; Kontainer 40ft: Ditaruh di Bay Genap (02, 06, 10...). Dilarang menumpuk 40ft di atas 20ft tanpa twistlock.
          </li>
          <li><span>3. Aturan Berat (Weight Rule):</span><br>
            &bull; Tier 1-2 (Bawah): Heavy Laden (&gt; 24 Ton) untuk Center of Gravity stabil.<br>
            &bull; Tier 3 (Tengah): Medium (15-24 Ton).<br>
            &bull; Tier 4-5 (Atas): Light / Empty (&lt; 15 Ton). Mencegah roboh ditiup angin.
          </li>
          <li><span>4. Zonasi Khusus:</span><br>
            &bull; Reefer: Hanya di Blok E (dekat panel colokan).<br>
            &bull; HazMat: Blok F berjarak &ge; 50m dari CFS, berparit.
          </li>
        </ul>
      </div>

      <div class="card-decision">
        <div class="title">🔄 Exception Handling Stacking:</div>
        <div class="condition">
          Jika slot yang dituju terisi/rusak &rarr; YMS Otomatis re-assign ke Slot Sekunder &amp; notifikasi instan ke VMT operator alat.
        </div>
      </div>
    </div>

    <!-- STAGE 3: CFS WAREHOUSE & REEFER COLD CHAIN -->
    <div class="stage-col">
      <div class="stage-header">
        <div class="stage-num">3</div>
        <div class="stage-title">CFS &amp; Reefer Storage</div>
        <div class="stage-sys">WMS + WCS</div>
      </div>

      <!-- ROLE -->
      <div class="card card-role">
        <div class="title">👤 Role Manusia &amp; 🛠️ Alat</div>
        <div class="content">
          <b>Role:</b> Tally CFS Inbound, Forklift Driver, Reefer Technician, QC Inspector.<br>
          <b>Alat:</b> Dock Leveler D1-D5, Forklift 3-5T, Handheld Barcode PDA, Sensor Suhu Digital, Power Plug 380V.
        </div>
      </div>

      <!-- CFS WMS RULES -->
      <div class="card" style="background:#132337; border-color:#0284c7;">
        <div style="color:#38bdf8; font-weight:700; margin-bottom:6px; font-size:12px;">🏬 Aturan WMS Inbound &amp; CFS:</div>
        <div style="font-size:11.5px; color:#e2e8f0; line-height:1.4;">
          &bull; <b>ASN Matching:</b> Qty Fisik vs PO ERP. Jika Qty &gt; PO &rarr; Trigger <b>Over-receipt Alert</b>.<br>
          &bull; <b>Directed Putaway:</b> Algoritma alokasi rak berdasarkan SKU, HazMat &amp; Suhu Ruang.<br>
          &bull; <b>Aisle-Rack-Shelf-Bin:</b> Traceability 100%.<br>
          &bull; <b>Rotasi Stok:</b> Kunci FEFO (First Expired First Out) / FIFO saat Pick Task terbit.<br>
          &bull; <b>Cycle Counting:</b> Blind counting tanpa stop gudang.
        </div>
      </div>

      <!-- REEFER COLD CHAIN -->
      <div class="card" style="background:#17253b; border-color:#06b6d4;">
        <div style="color:#22d3ee; font-weight:700; margin-bottom:6px; font-size:12px;">❄️ Aturan Cold Chain Reefer:</div>
        <div style="font-size:11.5px; color:#e2e8f0; line-height:1.4;">
          &bull; <b>Max Exposure Time:</b> Waktu cabut plug maks 30 menit.<br>
          &bull; <b>Monitoring Suhu:</b> Chilled (2&deg;C s/d 8&deg;C) atau Frozen (-18&deg;C). Telemetri IoT per 15 menit.<br>
          &bull; <b>Breach Alarm:</b> Jika suhu &gt; ambang toleransi, alert sirine &amp; SMS darurat ke Teknisi Genset.
        </div>
      </div>

      <!-- CONVEYOR & MASS SCANNER -->
      <div class="equipment-box">
        <div class="eq-header">
          <span>📦 WCS Mass Scanner &amp; Diverter</span>
        </div>
        <div class="eq-desc">
          Perekaman dimensi, berat, GS1 scan &lt; 500ms. Jika <b>No-Read Barcode</b> &rarr; Diverter belokkan paket ke Reject Lane.
        </div>
      </div>
    </div>

    <!-- STAGE 4: INTERMODAL SIDING & CUSTOMS CEISA -->
    <div class="stage-col">
      <div class="stage-header">
        <div class="stage-num">4</div>
        <div class="stage-title">Intermodal &amp; Customs</div>
        <div class="stage-sys">TMS + ERP + Customs</div>
      </div>

      <!-- ROLE -->
      <div class="card card-role">
        <div class="title">👤 Role Manusia &amp; 🛠️ Alat</div>
        <div class="content">
          <b>Role:</b> Intermodal Rail Planner, Crane Operator Rel Siding, Petugas Hanggar Bea Cukai, PPJK.<br>
          <b>Alat:</b> Rel Kereta Api Siding 600m (KAI Logistik), RMG / Reach Stacker Intermodal, Mobile X-Ray Scanner.
        </div>
      </div>

      <!-- INTERMODAL RULES -->
      <div class="card" style="background:#1e2238; border-color:#6366f1;">
        <div style="color:#818cf8; font-weight:700; margin-bottom:6px; font-size:12px;">🚆 Business Rule Kereta Api Intermodal:</div>
        <div style="font-size:11.5px; color:#e2e8f0; line-height:1.4;">
          &bull; <b>Transshipment Sync:</b> Sinkronisasi jadwal kedatangan 30 gerbong datar (flatcar) KA.<br>
          &bull; <b>Load Planning:</b> Penyeimbangan berat gandar gerbong (maks 15 ton/gandar).<br>
          &bull; <b>Reach Stacker Dispatch:</b> Pemindahan kontainer dari rangkaian KA langsung ke Yard Chassis dalam &lt; 4 menit/boks.
        </div>
      </div>

      <!-- CUSTOMS RULES -->
      <div class="card-decision" style="border-left-color:#eab308; border-color:#854d0e;">
        <div class="title" style="color:#facc15;">🛃 Validasi Kepabeanan CEISA 4.0:</div>
        <div class="condition">
          Pengecekan Respon Bea Cukai Real-Time API:
        </div>
        <div class="decision-branch">
          <div class="branch-pass">
            <div class="label">🟢 JALUR HIJAU (SPPB Terbit):</div>
            <div class="desc">Kunci pabean terbuka (<code>CUSTOMS_CLEARED</code>). Kontainer siap billing &amp; gate-out.</div>
          </div>
          <div class="branch-fail" style="border-color:#ca8a04; background:rgba(234,179,8,0.1);">
            <div class="label" style="color:#fde047;">🔴 JALUR MERAH (Behandle Fisik):</div>
            <div class="desc">Status <code>CUSTOMS_LOCKED</code>. Kontainer wajib dibawa ke hanggar periksa fisik. Dilarang keluar!</div>
          </div>
        </div>
      </div>

      <div class="equipment-box">
        <div class="eq-header">
          <span>🚚 TMS Dynamic Route &amp; e-POD</span>
        </div>
        <div class="eq-desc">
          Optimalisasi rute truk pengantaran, GPS Geofencing alert saat keluar rute resmi, serta penandatanganan e-POD digital.
        </div>
      </div>
    </div>

    <!-- STAGE 5: BILLING & GATE-OUT -->
    <div class="stage-col highlight">
      <div class="stage-header">
        <div class="stage-num">5</div>
        <div class="stage-title">Billing &amp; Gate-Out</div>
        <div class="stage-sys">ERP + YMS</div>
      </div>

      <!-- ROLE -->
      <div class="card card-role">
        <div class="title">👤 Role Manusia &amp; 🛠️ Alat</div>
        <div class="content">
          <b>Role:</b> Kasir Terminal Billing, Verifikator Gate-Out, Driver Truk Pengambil.<br>
          <b>Alat:</b> Kiosk Self-Service Gate-Out, QR Scanner, Palang Barrier Otomatis, Printer Struk e-Gate Pass.
        </div>
      </div>

      <!-- ERP BILLING RULES -->
      <div class="card" style="background:#162438; border-color:#0284c7;">
        <div style="color:#38bdf8; font-weight:700; margin-bottom:6px; font-size:12px;">💰 Business Rule Terminal Billing (ERP):</div>
        <div style="font-size:11.5px; color:#e2e8f0; line-height:1.4;">
          &bull; <b>Lift-On / Lift-Off (THC):</b> Tarif bongkar muat alat.<br>
          &bull; <b>Storage Fee:</b> Free Time 3 hari.<br>
          &bull; <b>Demurrage Tariff:</b> Hari 4-7 = Rp 250rb/hari, Hari &gt;7 = Rp 500rb/hari (Tarif progresif otomatis).<br>
          &bull; <b>Reefer Monitoring Fee:</b> Jam pemakaian listrik 380V.
        </div>
      </div>

      <!-- GATE-OUT DECISION -->
      <div class="card-decision">
        <div class="title">🚪 Validasi Gate-Out (Zero Leakage):</div>
        <div class="condition">
          <b>Pemeriksaan Sistem Otomatis:</b><br>
          1. Apakah SPPB Bea Cukai Valid?<br>
          2. Apakah Invoice Lunas (Status = PAID)?<br>
          3. Apakah Nomor Boks &amp; Truk Cocok (OCR/ANPR)?
        </div>

        <div class="decision-branch">
          <!-- JIKA LUNAS -->
          <div class="branch-pass">
            <div class="label">✅ JIKA LUNAS &amp; VALID (SPPB Approved):</div>
            <div class="desc">
              &bull; Palang Barrier Gate-Out Otomatis Terbuka.<br>
              &bull; Status Database: <code>TERMINAL_EXITED</code>.<br>
              &bull; e-Receipt &amp; e-POD terkirim ke email Shipper.<br>
              &bull; Slot di Yard Block-Bay-Row-Tier menjadi <b>KOSONG</b> (Tersedia kembali).
            </div>
          </div>

          <!-- JIKA BELUM LUNAS -->
          <div class="branch-fail">
            <div class="label">❌ JIKA BELUM LUNAS / CUSTOMS LOCKED:</div>
            <div class="desc">
              &bull; Palang Barrier <b>TERKUNCI RAPAT</b>.<br>
              &bull; Layar Kiosk menampilkan pesan: "UNPAID INVOICE / CUSTOMS HOLD".<br>
              &bull; Truk diarahkan ke Area Parkir Loket Billing. Mencegah kebocoran pendapatan terminal (Zero Revenue Leakage).
            </div>
          </div>
        </div>
      </div>

      <div class="equipment-box">
        <div class="eq-header">
          <span>🏁 Terminal Completion</span>
        </div>
        <div class="eq-desc">
          Siklus end-to-end tuntas. Data terarsip otomatis di database MySQL CIDP dan tersinkronisasi ke Odoo ERP.
        </div>
      </div>
    </div>

  </div>

  <!-- FOOTER -->
  <div class="footer-note" style="margin-top: 15px;">
    <div><b>Conclusion Supply Chain Consultant</b> &bull; Tim BA &amp; QA: Naufal Andika H. &amp; Zulfikar J. Fatah &bull; Hardware: Armansyah M. &bull; Software: Afriansyah A. &bull; Data: Juan G.</div>
    <div><b>Evaluator:</b> Dr. Tigor Franky, S.T., M.T. &bull; Institut Transportasi dan Logistik (ITL) Trisakti &bull; Live Web: <code>http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php</code></div>
  </div>

</body>
</html>
"""

# Diagram 2: Gate & Weighbridge Detail
html_content_gate_detail = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Peta Keputusan Timbangan & Gate Masuk</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    background: #090d16;
    color: #f1f5f9;
    padding: 35px;
    width: 2000px;
    height: 1200px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 18px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
  .grid-2 {
    display: grid;
    grid-template-columns: 850px 1050px;
    gap: 30px;
    height: 1000px;
  }
  .panel {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .panel-title {
    font-size: 20px;
    font-weight: 800;
    color: #38bdf8;
    border-bottom: 1px solid #334155;
    padding-bottom: 10px;
  }
  .card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 16px;
    font-size: 14px;
    line-height: 1.5;
  }
  .branch-box {
    border-radius: 8px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .pass-box { background: rgba(16, 185, 129, 0.1); border: 2px solid #10b981; }
  .fail-box { background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; }
  .dest-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 10px;
  }
  .dest-card {
    background: #1e293b;
    border: 1px solid #0284c7;
    border-radius: 6px;
    padding: 10px;
    font-size: 12.5px;
  }
  .dest-card b { color: #38bdf8; display: block; margin-bottom: 3px; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>ALUR &amp; ATURAN KEPUTUSAN TIMBANGAN DINAMIS GATE-IN (WCS + YMS)</h1>
      <p>MENJAWAB PERTANYAAN NOTULENSI: "TRUK MASUK TIMBANGAN, JIKA LULUS KE MANA, JIKA TIDAK KE MANA?"</p>
    </div>
    <div style="background:#1e293b; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Implementasi: pages/gate.php
    </div>
  </div>

  <div class="grid-2">
    <!-- PANEL KIRI: PARAMETER & VALIDASI -->
    <div class="panel">
      <div class="panel-title">1. Parameter Masukan &amp; Logika Validasi BA</div>

      <div class="card">
        <b style="color:#f59e0b; font-size:15px;">👥 Business Role &amp; Peralatan:</b><br>
        &bull; <b>Driver Truk:</b> Mengemudikan kendaraan ke platform timbangan &amp; tap e-Ticket RFID.<br>
        &bull; <b>Gate Clerk:</b> Memonitor layar antarmuka WCS/YMS Gate Kiosk.<br>
        &bull; <b>Weighbridge Load Cell (Avery 80T):</b> Menangkap berat aktual dalam &lt; 3 detik.<br>
        &bull; <b>Kamera ANPR &amp; OCR:</b> Membaca plat nomor polisi dan kode kontainer ISO 6346.
      </div>

      <div class="card">
        <b style="color:#38bdf8; font-size:15px;">📥 Parameter Input:</b><br>
        1. <code>Gross_Weight</code>: Berat total truk + sasis + muatan dari sensor timbangan.<br>
        2. <code>Tare_Weight</code>: Berat kosong truk &amp; sasis dari Master TMS ERP.<br>
        3. <code>Net_Weight</code>: <code>Gross_Weight - Tare_Weight</code>.<br>
        4. <code>VGM_SOLAS</code>: Berat muatan terverifikasi dari dokumen pengirim (Shipper).<br>
        5. <code>ISO_Container_Code</code>: Format 4 Huruf Kapital + 7 Angka.
      </div>

      <div class="card" style="border-left: 5px solid #f59e0b;">
        <b style="color:#fbbf24; font-size:15px;">⚖️ 3 Kriteria Business Rule Validasi:</b><br>
        <b>Aturan 1 (Batas Hukum Muatan):</b> Berat Kotor &le; 34.000 kg (Regulasi UU No. 22/2009 Jalan Kelas 1).<br>
        <b>Aturan 2 (Toleransi Berat):</b> Selisih <code>|Net_Weight - VGM_SOLAS| / VGM_SOLAS &le; 2.0%</code>.<br>
        <b>Aturan 3 (Integritas ISO):</b> Angka ke-7 cocok dengan perhitungan Modulo 11 ISO 6346.
      </div>
    </div>

    <!-- PANEL KANAN: ALUR KEPUTUSAN JIKA LULUS / JIKA TIDAK -->
    <div class="panel">
      <div class="panel-title">2. Alur Keputusan: Jika Lulus ke Mana, Jika Tidak ke Mana?</div>

      <!-- JIKA LULUS -->
      <div class="branch-box pass-box">
        <h3 style="color:#34d399; font-size:17px; display:flex; align-items:center; gap:8px;">
          ✅ JIKA LULUS (Semua 3 Aturan Terpenuhi)
        </h3>
        <p style="font-size:13.5px; color:#e2e8f0;">
          Status sistem diperbarui menjadi <code>GATE_IN_APPROVED</code>. Palang gerbang otomatis naik. Kiosk mencetak <b>Container Movement Slip (CMS)</b> yang dilengkapi rute GPS &amp; koordinat slot penumpukan.
        </p>
        <b style="color:#38bdf8; font-size:13px; margin-top:4px;">KEMANA TRUK DIARAHKAN? (Sesuai Jenis Barangnya):</b>
        <div class="dest-grid">
          <div class="dest-card">
            <b>1. Kontainer FCL Kering (Dry Cargo)</b>
            Diarahkan ke <b>Container Yard Blok A/B/C/D</b> sesuai koordinat Bay-Row-Tier untuk diangkat RTG Crane 45T.
          </div>
          <div class="dest-card">
            <b>2. Kontainer Dingin (Reefer Cargo)</b>
            Diarahkan ke <b>Blok E (Reefer Yard)</b> untuk segera dicolokkan ke listrik 380V (Maksimal Exposure 30 Menit).
          </div>
          <div class="dest-card">
            <b>3. Kargo Curah / Palet LCL</b>
            Diarahkan ke <b>Gudang CFS Pintu Dock D1-D5</b> untuk pembongkaran muatan (Stripping) oleh Forklift 3-5T.
          </div>
          <div class="dest-card">
            <b>4. Jalur Merah Bea Cukai / HazMat</b>
            Jalur Merah ke <b>Hanggar Behandle Karantina</b>; HazMat ke <b>Blok F (Bunded Isolation Area)</b>.
          </div>
        </div>
      </div>

      <!-- JIKA TIDAK LULUS -->
      <div class="branch-box fail-box">
        <h3 style="color:#f87171; font-size:17px; display:flex; align-items:center; gap:8px;">
          ❌ JIKA TIDAK LULUS (Overweight / Selisih &gt; 2% / ISO Invalid)
        </h3>
        <p style="font-size:13.5px; color:#e2e8f0;">
          Status sistem diperbarui menjadi <code>OVERLOAD_REJECTED</code> atau <code>DISCREPANCY_HOLD</code>. Palang gerbang <b>TETAP TERKUNCI RAPAT</b>. Lampu merah menyala dan sirene alert berbunyi di pos satpam.
        </p>
        <b style="color:#f87171; font-size:13px; margin-top:4px;">KEMANA TRUK DIARAHKAN? (PENANGANAN ANOMALI):</b>
        <div style="background:#1e293b; border:1px solid #ef4444; border-radius:6px; padding:12px; font-size:13px; line-height:1.5;">
          &bull; <b>Langkah 1:</b> Truk <b>DILARANG KERAS</b> masuk ke lapangan penumpukan (mencegah jalan beton K-450 ambles dan menghindari sanksi IMO).<br>
          &bull; <b>Langkah 2:</b> Truk diarahkan belok kiri ke <b>HOLDING BAY / REJECT LANE (Area Karantina Timbangan)</b>.<br>
          &bull; <b>Langkah 3:</b> Supervisor Gate melakukan audit dokumen bersama supir. Jika kesalahan ketik &rarr; Rekoreksi dokumen via Approval Supervisor.<br>
          &bull; <b>Langkah 4:</b> Jika muatan kelebihan tonase murni &rarr; Truk diarahkan putar balik keluar terminal melalui <b>U-Turn Bypass Exit</b> atau diarahkan bongkar sebagian di CFS stripping darurat.
        </div>
      </div>

    </div>
  </div>
</body>
</html>
"""

# Diagram 3: Pengangkatan & Penempatan Detail
html_content_lifting_detail = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Aturan Pengangkatan & Penempatan Kontainer 40ft/20ft</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    background: #090d16;
    color: #f1f5f9;
    padding: 35px;
    width: 2000px;
    height: 1200px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 18px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    height: 1000px;
  }
  .panel {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .panel-title {
    font-size: 20px;
    font-weight: 800;
    color: #38bdf8;
    border-bottom: 1px solid #334155;
    padding-bottom: 10px;
  }
  .card-eq {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .card-eq.highlight { border-color: #0284c7; background: #132238; }
  .badge-role {
    font-size: 12px;
    font-weight: 700;
    color: #f59e0b;
    text-transform: uppercase;
  }
  .eq-name {
    font-size: 17px;
    font-weight: 800;
    color: #fff;
  }
  .eq-desc {
    font-size: 13px;
    color: #cbd5e1;
    line-height: 1.45;
  }
  .rule-box {
    background: #141f36;
    border: 1px solid #2563eb;
    border-radius: 8px;
    padding: 14px;
    font-size: 13px;
    line-height: 1.5;
  }
  .rule-box b { color: #38bdf8; }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>MATRIKS PENGANGKATAN &amp; PENEMPATAN KONTAINER (YMS + WCS)</h1>
      <p>MENJAWAB PERTANYAAN NOTULENSI: "SIAPA YANG NGANGKAT, KONTAINER 40 FEET SIAPA YANG ANGKAT, PENEMPATANNYA SEPERTI APA?"</p>
    </div>
    <div style="background:#1e293b; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Implementasi: pages/alat.php &amp; pages/yard.php
    </div>
  </div>

  <div class="grid-2">
    <!-- PANEL KIRI: SIAPA YANG NGANGKAT? -->
    <div class="panel">
      <div class="panel-title">1. Siapa yang Mengangkat? (Alat &amp; Operator)</div>

      <div class="card-eq highlight">
        <span class="badge-role">🚜 1. Kontainer 40 Feet (Laden / Muatan Berat)</span>
        <div class="eq-name">RTG Crane 45 Ton / Reach Stacker 45 Ton</div>
        <div class="eq-desc">
          &bull; <b>Alat Utama:</b> <b>RTG Crane (Rubber Tyred Gantry)</b> kapasitas 45 Ton melayani rentang 6+1 baris dan 5+1 tinggi tier. Dilengkapi telescopic spreader otomatis 20ft &amp; 40ft.<br>
          &bull; <b>Alat Pendukung:</b> <b>Reach Stacker 45 Ton</b> (Kalmar / Konecranes) untuk fleksibilitas di yard block dan rail siding.<br>
          &bull; <b>Role Manusia:</b> Operator RTG/RS berlisensi SIO Kelas 1 Kemnaker. Menerima instruksi kerja (Work Instruction) real-time dari YMS melalui layar VMT (Vehicle Mounted Terminal) di kabin alat.
        </div>
      </div>

      <div class="card-eq">
        <span class="badge-role">🏗️ 2. Kontainer 20 Feet Kosong (Empty Container)</span>
        <div class="eq-name">Empty Container Handler (ECH) 10 Ton</div>
        <div class="eq-desc">
          &bull; <b>Alat:</b> <b>Empty Container Handler (ECH)</b> khusus peti kemas kosong dengan side-spreader.<br>
          &bull; <b>Kecepatan:</b> Dapat menumpuk hingga 6 s/d 8 tier dengan kecepatan angkat 0,6 m/s.<br>
          &bull; <b>Role Manusia:</b> Operator ECH berlisensi SIO.
        </div>
      </div>

      <div class="card-eq">
        <span class="badge-role">🚆 3. Kontainer Intermodal di Rel Kereta Api 600M</span>
        <div class="eq-name">Rail Mounted Gantry (RMG) 45 Ton / RS Intermodal</div>
        <div class="eq-desc">
          &bull; <b>Alat:</b> <b>RMG Crane 45 Ton</b> atau Reach Stacker dengan intermodal leg attachments.<br>
          &bull; <b>Fungsi:</b> Memindahkan kontainer 40ft/20ft dari gerbong datar (flatcar) KA Logistik ke yard chassis dalam tempo &lt; 4 menit/kontainer.<br>
          &bull; <b>Role Manusia:</b> Operator RMG &amp; Ground Marshall Rel Siding.
        </div>
      </div>

      <div class="card-eq">
        <span class="badge-role">🏬 4. Kargo Palet / Curah di Gudang CFS</span>
        <div class="eq-name">Forklift 3-5 Ton (Diesel / Electric) &amp; Reach Truck</div>
        <div class="eq-desc">
          &bull; <b>Alat:</b> Forklift 3-5 Ton untuk bongkar isi boks (stripping) di rampa dock leveler, serta Reach Truck untuk menaikkan palet ke rak selektif (Aisle-Rack-Shelf-Bin).<br>
          &bull; <b>Role Manusia:</b> Operator Forklift CFS &amp; Tallyman Gudang.
        </div>
      </div>
    </div>

    <!-- PANEL KANAN: PENEMPATANNYA SEPERTI APA? -->
    <div class="panel">
      <div class="panel-title">2. Penempatannya Seperti Apa? (Business Rules Stacking)</div>

      <div class="rule-box">
        <b>📐 A. Struktur Hierarki Koordinat 4D:</b><br>
        Setiap peti kemas di CIDP memiliki alamat mutlak: <b>[Blok] - [Bay] - [Row] - [Tier]</b>.<br>
        <i>Contoh: <code>YD-A-04-03-02</code> &rarr; Yard Blok A, Bay 04 (Kontainer 40ft), Row 03, Tier 02.</i>
      </div>

      <div class="rule-box">
        <b>📏 B. Aturan Bay (20 Feet vs 40 Feet):</b><br>
        &bull; <b>Bay Ganjil (01, 03, 05, 07):</b> Disediakan khusus untuk 2 unit kontainer 20 Feet berdampingan.<br>
        &bull; <b>Bay Genap (02, 06, 10, 14):</b> Disediakan khusus untuk 1 unit kontainer 40 Feet yang menempati 2 slot 20ft.<br>
        &bull; <b>Aturan Anti-Tabrakan:</b> Sistem YMS secara mutlak melarang menumpuk kontainer 40ft di atas dua kontainer 20ft jika tinggi atau posisinya tidak seimbang.
      </div>

      <div class="rule-box">
        <b>⚖️ C. Aturan Berat (Weight Stacking Hierarchy):</b><br>
        &bull; <b>Tier 1 &amp; Tier 2 (Lantai Bawah):</b> Kontainer Berat (&gt; 24 Ton) wajib diletakkan di tier paling bawah untuk menjaga kestabilan gravitasi (Center of Gravity) dan mencegah amblasnya landasan beton.<br>
        &bull; <b>Tier 3 (Tengah):</b> Kontainer Bobot Sedang (15 s/d 24 Ton).<br>
        &bull; <b>Tier 4 &amp; Tier 5 (Lantai Atas):</b> Kontainer Ringan (&lt; 15 Ton) atau Kontainer Kosong (Empty).
      </div>

      <div class="rule-box">
        <b>🗺️ D. Aturan Zonasi Berdasarkan Karakteristik Kargo:</b><br>
        &bull; <b>Reefer (Dingin):</b> Hanya dialokasikan pada Blok E yang memiliki tiang colokan 380V. Maksimal di Tier 3 agar teknisi mudah memantau kabel.<br>
        &bull; <b>HazMat / DG (B3):</b> Hanya di Blok F yang dilengkapi parit penampung tumpahan kimia, berjarak minimal 50 meter dari gedung CFS.<br>
        &bull; <b>Ekspor vs Impor:</b> Kontainer ekspor diletakkan dekat Rel Siding KA untuk kecepatan muat, kontainer impor dekat Gate Out.
      </div>
    </div>
  </div>
</body>
</html>
"""

# Diagram 4: Peta Arsitektur & Dimana Ditaruhnya di Aplikasi
html_content_app_concept = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Peta Arsitektur & Dimana Ditaruhnya di Aplikasi CIDP</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    background: #090d16;
    color: #f1f5f9;
    padding: 35px;
    width: 2000px;
    height: 1200px;
  }
  .header {
    border-bottom: 2px solid #1e293b;
    padding-bottom: 18px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 30px; font-weight: 800; color: #fff; }
  .header p { font-size: 15px; color: #38bdf8; font-weight: 600; margin-top: 4px; }
  .grid-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
    height: 1000px;
  }
  .col-sys {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  .col-sys.active { border-color: #0284c7; background: #0c1a2f; }
  .sys-title {
    font-size: 18px;
    font-weight: 800;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .sys-title span { font-size: 12px; color: #38bdf8; }
  .app-file {
    background: #111827;
    border: 1px solid #374151;
    border-radius: 6px;
    padding: 8px 10px;
    font-family: monospace;
    font-size: 12px;
    color: #38bdf8;
    font-weight: 700;
  }
  .box-content {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 12px;
    font-size: 12.5px;
    line-height: 1.45;
    color: #cbd5e1;
  }
  .box-content b { color: #f1f5f9; display: block; margin-bottom: 4px; }
  .badge-rule {
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
  }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>PEMETAAN KONSEP &amp; LOKASI MODUL PADA APLIKASI WEB CIDP</h1>
      <p>MENJAWAB PERTANYAAN: "DI MANA NANTI DITARUHNYA DAN KONSEP SEPERTI APA KARENA APLIKASI SUDAH TERLALU KOMPLEKS?"</p>
    </div>
    <div style="background:#1e293b; border:1px solid #38bdf8; padding:8px 16px; border-radius:8px; color:#38bdf8; font-weight:700;">
      Arsitektur Terpadu 5 Modul Logistik
    </div>
  </div>

  <div class="grid-5">

    <!-- 1. WCS -->
    <div class="col-sys active">
      <div class="sys-title">
        WCS / WES
        <span>Warehouse &amp; Terminal Control</span>
      </div>
      <div class="app-file">📁 pages/gate.php<br>📁 pages/scanner.php</div>
      <div class="box-content">
        <b>🎯 Konsep &amp; Fungsi:</b>
        Mengendalikan otomasi perangkat keras di lapangan dalam hitungan milidetik (&lt; 500 ms).
      </div>
      <div class="box-content">
        <b>⚖️ Aturan Bisnis yang Ditaruh di Sini:</b>
        &bull; <b>Jembatan Timbang:</b> Tangkap gross weight &amp; hitung toleransi &plusmn; 2%.<br>
        &bull; <b>Palang Barrier:</b> Buka jika valid, kunci jika overweight.<br>
        &bull; <b>Kamera ANPR/OCR:</b> Tangkap plat truk &amp; nomor boks ISO 6346.<br>
        &bull; <b>Diverter Conveyor:</b> Belokkan ke Reject Lane jika No-Read barcode.
      </div>
    </div>

    <!-- 2. YMS -->
    <div class="col-sys">
      <div class="sys-title">
        YMS
        <span>Yard Management System</span>
      </div>
      <div class="app-file">📁 pages/yard.php<br>📁 pages/alat.php<br>📁 pages/denah.php</div>
      <div class="box-content">
        <b>🎯 Konsep &amp; Fungsi:</b>
        Mengelola tata letak fisik lapangan penumpukan 35 Ha, pergerakan armada truk, dan alat berat terminal.
      </div>
      <div class="box-content">
        <b>⚖️ Aturan Bisnis yang Ditaruh di Sini:</b>
        &bull; <b>3D Yard Map:</b> Alamat Block-Bay-Row-Tier.<br>
        &bull; <b>Stacking Rules:</b> Bay genap (40ft), ganjil (20ft), Tier 1-2 berat, Tier 4-5 ringan.<br>
        &bull; <b>Equipment Dispatch:</b> Penugasan RTG 45T, Reach Stacker &amp; ECH.<br>
        &bull; <b>Dwelling Time Alert:</b> Peringatan jika boks menumpuk &gt; 3 hari free time.
      </div>
    </div>

    <!-- 3. WMS -->
    <div class="col-sys">
      <div class="sys-title">
        WMS
        <span>Warehouse Management</span>
      </div>
      <div class="app-file">📁 pages/cfs.php<br>📁 pages/reefer.php<br>📁 master_rak.php</div>
      <div class="box-content">
        <b>🎯 Konsep &amp; Fungsi:</b>
        Mengelola barang kargo curah di gudang CFS (stripping/stuffing) dan pengawasan cold storage.
      </div>
      <div class="box-content">
        <b>⚖️ Aturan Bisnis yang Ditaruh di Sini:</b>
        &bull; <b>ASN Matching:</b> Qty fisik vs PO. Trigger Over-receipt alert jika Qty &gt; PO.<br>
        &bull; <b>Directed Putaway:</b> Penentuan rak Aisle-Rack-Shelf-Bin.<br>
        &bull; <b>FEFO / FIFO:</b> Kunci barang masa kedaluwarsa terdekat saat picking.<br>
        &bull; <b>Reefer Monitoring:</b> Pemantauan suhu chilled &amp; frozen real-time.
      </div>
    </div>

    <!-- 4. TMS -->
    <div class="col-sys">
      <div class="sys-title">
        TMS
        <span>Transportation Management</span>
      </div>
      <div class="app-file">📁 pages/trucking.php<br>📁 pages/intermodal.php</div>
      <div class="box-content">
        <b>🎯 Konsep &amp; Fungsi:</b>
        Mengatur pergerakan truk ekspedisi di luar terminal dan konektivitas antarmoda rel siding kereta api.
      </div>
      <div class="box-content">
        <b>⚖️ Aturan Bisnis yang Ditaruh di Sini:</b>
        &bull; <b>Dynamic Routing:</b> Pencarian rute efisien batasan kemacetan &amp; tonase.<br>
        &bull; <b>GPS Geofencing:</b> Alert otomatis saat supir keluar jalur resmi.<br>
        &bull; <b>e-POD:</b> Foto bukti serah terima &amp; TTD digital.<br>
        &bull; <b>Intermodal Sync:</b> Jadwal transfer peti kemas dari gerbong datar KA ke chassis.
      </div>
    </div>

    <!-- 5. ERP -->
    <div class="col-sys">
      <div class="sys-title">
        ERP
        <span>Enterprise Resource Planning</span>
      </div>
      <div class="app-file">📁 pages/billing.php<br>📁 pages/customs.php<br>📁 pages/settings.php</div>
      <div class="box-content">
        <b>🎯 Konsep &amp; Fungsi:</b>
        Mengelola master data pelanggan, kepatuhan pabean Bea Cukai, serta akuntansi dan penagihan terminal.
      </div>
      <div class="box-content">
        <b>⚖️ Aturan Bisnis yang Ditaruh di Sini:</b>
        &bull; <b>Terminal Tariff Engine:</b> Hitung otomatis Lift-on/Lift-off THC, storage, &amp; plugging fee.<br>
        &bull; <b>Tarif Progresif Demurrage:</b> Denda overstay harian otomatis.<br>
        &bull; <b>CEISA 4.0 Customs:</b> Validasi SPPB Bea Cukai (Jalur Hijau vs Merah).<br>
        &bull; <b>Zero Leakage Gate-Out:</b> Palang keluar hanya buka jika status invoice PAID.
      </div>
    </div>

  </div>
</body>
</html>
"""

def generate_jpgs():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        
        # 1. End to End
        page1 = browser.new_page(viewport={"width": 2400, "height": 1400}, device_scale_factor=1.5)
        page1.set_content(html_content_end_to_end)
        p1 = os.path.join(output_dir, "ALUR_END_TO_END_PROSES_BISNIS_CIDP.jpg")
        page1.screenshot(path=p1, type="jpeg", quality=95)
        print("Generated:", p1)
        page1.close()

        # 2. Gate & Weighbridge Detail
        page2 = browser.new_page(viewport={"width": 2000, "height": 1200}, device_scale_factor=1.5)
        page2.set_content(html_content_gate_detail)
        p2 = os.path.join(output_dir, "ALUR_GATE_DAN_TIMBANGAN_DECISION_RULES.jpg")
        page2.screenshot(path=p2, type="jpeg", quality=95)
        print("Generated:", p2)
        page2.close()

        # 3. Lifting & Stacking Detail
        page3 = browser.new_page(viewport={"width": 2000, "height": 1200}, device_scale_factor=1.5)
        page3.set_content(html_content_lifting_detail)
        p3 = os.path.join(output_dir, "ALUR_PENGANGKATAN_DAN_PENEMPATAN_KONTAINER.jpg")
        page3.screenshot(path=p3, type="jpeg", quality=95)
        print("Generated:", p3)
        page3.close()

        # 4. App Architecture Mapping
        page4 = browser.new_page(viewport={"width": 2000, "height": 1200}, device_scale_factor=1.5)
        page4.set_content(html_content_app_concept)
        p4 = os.path.join(output_dir, "PETA_ARSITEKTUR_DAN_KONSEP_APLIKASI_CIDP.jpg")
        page4.screenshot(path=p4, type="jpeg", quality=95)
        print("Generated:", p4)
        page4.close()

        browser.close()

if __name__ == "__main__":
    generate_jpgs()
