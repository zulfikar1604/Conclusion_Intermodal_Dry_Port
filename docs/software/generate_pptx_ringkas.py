import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_concise_presentation():
    prs = Presentation()
    # 16:9 Widescreen (13.333 x 7.5 Inches)
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    TOTAL_SLIDES = 8

    # Professional Color Palette
    C_NAVY = RGBColor(0, 47, 94)        # #002f5e - Navy ITL / Port Authority
    C_BLUE = RGBColor(1, 112, 185)      # #0170b9 - Primary Blue
    C_LIGHT_BG = RGBColor(248, 250, 252)# #f8fafc - Slate 50
    C_CARD_BG = RGBColor(255, 255, 255) # #ffffff - Card White
    C_BORDER = RGBColor(226, 232, 240)  # #e2e8f0 - Border Gray
    C_DARK = RGBColor(15, 23, 42)       # #0f172a - Slate 900
    C_TEXT = RGBColor(30, 41, 59)       # #1e293b - Slate 800
    C_MUTED = RGBColor(100, 116, 139)   # #64748b - Slate 500
    C_EMERALD = RGBColor(16, 185, 129)  # #10b981 - Success Green
    C_AMBER = RGBColor(245, 158, 11)    # #f59e0b - Accent Amber
    C_INDIGO = RGBColor(99, 102, 241)   # #6366f1 - Tech Indigo
    C_PURPLE = RGBColor(147, 51, 234)   # #9333ea - Purple AI
    C_WHITE = RGBColor(255, 255, 255)
    C_CODE_BG = RGBColor(15, 23, 42)    # #0f172a - Code Block BG
    C_GREEN_BG = RGBColor(236, 253, 245)# #ecfdf5
    C_BLUE_BG = RGBColor(239, 246, 255) # #eff6ff
    C_DEMO_TAG_BG = RGBColor(254, 243, 199) # Amber 100 for Live Demo Tag
    C_DEMO_TAG_TXT = RGBColor(180, 83, 9)   # Amber 700

    def add_header(slide, title_text, category_text="TOPIK 7: INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT"):
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.35), Inches(11.7), Inches(1.1))
        tf = header_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        
        p0 = tf.paragraphs[0]
        p0.text = category_text.upper()
        p0.font.size = Pt(9.5)
        p0.font.bold = True
        p0.font.color.rgb = C_BLUE
        
        p1 = tf.add_paragraph()
        p1.text = title_text
        p1.font.size = Pt(19)
        p1.font.bold = True
        p1.font.color.rgb = C_NAVY
        p1.space_before = Pt(3)

    def add_footer(slide, current_slide):
        footer_box = slide.shapes.add_textbox(Inches(0.8), Inches(7.05), Inches(11.7), Inches(0.35))
        tf = footer_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = f"Conclusion Supply Chain Consultant — CIDP 35 Ha | Presentasi & Skenario Live Demo Web                    Slide {current_slide} of {TOTAL_SLIDES}"
        p.font.size = Pt(8.5)
        p.font.color.rgb = C_MUTED

    def create_card(slide, left, top, width, height, bg_color=C_CARD_BG, border_color=C_BORDER):
        shape = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        shape.fill.solid()
        shape.fill.fore_color.rgb = bg_color
        if border_color:
            shape.line.color.rgb = border_color
            shape.line.width = Pt(1)
        else:
            shape.line.fill.background()
        return shape

    def add_demo_banner(slide, left, top, width, height, text_module, text_action):
        shape = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        shape.fill.solid()
        shape.fill.fore_color.rgb = C_DEMO_TAG_BG
        shape.line.color.rgb = C_AMBER
        shape.line.width = Pt(1)

        tb = slide.shapes.add_textbox(left + Inches(0.15), top + Inches(0.08), width - Inches(0.3), height - Inches(0.15))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = f"💻 LIVE DEMO WEB: {text_module.upper()}"
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = C_DEMO_TAG_TXT
        p = tf.add_paragraph()
        p.text = text_action
        p.font.size = Pt(8.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(1)

    # =========================================================================
    # SLIDE 1: COVER
    # =========================================================================
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, prs.slide_height)
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = C_NAVY
    bg1.line.fill.background()

    # Left accent bar
    bar = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(0.5), Inches(0.12), Inches(6.4))
    bar.fill.solid()
    bar.fill.fore_color.rgb = C_BLUE
    bar.line.fill.background()

    # Title Box
    tbox1 = s1.shapes.add_textbox(Inches(1.1), Inches(0.45), Inches(11.4), Inches(2.2))
    tf1 = tbox1.text_frame
    tf1.word_wrap = True
    tf1.margin_left = tf1.margin_top = tf1.margin_right = tf1.margin_bottom = 0
    
    p = tf1.paragraphs[0]
    p.text = "TOPIK 7 — INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = RGBColor(147, 197, 253)

    p = tf1.add_paragraph()
    p.text = "Sistem Manajemen Lapangan Penumpukan (Yard Management System / YMS) pada Conclusion Intermodal Dry Port (CIDP) 35 Ha"
    p.font.size = Pt(21)
    p.font.bold = True
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(4)

    p = tf1.add_paragraph()
    p.text = "PROGRES MINGGU INI: ARSITEKTUR SOFTWARE, INGESTION DATA HARDWARE & LIVE DEMO SISTEM"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p.space_before = Pt(6)

    # Team Members Box
    team_card = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.1), Inches(2.8), Inches(11.4), Inches(3.4))
    team_card.fill.solid()
    team_card.fill.fore_color.rgb = RGBColor(15, 30, 60)
    team_card.line.color.rgb = RGBColor(30, 58, 110)
    team_card.line.width = Pt(1)

    tbox_team = s1.shapes.add_textbox(Inches(1.3), Inches(2.95), Inches(11.0), Inches(3.1))
    tft = tbox_team.text_frame
    tft.word_wrap = True
    tft.margin_left = tft.margin_top = tft.margin_right = tft.margin_bottom = 0

    p = tft.paragraphs[0]
    p.text = "TIM PENGEMBANG & KONSULTAN (CONCLUSION SUPPLY CHAIN CONSULTANT):"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = RGBColor(147, 197, 253)

    members = [
        ("1. Zulfikar Jafarudin Fatah", "24D507001001", "Lead System Architect (Ketua Tim)", "Arsitektur Sistem Web YMS, Database MySQL, Dashboard Eksekutif, & 3D Three.js Engine"),
        ("2. Armansyah Muchtarrom", "24D507001010", "Hardware & Infrastructure Specialist", "Pemetaan Protokol Sensor Lapangan (RS-232, TCP/IP, LoRaWAN) & Spesifikasi IoT"),
        ("3. Afriansayah Ayubi", "24D507001026", "Software & ERP Process Specialist (Presenter)", "Logika Penanganan Peti Kemas, Terminal Tariff Engine (Lo-Lo, Storage, VGM), & Integrasi Odoo"),
        ("4. Juan Gamaliel", "24D507001016", "Data Integration Specialist (Presenter)", "Arsitektur REST API Gateway, Pemodelan JSON Payload, Stacking Matrix & Collision Detection"),
        ("5. Naufal Andika Heditya", "24D507001012", "Business Analyst & QA Specialist", "Standardisasi AIDC (GS1-128, SSCC-18, ISO 6346), Integrasi CEISA 4.0 Bea Cukai, & QA Testing")
    ]

    for name, nim, role, desc in members:
        p = tft.add_paragraph()
        p.text = f"• {name} ({nim}) — {role}\n   Fokus: {desc}"
        p.font.size = Pt(9.5)
        p.font.color.rgb = RGBColor(241, 245, 249)
        p.space_before = Pt(4)

    # Footer Box
    fbox = s1.shapes.add_textbox(Inches(1.1), Inches(6.35), Inches(11.4), Inches(0.7))
    ftf = fbox.text_frame
    ftf.word_wrap = True
    ftf.margin_left = ftf.margin_top = ftf.margin_right = ftf.margin_bottom = 0
    p = ftf.paragraphs[0]
    p.text = "Mata Kuliah: Teknologi dan Perangkat Lunak Logistik  •  Dosen Pengampu: Dr. Tigor Franky, S.T., M.T."
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = RGBColor(203, 213, 225)
    p = ftf.add_paragraph()
    p.text = "Program Studi S1 Manajemen Logistik  •  Institut Transportasi dan Logistik (ITL) Trisakti  •  September 2026"
    p.font.size = Pt(9.5)
    p.font.color.rgb = RGBColor(148, 163, 184)
    p.space_before = Pt(2)

    # =========================================================================
    # SLIDE 2: TAKSONOMI 4-LAYER & 3 ALIRAN LOGISTIK
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "Taksonomi 4-Layer IT Logistik & Pengelolaan 3 Aliran Utama Terminal")
    add_footer(s2, 2)

    c2_w = Inches(3.7)
    c2_h = Inches(4.0)

    # Card 1: 3 Aliran
    create_card(s2, Inches(0.8), Inches(1.7), c2_w, c2_h)
    tb = s2.shapes.add_textbox(Inches(1.0), Inches(1.85), c2_w - Inches(0.4), c2_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "SINKRONISASI 3 ALIRAN"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Aliran Logistik Terpadu"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "1. Goods Flow (Fisik 35 Ha):\nPergerakan kontainer di gerbang masuk, jembatan timbang 80t, lapangan penumpukan Blok A-E, hingga gerbong kereta api logistik.\n\n2. Information Flow (Data Telemetri):\nData instan sensor (OCR, ANPR, DGPS, Twistlock, Suhu) yang diolah menjadi koordinat Bay-Row-Tier.\n\n3. Financial Flow (Billing & ERP):\nPenerbitan piutang & e-Faktur jasa terminal (Lo-Lo, penumpukan progresif, reefer) ke ERP Odoo."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: 4-Layer Taxonomy
    create_card(s2, Inches(4.8), Inches(1.7), c2_w, c2_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s2.shapes.add_textbox(Inches(5.0), Inches(1.85), c2_w - Inches(0.4), c2_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "TAKSONOMI 4 LAPISAN IT"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Kerangka Kerja Dr. Tigor Franky"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Layer 1: Sensing & Capture\n  26 Item hardware (kamera OCR, timbangan 80t, RFID UHF, twistlock, DGPS RTK).\n\n• Layer 2: Connectivity Protocols\n  Wi-Fi 7, 4G LTE, LoRaWAN, RS-232, TCP/IP.\n\n• Layer 3: Application Systems (Core YMS)\n  Software YMS, 3D WebGL Engine, Stacking Rules, dan Terminal Tariff Engine.\n\n• Layer 4: Integration Protocols\n  REST API, JSON Payload, CEISA 4.0 Bea Cukai, SAKA PT KAI, dan Odoo ERP."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: Peran Software
    create_card(s2, Inches(8.8), Inches(1.7), c2_w, c2_h)
    tb = s2.shapes.add_textbox(Inches(9.0), Inches(1.85), c2_w - Inches(0.4), c2_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "NILAI STRATEGIS SOFTWARE"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Otak Pengolah Data Mentah"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Menjawab Arahan Dosen:\n  Membuktikan bagaimana software menerima data mentah hardware dan mengolahnya menjadi kecerdasan operasional.\n\n• Transformasi Nilai Bisnis:\n  1. Mengubah sinyal bobot ➔ Status SOLAS VGM.\n  2. Mengubah koordinat DGPS ➔ Slot Blok-Bay-Row-Tier.\n  3. Mengubah pelepasan boks ➔ Tagihan Lo-Lo Rp 250rb.\n  4. Mengubah masa inap ➔ Audit dwell time & heatmap."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Live Demo Banner Bottom
    add_demo_banner(s2, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Struktur Dasbor & 4 Kluster Menu",
                    "Buka web browser di dashboard.php. Tunjukkan 4 kluster navigasi sidebar: Eksekutif & Visualisasi, Operasional Utama (Core YMS), Fasilitas Khusus & Pabean, serta Komersial & Sistem.")

    # =========================================================================
    # SLIDE 3: ARSITEKTUR TEKNOLOGI SOFTWARE: DECOUPLED STACK & SANDBOX
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "Arsitektur Teknologi Software: Decoupled Stack & Virtual IoT Sandbox")
    add_footer(s3, 3)

    c3_w = Inches(5.7)
    c3_h = Inches(4.0)

    # Left: Decoupled Architecture
    create_card(s3, Inches(0.8), Inches(1.7), c3_w, c3_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s3.shapes.add_textbox(Inches(1.0), Inches(1.85), c3_w - Inches(0.4), c3_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FILOSOFI DESAIN ARSITEKTUR"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Decoupled Architecture & Virtual Sandbox"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Mengapa Harus Decoupled (Terpisah)?\n  Software YMS tidak pernah mengikatkan diri langsung ke kabel fisik sensor lapangan. Komunikasi selalu dijembatani oleh IoT Edge Gateway berbasis standar REST API & JSON Payload.\n\n• Uji Coba Virtual Sandbox Standar Enterprise:\n  Dalam industri maritim dunia (misal Navis N4), software selalu diuji menggunakan Virtual Simulator Payload. Struktur JSON yang kami uji menggunakan Postman 100% identik dengan sinyal gateway fisik Advantech nantinya.\n\n• Transisi Plug-and-Play ke Lapangan:\n  Saat hardware fisik terpasang di terminal Cikarang, alat tinggal menembak endpoint API yang sama tanpa mengubah 1 baris kode software YMS!"
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Right: Full Tech Stack
    create_card(s3, Inches(6.8), Inches(1.7), c3_w, c3_h)
    tb = s3.shapes.add_textbox(Inches(7.0), Inches(1.85), c3_w - Inches(0.4), c3_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KOMPONEN SOFTWARE & LIBRARY LENGKAP"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Stack Teknologi yang Digunakan di Proyek"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    
    stack_items = [
        "• Backend & DB: PHP 8.2 Enterprise Engine dengan PDO MySQL (Prepared Statements, ACID Transactions, Zero SQL Injection), MySQL 8.0 InnoDB.",
        "• 3D Digital Twin: Three.js r128 (WebGL 60 FPS), OrbitControls 360°, Tween.js untuk animasi kinematik derek, Raycaster 3D Inspector.",
        "• 2D GIS & Denah: Leaflet.js v1.9 untuk denah terminal 35 Ha dan plotting GPS armada.",
        "• Frontend UI/UX: Tailwind CSS 3.4, Vanilla ES6 JS, Google Font Plus Jakarta Sans.",
        "• Analitik & Visual: Chart.js 4.4 untuk tren gate, okupansi yard, dan pendapatan.",
        "• Dokumen & Pass: QRCode.js untuk generasi e-Gate Pass, export-utils.js (Excel/PDF).",
        "• Testing & Mocking: Postman v11 API Suite untuk otomasi pengujian asersi status."
    ]
    p = tf.add_paragraph()
    p.text = "\n".join(stack_items)
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Live Demo Banner Bottom
    add_demo_banner(s3, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Pengaturan Global & Konektivitas Sistem",
                    "Buka menu Pengaturan Global (dashboard.php?page=settings). Tunjukkan status live basis data MySQL PDO, parameter terminal 35 Ha, dan matriks penanggung jawab tim.")

    # =========================================================================
    # SLIDE 4: PIPELINE INGESTION DATA DARI 11 HARDWARE SIMULASI
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "Pipeline Ingestion Data: Bagaimana Software Menerima Sinyal 11 Hardware")
    add_footer(s4, 4)

    c4_w = Inches(3.7)
    c4_h = Inches(4.0)

    # Col 1: Gate Ingestion
    create_card(s4, Inches(0.8), Inches(1.7), c4_w, c4_h)
    tb = s4.shapes.add_textbox(Inches(1.0), Inches(1.85), c4_w - Inches(0.4), c4_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 1: OTOMASI GERBANG"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Ingestion 6 Hardware Gate"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Kamera ANPR (#1): Mengirim string plat nomor via TCP/IP HTTP POST.\n• Kamera OCR (#2): Memancarkan kode ISO 6346 boks via socket ethernet.\n• Timbangan 80t (#6): Stream berat bruto kendaraan via serial RS-232 Modbus RTU.\n• RFID Reader 20m (#7): Mengirim tag ID supir via Wiegand-34 interface.\n• Edge AI PC (#18): Mengagregasi sensor gerbang menjadi 1 payload JSON ke endpoint YMS.\n• LED Display (#10): Menerima perintah serial untuk menampilkan instruksi blok penumpukan."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Col 2: Yard & Crane Ingestion
    create_card(s4, Inches(4.8), Inches(1.7), c4_w, c4_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s4.shapes.add_textbox(Inches(5.0), Inches(1.85), c4_w - Inches(0.4), c4_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 2: ALAT BERAT & YARD"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Ingestion Telemetri Derek"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• DGPS RTK Receiver (#21):\n  Mengirim koordinat NMEA-0183 ($GNGGA) akurasi sentimeter (<2 cm) dari boom Reach Stacker.\n\n• Spreader Twistlock Sensor (#22):\n  Mengirim status pin hidrolik via bus CANopen (0 = UNLOCKED, 1 = LOCKED) dan load cell tonase.\n\n• Logika Perkawinan Sinyal:\n  Software mendeteksi perubahan LOCKED ➔ UNLOCKED sebagai penanda mutlak pelepasan kontainer di yard, mengunci koordinat DGPS detik itu juga!"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Col 3: Cold Chain, Rail, & Customs
    create_card(s4, Inches(8.8), Inches(1.7), c4_w, c4_h)
    tb = s4.shapes.add_textbox(Inches(9.0), Inches(1.85), c4_w - Inches(0.4), c4_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 3, 4, & 5: FASILITAS KHUSUS"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_PURPLE
    p = tf.add_paragraph()
    p.text = "Reefer, Rel KA, & E-Seal"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Smart Reefer Socket (#23):\n  Ingestion arus ampere, daya kWh, dan suhu pendingin kompresor (-18°C) via LoRaWAN/Modbus setiap 5 detik.\n\n• Rail Axle Counter (#25):\n  Ingestion pulsa roda kereta dari sensor leher rel Frauscher, dikonversi menjadi hitungan gerbong.\n\n• Smart E-Seal GPS (#26):\n  Ingestion koordinat GPS rute Priok-CIDP dan status loop kawat segel via MQTT jaringan 4G."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Live Demo Banner Bottom
    add_demo_banner(s4, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Simulasi Gerbang & Telemetri Sensor Lapangan",
                    "Buka menu Manajemen Gate (dashboard.php?page=gate). Tunjukkan Tab 1: Jalankan simulasi Skenario Truk 1 (40HC) & Skenario 3 (Overweight Alert), lihat live feed ANPR, OCR, VGM, & LED. Lalu buka Tab 3: Tunjukkan telemetri IoT Reach Stacker, Reefer, Siding KA, & E-Seal.")

    # =========================================================================
    # SLIDE 5: CORE ENGINE 1: 3D YARD STACKING & COLLISION DETECTION
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "Core Engine 1: Dynamic Yard Allocation & 3D Stacking Matrix (Bay-Row-Tier)")
    add_footer(s5, 5)

    c5_w = Inches(5.7)
    c5_h = Inches(4.0)

    # Left: Transformasi Koordinat
    create_card(s5, Inches(0.8), Inches(1.7), c5_w, c5_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(1.85), c5_w - Inches(0.4), c5_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FOKUS EVALUASI SILABUS TOPIK 7"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Algoritma Pembaruan Posisi Stacking Yard"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Transformasi Koordinat Spasial:\n  Mengonversi sinyal DGPS satelit mentah (X, Y, Z) menjadi matriks standar terminal peti kemas dunia:\n  Block [A - E] ➔ Bay [01 - 12] ➔ Row [01 - 04] ➔ Tier [01 - 04].\n\n• Aturan Penumpukan Cerdas (Stacking Rules):\n  1. Heavy-on-Bottom Rule: Peti kemas bermuatan berat (Laden 25-32 ton) wajib di Tier 1 & 2; boks kosong (Empty) di Tier 3 & 4.\n  2. Anti-Overhang Check: Kontainer 40ft dilarang ditumpuk di atas kontainer 20ft.\n  3. Segregasi Kelas Bahaya: Kontainer DG otomatis dialokasikan ke Blok DG terisolasi."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Right: Collision Detection & WebGL Sync
    create_card(s5, Inches(6.8), Inches(1.7), c5_w, c5_h)
    tb = s5.shapes.add_textbox(Inches(7.0), Inches(1.85), c5_w - Inches(0.4), c5_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LOGIKA VALIDASI SISTEM"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Deteksi Tabrakan Slot & Sinkronisasi Atomik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Deteksi Tabrakan Slot (Collision Prevention):\n  Software memvalidasi ketersediaan slot sebelum eksekusi:\n  SELECT id FROM containers WHERE block=? AND bay=? AND row=? AND tier=? AND status='in_yard';\n  Jika terisi, perintah relokasi ditolak demi keamanan!\n\n• Transaksi Basis Data Atomik (ACID):\n  1. Update koordinat kontainer.\n  2. Pelepasan status alat berat Reach Stacker.\n  3. Pencatatan audit trail & biaya Lo-Lo Rp 250.000 ke tabel yard_events.\n\n• Sinkronisasi Seketika ke WebGL 3D:\n  Data hasil mutasi database langsung memicu event listener Three.js untuk memindahkan mesh 3D kontainer secara halus."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Live Demo Banner Bottom
    add_demo_banner(s5, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Panel Simulasi 3D Virtual Terminal",
                    "Buka menu Panel Simulasi IoT (dashboard.php?page=simulator). Klik tombol 'Pindahkan Box (RS)', pilih slot tujuan. Tunjukkan animasi crane Three.js mengangkat boks, update koordinat DB, dan klik kontainer 3D untuk inspeksi metadata Raycasting.")

    # =========================================================================
    # SLIDE 6: CORE ENGINE 2: OTOMASI GATE, SOLAS VGM & E-PASS
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "Core Engine 2: Otomasi Gerbang, Kalkulasi VGM SOLAS & e-Gate Pass (<28 Detik)")
    add_footer(s6, 6)

    # 4 Sequential Cards
    st_w = Inches(2.7)
    st_h = Inches(4.0)

    steps_short = [
        ("TAHAP 1: MATCHING", "Data Matching & OCR", C_BLUE,
         "• Loop detector memicu ANPR.\n• Matching plat nomor truk dengan Odoo Fleet.\n• Kamera OCR membaca nomor kontainer ISO 6346 ➔ mencocokkan status Delivery Order (DO) di database."),
        ("TAHAP 2: KALKULASI VGM", "Validasi SOLAS 80 Ton", C_EMERALD,
         "• Timbangan membaca berat kotor.\n• Net VGM = Gross - Tare.\n• Lolos jika Net <= 34 ton (VERIFIED_PASS); Tolak jika overload (>34t).\n• Membebankan Jasa Timbang VGM Rp 50.000."),
        ("TAHAP 3: ALOKASI BLOK", "Smart Routing Engine", C_INDIGO,
         "• Memeriksa jenis muatan: Dry ➔ Blok A/B/C; Reefer ➔ Blok Reefer; DG ➔ Blok Bahaya.\n• Memilih slot kosong terdekat guna menghemat konsumsi BBM solar crane.\n• Teks LED: 'SILAHKAN MASUK B04'."),
        ("TAHAP 4: OTOMASI PASS", "E-Gate Pass & Barrier", C_PURPLE,
         "• Edge PC memicu relay mengangkat palang gerbang (1.2 detik).\n• QRCode.js Engine: Menerbitkan tiket digital e-Gate Pass berkode QR (format GP-YYYYMMDD-XXXX) ke supir.\n• Transaksi selesai dalam 28 detik!")
    ]

    for i, (title, sub, col, body) in enumerate(steps_short):
        left_pos = Inches(0.8 + i * 2.95)
        create_card(s6, left_pos, Inches(1.7), st_w, st_h)
        tb = s6.shapes.add_textbox(left_pos + Inches(0.15), Inches(1.85), st_w - Inches(0.3), st_h - Inches(0.3))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = col
        p = tf.add_paragraph()
        p.text = sub
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(2)
        p = tf.add_paragraph()
        p.text = body
        p.font.size = Pt(8.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(5)

    # Live Demo Banner Bottom
    add_demo_banner(s6, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Pelacakan Kontainer & Manajemen Trucking",
                    "Buka menu Pelacakan Kontainer (dashboard.php?page=kontainer) & Manajemen Trucking (page=trucking). Tunjukkan daftar nomor ISO 6346, tag RFID, status kepabeanan SPPB, dan tiket e-Gate Pass berkode QR hasil generasi QRCode.js.")

    # =========================================================================
    # SLIDE 7: BUSINESS INTELLIGENCE & SINKRONISASI ERP ODOO (ZERO LEAKAGE)
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "Business Intelligence & Integrasi Finansial ERP Odoo (Zero Revenue Leakage)")
    add_footer(s7, 7)

    c7_w = Inches(5.7)
    c7_h = Inches(4.0)

    # Left: Business Intelligence Output
    create_card(s7, Inches(0.8), Inches(1.7), c7_w, c7_h)
    tb = s7.shapes.add_textbox(Inches(1.0), Inches(1.85), c7_w - Inches(0.4), c7_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "BUSINESS INTELLIGENCE OPERASIONAL"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Transformasi Data Mentah Jadi Wawasan"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "1. Audit Dwell Time Real-Time:\nMenghitung masa inap: Dwell = NOW() - Gate_In_Time.\nAudit 3 Masa Tarif: Masa I Free (1-3 hari), Masa II Rp 45rb/hari (4-10 hari), Masa III Rp 90rb/hari (>10 hari). Heatmap yard menyoroti boks overstay agar rata-rata dwell time ditekan ke 2.1 hari!\n\n2. Okupansi Blok Yard (Dynamic Load Balancing):\nMenghitung % keterisian 200 slot. Jika Blok A >80%, sistem otomatis memindahkan alokasi kontainer baru ke Blok B/C.\n\n3. Produktivitas Alat Berat (OEE):\nMelacak Moves per Hour (>22 box/jam) & konsumsi solar per gerakan kontainer."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Right: Event-Driven Odoo Integration
    create_card(s7, Inches(6.8), Inches(1.7), c7_w, c7_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s7.shapes.add_textbox(Inches(7.0), Inches(1.85), c7_w - Inches(0.4), c7_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "EVENT-DRIVEN ERP ODOO SYNC"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Akuntabilitas Finansial Tanpa Bocor"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "Setiap mutasi fisik otomatis menjadi entri jurnal finansial Odoo:\n• Truk Masuk Timbangan ➔ Jasa VGM SOLAS Rp 50.000.\n• RS Angkat/Lepas Box ➔ Jasa Lift-Off / Lo-Lo Rp 250.000.\n• Inap Yard Hari Ke-5 ➔ Jasa Penumpukan Masa II Rp 45.000/hari.\n• Steker Reefer Aktif ➔ Jasa Listrik Dingin Rp 250.000 / shift.\n• Alih Muat Kereta Api ➔ Jasa Rail Stevedoring Rp 350.000 / box.\n\nPrinsip Zero Revenue Leakage:\nTidak ada jasa fisik yang luput dari penagihan. Semua terkonversi menjadi piutang dan e-Faktur resmi di Odoo Invoicing!"
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # Live Demo Banner Bottom
    add_demo_banner(s7, Inches(0.8), Inches(5.9), Inches(11.7), Inches(0.95),
                    "Dasbor Beranda Eksekutif & Billing Finansial",
                    "Buka menu Beranda Eksekutif (dashboard.php?page=beranda). Tunjukkan 6 kartu KPI ringkas, grafik tren gate Chart.js, gauge utilisasi yard (63%), rata-rata dwell time (2.1 hari), dan ringkasan gross revenue harian.")

    # =========================================================================
    # SLIDE 8: CHEAT SHEET DEMO LIVE WEB & KESIMPULAN
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "Cheat Sheet Panduan Live Demo Web & Kesimpulan Nilai Tambah")
    add_footer(s8, 8)

    # Left: Cheat Sheet Matrix
    create_card(s8, Inches(0.8), Inches(1.7), Inches(7.5), Inches(5.15))
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(1.85), Inches(7.1), Inches(4.8))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "CHEAT SHEET PANDUAN LIVE DEMO WEB (UNTUK PRESENTER)"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Matriks Rujukan Saat Demonstrasi Langsung di Depan Dosen"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)

    demo_matrix = [
        ("1. Beranda Eksekutif", "page=beranda", "Chart.js, KPI Cards, Dwell Meter", "Tunjukkan rata-rata Dwell 2.1 hari & Gross Revenue Rp 47.2M"),
        ("2. Manajemen Gate", "page=gate", "Otomasi 5-Tahap, Timbangan, LED", "Jalankan simulasi Truk 1 (Lolos) & Truk 3 (Overweight Alert >34t)"),
        ("3. Panel Simulasi 3D", "page=simulator", "Three.js WebGL, Tween.js, Raycaster", "Klik 'Pindahkan Box (RS)', tunjukkan animasi crane & klik boks 3D"),
        ("4. Pelacakan Kontainer", "page=kontainer", "Tabel Kontainer, RFID Tag, GS1 SSCC", "Tunjukkan koordinat 3D Bay-Row-Tier & status kepabeanan SPPB"),
        ("5. Manajemen Trucking", "page=trucking", "Armada Truk, Tara/Bruto, e-Pass", "Tunjukkan verifikasi berat jembatan timbang & QR Code supir"),
        ("6. Lokasi Alat Berat", "page=alat", "Radar 2D Terminal, GPS Koordinat", "Tunjukkan posisi Reach Stacker RS-01/02/03 & level BBM solar"),
        ("7. Pengaturan Global", "page=settings", "Koneksi PDO, Parameter 35 Ha", "Tunjukkan status Live DB MySQL & matriks tanggung jawab tim")
    ]

    p = tf.add_paragraph()
    p.text = "Alur Live Demo Berurutan:"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    for no, url, tech, desc in demo_matrix:
        p = tf.add_paragraph()
        p.text = f"• {no} [{url}]:\n   {desc} ({tech})"
        p.font.size = Pt(8.2)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(2)

    # Right: Strategic Value Pillars
    create_card(s8, Inches(8.5), Inches(1.7), Inches(4.0), Inches(5.15), bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s8.shapes.add_textbox(Inches(8.7), Inches(1.85), Inches(3.6), Inches(4.8))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "4 PILAR NILAI STRATEGIS"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Keunggulan CIDP YMS"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "1. Kecepatan Operasional:\nOtomasi gate 28 detik & alokasi yard cerdas memangkas dwell time ke 2.1 hari.\n\n2. Integritas Data Absolut:\n100% data ditangkap otomatis oleh sensor tanpa salah ketik manual.\n\n3. Zero Revenue Leakage:\nSetiap gerakan fisik alat berat langsung tercatat menjadi faktur resmi di ERP Odoo.\n\n4. Multimoda Terpadu:\nSinkronisasi mulus truk jalan raya, kereta api logistik, dan kepabeanan DJBC CEISA 4.0."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Save presentation
    output_path = os.path.join(os.path.dirname(__file__), "PRESENTASI_SOFTWARE_RINGKAS_CIDP.pptx")
    prs.save(output_path)
    print(f"[SUCCESS] Concise Presentation created successfully at: {output_path}")

if __name__ == "__main__":
    create_concise_presentation()
