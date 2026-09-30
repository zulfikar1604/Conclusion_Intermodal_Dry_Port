import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_software_presentation():
    prs = Presentation()
    # 16:9 Widescreen (13.333 x 7.5 Inches)
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    TOTAL_SLIDES = 15

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
    C_PURPLE_BG = RGBColor(250, 245, 255)# #faf5ff
    C_AMBER_BG = RGBColor(254, 243, 199) # #fef3c7

    def add_header(slide, title_text, category_text="TOPIK 7: INLAND CONTAINER DEPOT (ICD) & DRY PORT MANAGEMENT"):
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.7), Inches(1.1))
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
        footer_box = slide.shapes.add_textbox(Inches(0.8), Inches(7.0), Inches(11.7), Inches(0.35))
        tf = footer_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = f"Afriansayah Ayubi & Zulfikar J. Fatah — Software & Architecture | Topik 7: ICD & Dry Port Management (CIDP 35 Ha)                    Slide {current_slide} of {TOTAL_SLIDES}"
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

    # Title Box (Top)
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
    p.text = "PROGRES MINGGU INI: ARSITEKTUR SOFTWARE, INGESTION DATA HARDWARE & ENGINE INFORMASI INTELEJEN OPERASIONAL"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p.space_before = Pt(6)

    # Team Members Box (Middle)
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

    # Footer Box (Bottom)
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
    # SLIDE 2: LANSKAP TAKSONOMI 4-LAYER IT LOGISTIK & 3 ALIRAN UTAMA
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "Taksonomi 4-Layer IT Logistik & Pengelolaan 3 Aliran Utama Terminal")
    add_footer(s2, 2)

    card_w = Inches(3.7)
    card_h = Inches(4.8)

    # Card 1: 3 Aliran Utama
    create_card(s2, Inches(0.8), Inches(1.8), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(1.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LANDASAN TEORI LOGISTIK"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Sinkronisasi 3 Aliran Logistik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• 1. Aliran Fisik Barang (Goods Flow):\n  Pergerakan kontainer riil di lapangan 35 Ha: Gate ➔ Timbangan ➔ Lapangan Penumpukan Blok A-E ➔ Rangkaian Kereta Api Logistik.\n\n• 2. Aliran Informasi (Information Flow):\n  Data telemetri instan dari sensor (OCR, ANPR, DGPS, Twistlock, Suhu) yang diolah menjadi koordinat Bay-Row-Tier dan status peti kemas.\n\n• 3. Aliran Finansial (Financial Flow):\n  Penerbitan billing, e-Faktur, dan rekonsiliasi piutang jasa terminal (Lo-Lo, penumpukan progresif, plugging reefer, timbang VGM) ke ERP Odoo."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: 4-Layer IT Taxonomy
    create_card(s2, Inches(4.8), Inches(1.8), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s2.shapes.add_textbox(Inches(5.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FRAMEWORK DR. TIGOR FRANKY"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Taksonomi 4 Lapisan IT Logistik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Layer 1: Sensing & Capture (Hardware)\n  26 item alat fisik (kamera OCR, timbangan 80t, RFID UHF, twistlock, DGPS RTK).\n\n• Layer 2: Network & Protocols (Middleware)\n  Wi-Fi 7, 4G LTE, LoRaWAN, RS-232, TCP/IP.\n\n• Layer 3: Application & Systems (Core YMS)\n  Perangkat lunak YMS, 3D WebGL Engine, Stacking Rules, dan Terminal Tariff Engine.\n\n• Layer 4: Integration & Data Protocols\n  REST API, JSON Payload, CEISA 4.0 DJBC (Kepabeanan), SAKA KAI, dan Odoo ERP."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: Peran Kunci Software
    create_card(s2, Inches(8.8), Inches(1.8), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(9.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
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
    p.text = "• Hardware Hanyalah Alat Pengumpul:\n  Sensor hanya membaca voltase, string plat, atau koordinat angka tanpa makna bisnis.\n\n• Software Mentransformasi Data:\n  1. Mengubah sinyal bobot ➔ Status VGM SOLAS.\n  2. Mengubah koordinat GPS ➔ Slot Blok-Bay-Row-Tier.\n  3. Mengubah pelepasan box ➔ Tagihan Lo-Lo Rp 250rb.\n  4. Mengubah masa inap ➔ Audit dwell time & heatmap.\n\n• Menjawab Arahan Dosen:\n  Membuktikan bagaimana software menerima data mentah hardware dan mengolahnya menjadi kecerdasan operasional."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 3: ARSITEKTUR TEKNOLOGI SOFTWARE: DECOUPLED STACK & SANDBOX
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "Arsitektur Teknologi Software: Decoupled Stack & Virtual IoT Sandbox")
    add_footer(s3, 3)

    col_w = Inches(5.7)
    col_h = Inches(4.8)

    # Left Column: Decoupled Architecture Philosophy
    create_card(s3, Inches(0.8), Inches(1.8), col_w, col_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s3.shapes.add_textbox(Inches(1.0), Inches(2.0), col_w - Inches(0.4), col_h - Inches(0.4))
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
    p.text = "• Mengapa Arsitektur Decoupled (Terpisah)?\n  Software YMS tidak pernah mengikatkan diri langsung ke kabel fisik sensor lapangan. Komunikasi selalu melalui IoT Edge Gateway berbasis standar REST API & JSON Payload.\n\n• Jawaban Resmi Mengenai Ketiadaan Alat Asli di Kelas:\n  Dalam industri enterprise maritim kelas dunia (misal Navis N4), software selalu diuji menggunakan Virtual Simulator Payload.\n  Struktur JSON yang ditembakkan oleh simulator kami 100% identik dengan sinyal gateway fisik Advantech nantinya.\n\n• Transisi Plug-and-Play ke Hardware Asli:\n  Saat perangkat fisik dipasang di pelabuhan, alat cukup menembak endpoint API yang sama tanpa mengubah 1 baris kode pun pada software YMS!"
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Right Column: Full Technology Stack
    create_card(s3, Inches(6.8), Inches(1.8), col_w, col_h, bg_color=C_CARD_BG, border_color=C_BORDER)
    tb = s3.shapes.add_textbox(Inches(7.0), Inches(2.0), col_w - Inches(0.4), col_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KOMPONEN TEKNOLOGI LENGKAP"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Enterprise Modern Web & 3D WebGL Stack"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    
    stack_points = [
        "• Backend & Database: PHP 8.2 Enterprise Engine dengan PDO MySQL (ACID Transactions, Prepared Statements, Zero SQL Injection), MySQL 8.0 InnoDB.",
        "• 3D Digital Twin Engine: Three.js r128 (WebGL), OrbitControls 360°, Tween.js untuk animasi kinematik Reach Stacker/truk, Raycaster 3D Inspector.",
        "• 2D GIS & Terminal Mapping: Leaflet.js v1.9 untuk pemetaan spasial denah 35 Ha, plotting GPS armada, dan overlay zona operasional.",
        "• Frontend UI/UX: Tailwind CSS 3.4 (Responsive Mobile/Desktop), Vanilla ES6 JavaScript, Google Font Plus Jakarta Sans, FontAwesome 6 Icons.",
        "• Visual Analytics: Chart.js 4.4 untuk tren gerbang, okupansi yard per blok, rasio kargo, dan analitik pendapatan harian.",
        "• Utilities & Reporting: QRCode.js untuk generasi instan e-Gate Pass supir, export-utils.js (ekspor laporan XLSX, PDF, CSV, & Print).",
        "• Testing & Middleware: Postman v11 API Suite (Mock Server, Automated Assertion Scripts, Regression Suite) & REST API Gateway terpadu."
    ]
    p = tf.add_paragraph()
    p.text = "\n".join(stack_points)
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(5)

    # =========================================================================
    # SLIDE 4: PIPELINE INGESTION DATA: BAGAIMANA SOFTWARE MENERIMA DATA HARDWARE
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "Pipeline Ingestion Data: Bagaimana Software Menerima Sinyal 11 Hardware Simulasi")
    add_footer(s4, 4)

    c4_w = Inches(3.7)
    c4_h = Inches(4.8)

    # Col 1: Gate Ingestion
    create_card(s4, Inches(0.8), Inches(1.8), c4_w, c4_h)
    tb = s4.shapes.add_textbox(Inches(1.0), Inches(2.0), c4_w - Inches(0.4), c4_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 1: OTOMASI GERBANG"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Ingestion 6 Hardware Gate"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Kamera ANPR (#1): Mengirim string plat nomor via TCP/IP HTTP POST saat loop detector aktif.\n• Kamera OCR (#2): Memancarkan kode ISO 6346 (4 huruf + 7 angka) via socket ethernet.\n• Jembatan Timbang 80t (#6): Stream data berat bruto melalui serial RS-232 Modbus RTU.\n• RFID Reader 20m (#7): Mengirim tag ID EPC Gen2 supir via Wiegand-34 interface.\n• Edge AI PC (#18): Mengagregasi 4 sensor di atas menjadi 1 payload JSON terpadu.\n• LED Display (#10): Menerima perintah serial untuk menampilkan instruksi blok penumpukan."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 2: Yard & Crane Ingestion
    create_card(s4, Inches(4.8), Inches(1.8), c4_w, c4_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s4.shapes.add_textbox(Inches(5.0), Inches(2.0), c4_w - Inches(0.4), c4_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 2: ALAT BERAT & YARD"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Ingestion Telemetri Crane"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• DGPS RTK Receiver (#21):\n  Mengirim NMEA-0183 ($GNGGA) dengan koreksi diferensial RTK (<2 cm) untuk koordinat presisi alat berat (RS-01/02/03).\n\n• Spreader Twistlock Sensor (#22):\n  Mengirim status pin hidrolik via bus CANopen (0 = UNLOCKED, 1 = LOCKED) dan tonase load cell.\n\n• Logika Perkawinan Sinyal:\n  Software mendeteksi perubahan status dari LOCKED ➔ UNLOCKED sebagai penanda mutlak pelepasan kontainer di slot yard, mengunci koordinat DGPS detik itu juga!"
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 3: Cold Chain, Rail, & Customs Ingestion
    create_card(s4, Inches(8.8), Inches(1.8), c4_w, c4_h)
    tb = s4.shapes.add_textbox(Inches(9.0), Inches(2.0), c4_w - Inches(0.4), c4_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KLASTER 3, 4, & 5: FASILITAS KHUSUS"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_PURPLE
    p = tf.add_paragraph()
    p.text = "Reefer, Rel KA, & E-Seal"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Smart Reefer Socket (#23):\n  Ingestion telemetri arus ampere, daya kWh, dan suhu pendingin kompresor (-18°C) via LoRaWAN / Modbus RTU setiap 5 detik.\n\n• Rail Axle Counter (#25):\n  Ingestion pulsa roda kereta dari sensor Frauscher di leher rel sepur simpan, dikonversi software menjadi jumlah gerbong datar.\n\n• Smart E-Seal GPS (#26):\n  Ingestion koordinat GPS rute transit Priok-CIDP dan status loop kawat segel melalui protokol MQTT via jaringan seluler 4G LTE."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 5: CORE ENGINE 1: DYNAMIC YARD ALLOCATION & 3D STACKING MATRIX
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "Core Engine 1: Dynamic Yard Allocation & 3D Stacking Matrix (Bay-Row-Tier)")
    add_footer(s5, 5)

    c5_w = Inches(5.7)
    c5_h = Inches(4.8)

    # Left: The Coordinate Transformation & Stacking Rules
    create_card(s5, Inches(0.8), Inches(1.8), c5_w, c5_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(2.0), c5_w - Inches(0.4), c5_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FOKUS EVALUASI SILABUS TOPIK 7"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Algoritma Pembaruan Posisi Stacking Yard"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Transformasi Koordinat Spasial:\n  Mengonversi data satelit mentah DGPS (Latitude, Longitude, Altitude) menjadi sistem koordinat standar terminal maritim dunia:\n  Block [A - E] ➔ Bay [01 - 12] ➔ Row [01 - 04] ➔ Tier [01 - 04].\n\n• Aturan Penumpukan Cerdas (Stacking Rules Engine):\n  1. Heavy-on-Bottom Rule: Peti kemas bermuatan berat (Laden 25-32 ton) wajib di Tier 1 & 2; peti kosong (Empty) di Tier 3 & 4.\n  2. Anti-Overhang Check: Kontainer 40ft High Cube tidak diizinkan ditumpuk di atas kontainer 20ft standar.\n  3. Segregasi Kelas Bahaya: Kontainer DG (Dangerous Goods) otomatis dialokasikan ke Blok DG berjarak aman dari kantor & rel KA."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Right: Collision Detection & Atomic Sync
    create_card(s5, Inches(6.8), Inches(1.8), c5_w, c5_h)
    tb = s5.shapes.add_textbox(Inches(7.0), Inches(2.0), c5_w - Inches(0.4), c5_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LOGIKA VALIDASI SISTEM"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Deteksi Tabrakan Slot & Sinkronisasi Atomik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Deteksi Tabrakan Slot (Slot Collision Detection):\n  Sebelum mutasi database disimpan, software memverifikasi apakah koordinat tujuan sudah dihuni kontainer berstatus 'in_yard':\n  SELECT id FROM containers WHERE block=? AND bay=? AND row=? AND tier=? AND status='in_yard';\n  Jika terisi, sistem menolak dan memberikan rekomendasi slot alternatif terdekat!\n\n• Transaksi Basis Data Atomik (ACID):\n  1. UPDATE containers (posisi Bay-Row-Tier baru).\n  2. UPDATE equipment (status RS kembali idle, lepas kontainer).\n  3. INSERT INTO yard_events (audit trail perpindahan & biaya Lo-Lo).\n\n• Sinkronisasi Seketika ke WebGL 3D:\n  Data hasil mutasi database langsung memicu event listener Three.js untuk memindahkan mesh 3D kontainer secara halus di layar pengguna."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 6: CORE ENGINE 2: OTOMASI GERBANG, KALKULASI VGM SOLAS & E-PASS
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "Core Engine 2: Otomasi Gerbang, Kalkulasi VGM SOLAS & Penerbitan e-Pass")
    add_footer(s6, 6)

    # 4 Sequential Processing Step Cards
    step_w = Inches(2.7)
    step_h = Inches(4.8)

    steps_data = [
        ("TAHAP 1: IDENTIFIKASI", "Data Matching & OCR", C_BLUE,
         "• Sinyal Masuk: Loop detector mendeteksi truk trailer melintas.\n• Matching Plat: Kamera ANPR membaca plat nomor ➔ dicocokkan dengan data armada terdaftar di Odoo Fleet.\n• Matching Box: Kamera OCR membaca nomor kontainer ISO 6346 ➔ mencocokkan status manifes / Delivery Order (DO).\n• Hasil: Validasi kepemilikan dan kelayakan kargo masuk terminal."),
        ("TAHAP 2: KALKULASI VGM", "Validasi SOLAS 80 Ton", C_EMERALD,
         "• Pembacaan Sensor: 8 Load cell timbangan membaca berat bruto total kendaraan.\n• Formula Komputasi:\n  Net VGM = Gross Timbang - Tare Truk.\n• Kepatuhan Maritim SOLAS:\n  - Jika Net <= 34.000 kg ➔ VERIFIED_PASS.\n  - Jika Net > 34.000 kg ➔ OVERLOAD_REJECT.\n• Pemicu Finansial: Otomatis membebankan Jasa Timbang VGM Rp 50.000."),
        ("TAHAP 3: ALOKASI BLOK", "Smart Routing Engine", C_INDIGO,
         "• Penentuan Lokasi Yard:\n  Software memeriksa jenis kargo kontainer:\n  - Dry Kargo ➔ Blok A/B/C.\n  - Reefer Dingin ➔ Blok Reefer (dekat steker).\n  - Kargo Berbahaya ➔ Blok DG terisolasi.\n• Optimasi Manuver:\n  Memilih slot kosong terdekat untuk meminimalkan jarak tempuh armada Reach Stacker.\n• Pengiriman Teks LED: 'SILAHKAN KE BLOK B04'."),
        ("TAHAP 4: OTOMASI PALANG", "E-Gate Pass & Izin Masuk", C_PURPLE,
         "• Eksekusi Fisik: Edge PC mengaktifkan relay kontrol untuk membuka barrier gate kecepatan tinggi (1.2 detik).\n• Dokumen Digital (QRCode.js Engine):\n  Menerbitkan tiket digital e-Gate Pass berkode QR instan (format GP-YYYYMMDD-XXXX) ke ponsel supir.\n• Total Waktu Transaksi: Selesai hanya dalam 28 detik (pangkas 65% waktu gate manual)!")
    ]

    for i, (title, sub, col, body) in enumerate(steps_data):
        left_pos = Inches(0.8 + i * 2.95)
        create_card(s6, left_pos, Inches(1.8), step_w, step_h)
        tb = s6.shapes.add_textbox(left_pos + Inches(0.15), Inches(2.0), step_w - Inches(0.3), step_h - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(10)
        p.font.bold = True
        p.font.color.rgb = col
        p = tf.add_paragraph()
        p.text = sub
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(3)
        p = tf.add_paragraph()
        p.text = body
        p.font.size = Pt(8.8)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 7: CORE ENGINE 3: COLD CHAIN, REL KA, & KEPABEANAN CEISA 4.0
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "Core Engine 3: Telemetri Cold Chain, Siding Kereta Api, & Bea Cukai CEISA 4.0")
    add_footer(s7, 7)

    c7_w = Inches(3.7)
    c7_h = Inches(4.8)

    # Card 1: Cold Chain Engine
    create_card(s7, Inches(0.8), Inches(1.8), c7_w, c7_h)
    tb = s7.shapes.add_textbox(Inches(1.0), Inches(2.0), c7_w - Inches(0.4), c7_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "COLD CHAIN TELEMETRY ENGINE"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Smart Reefer Watchdog"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Monitoring 300 Titik Steker Listrik 380V:\n  Menerima sinyal arus dan daya per socket secara kontinu.\n\n• Threshold Alert (Pencegahan Busuk Kargo):\n  Target komoditas beku: -18°C s/d -20°C.\n  Jika suhu melonjak > -15°C atau arus listrik 0 Ampere (indikasi trip listrik/kabel putus), software membunyikan alarm visual merah di NOC dalam waktu 3 detik.\n\n• Kalkulasi Otomatis Tarif Steker:\n  Software menghitung durasi colokan aktif, memicu tagihan Rp 250.000 per shift 8 jam ke Odoo Invoicing."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: Intermodal Rail Siding Engine
    create_card(s7, Inches(4.8), Inches(1.8), c7_w, c7_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s7.shapes.add_textbox(Inches(5.0), Inches(2.0), c7_w - Inches(0.4), c7_h - Inches(0.4))
    tf = tf_box = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "INTERMODAL RAIL SIDING ENGINE"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "Deteksi Rangkaian KA Logistik"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Algoritma Konversi Pulsa Axle Counter:\n  Software menerima pulsa hitungan roda dari sensor leher rel Frauscher:\n  Total Gerbong = Axle Count / 4\n  Total Kapasitas = Total Gerbong x 2 TEU.\n  (Contoh: 120 Axle = 30 Gerbong Datar = 60 TEU).\n\n• Alokasi RTG Crane Otomatis:\n  Memicu instruksi kerja (Job Order) alih muat kontainer dari gerbong datar kereta ke tumpukan Blok A.\n\n• Rekonsiliasi Sewa Rel (TAC):\n  Menghitung bagi hasil Track Access Charge bersama PT Kereta Api Logistik (KAI Logistik)."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: CEISA 4.0 Customs Pipeline
    create_card(s7, Inches(8.8), Inches(1.8), c7_w, c7_h)
    tb = s7.shapes.add_textbox(Inches(9.0), Inches(2.0), c7_w - Inches(0.4), c7_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KEPABEANAN & CEISA 4.0 DJBC"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_PURPLE
    p = tf.add_paragraph()
    p.text = "Pelacakan E-Seal & Rilis SPPB"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Pengawasan Koridor Transit Priok-CIDP:\n  Software memetakan posisi GPS kontainer import berstatus TPS transit secara geofencing.\n\n• Tamper Alert Sabotase Kargo:\n  Jika kawat segel elektronik Smart E-Seal dipotong paksa sebelum izin pabean terbit, sistem membunyikan alarm darurat dan mengunci kargo.\n\n• Integrasi Jalur Pabean:\n  Sinkronisasi dokumen SPPB (Surat Persetujuan Pengeluaran Barang):\n  - Jalur Hijau: Rilis langsung ke truk.\n  - Jalur Merah: Alokasi otomatis ke area Behandle / Pemindai X-Ray 6 MeV."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 8: BUSINESS INTELLIGENCE ENGINE: TRANSFORMASI RAW DATA JADI INFORMASI
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "Business Intelligence Engine: Transformasi Data Mentah Menjadi Keputusan Cerdas")
    add_footer(s8, 8)

    q_w = Inches(5.7)
    q_h = Inches(2.3)

    # Q1: Dwell Time
    create_card(s8, Inches(0.8), Inches(1.8), q_w, q_h)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(1.9), q_w - Inches(0.4), q_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "1. AUDIT DWELL TIME & PERINGATAN INAP PROGRESIF"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "• Menghitung lama inap real-time: Dwell = NOW() - Gate_In_Time.\n• Audit 3 Masa Tarif: Masa I Free (1-3 hari), Masa II Rp 45rb/hari (4-10 hari), Masa III Rp 90rb/hari (>10 hari).\n• Heatmap Lapangan: Memberikan warna visual pada kontainer yang overstay agar diprioritaskan keluar."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Q2: Yard Utilization
    create_card(s8, Inches(6.8), Inches(1.8), q_w, q_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s8.shapes.add_textbox(Inches(7.0), Inches(1.9), q_w - Inches(0.4), q_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "2. OKUPANSI BLOK YARD & OPTIMASI KAPASITAS 35 HA"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "• Menghitung persentase keterisian per blok: Occupancy % = (Slot Terisi / Kapasitas Total) x 100%.\n• Load Balancing Lapangan: Jika Blok A >80%, sistem otomatis mengarahkan truk masuk baru ke Blok B atau C.\n• Mencegah kemacetan antrean Reach Stacker di satu blok penumpukan yang padat."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Q3: Equipment Productivity
    create_card(s8, Inches(0.8), Inches(4.3), q_w, q_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(4.4), q_w - Inches(0.4), q_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "3. PRODUKTIVITAS ALAT BERAT & MONITORING BBM"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "• Melacak metrik kinerja: Moves per Hour (target >22 box/jam untuk Reach Stacker).\n• Monitoring Bahan Bakar Solar: Menghitung rasio konsumsi liter/gerakan kontainer untuk mendeteksi inefisiensi.\n• Odoo Maintenance Trigger: Setiap 250 jam jalan, sistem otomatis memesan servis ganti oli dan pelumasan boom."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Q4: Gate Turnaround
    create_card(s8, Inches(6.8), Inches(4.3), q_w, q_h)
    tb = s8.shapes.add_textbox(Inches(7.0), Inches(4.4), q_w - Inches(0.4), q_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "4. TRUCK TURNAROUND TIME (TTT) & ANALISIS BOTTLENECK"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p = tf.add_paragraph()
    p.text = "• Mengukur durasi truk sejak tap di gerbang masuk hingga keluar: Target TTT < 35 Menit.\n• Deteksi Dini Kemacetan: Mengidentifikasi jika proses penimbangan VGM atau pencarian slot melambat.\n• Dashboard Eksekutif: Menampilkan rasio ketepatan waktu pengiriman kargo (98% On-Time Delivery)."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # =========================================================================
    # SLIDE 9: TARIFF ENGINE & SINKRONISASI FINANSIAL ERP ODOO (ZERO LEAKAGE)
    # =========================================================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "Terminal Tariff Engine & Integrasi Finansial ERP Odoo (Zero Revenue Leakage)")
    add_footer(s9, 9)

    rows = 7
    cols = 6
    t9_shape = s9.shapes.add_table(rows, cols, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    t9 = t9_shape.table
    t9.columns[0].width = Inches(2.2)
    t9.columns[1].width = Inches(1.8)
    t9.columns[2].width = Inches(2.1)
    t9.columns[3].width = Inches(1.8)
    t9.columns[4].width = Inches(1.8)
    t9.columns[5].width = Inches(2.0)

    headers9 = ["Kejadian Fisik (Hardware)", "Sensor Pemicu", "Rumus / Logika Tarif YMS", "Nilai Jasa (IDR)", "Modul ERP Odoo", "Dampak Akuntansi"]
    for j, h in enumerate(headers9):
        cell = t9.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(9)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    tariff_matrix = [
        ("Truk Masuk Timbangan", "Weighbridge 80t (#6)", "Biaya verifikasi SOLAS bersertifikat", "Rp 50.000 / truk", "Odoo Invoicing", "Pencatatan pendapatan jasa timbang"),
        ("RS Angkat/Lepas Box", "Twistlock & DGPS (#21,22)", "Tarif Lo-Lo: 40ft Rp 250k / 20ft Rp 175k", "Rp 250.000 / box", "Odoo Invoicing", "Pendapatan stevedoring yard"),
        ("Inap Yard Hari Ke-5", "Audit Dwell Timer YMS", "Tarif Masa II: Rp 45.000/box/hari", "Rp 45.000 / hari", "Odoo Invoicing", "Piutang sewa lapangan progresif"),
        ("Steker Reefer Aktif", "Smart Socket 380V (#23)", "Tarif listrik dingin per 8 jam (shift)", "Rp 250.000 / shift", "Odoo Invoicing", "Pendapatan utilitas cold chain"),
        ("Bongkar Kereta Api", "Axle Counter & RTG (#25)", "Tarif alih muat antarmoda rel ke yard", "Rp 350.000 / box", "Odoo Invoicing", "Pendapatan intermodal handling"),
        ("Sewa Jalur Rel KAI", "Sensor Gandar Frauscher (#25)", "Bagi hasil Track Access Charge PT KAI", "Rp 1.500.000 / gerbong", "Odoo Purchase", "Beban operasional sewa rel (HPP)")
    ]

    for i, row in enumerate(tariff_matrix):
        for j, val in enumerate(row):
            cell = t9.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_LIGHT_BG if i % 2 == 0 else C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(8.5)
                p.font.color.rgb = C_TEXT

    # =========================================================================
    # SLIDE 10: SKEMA BASIS DATA RELASIONAL & AUDIT TRAIL
    # =========================================================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "Struktur Data Relasional: Skema Basis Data cidp_yms & Audit Trail")
    add_footer(s10, 10)

    # 4 Entity Cards
    e_w = Inches(2.7)
    e_h = Inches(4.8)

    entities = [
        ("TABEL: containers", "Entitas Master Peti Kemas", C_BLUE,
         "• id (INT, PK, Auto Increment)\n• container_number (VARCHAR 11, Unique)\n• iso_code (VARCHAR 4, misal 42G1)\n• size_type (40FT HC / 20FT STD)\n• cargo_type (dry / reefer / dg / empty)\n• rfid_tag (VARCHAR 32, UHF EPC Gen2)\n• sscc_code (VARCHAR 30, Standar GS1)\n• gross_weight_kg (DECIMAL 10,2)\n• owner_company (VARCHAR 100)\n• seal_number (VARCHAR 50)\n• customs_status (SPPB_CLEARED)\n• block, bay, row, tier (CHAR & INT)\n• gate_in_time, gate_out_time\n• status (in_yard / gate_out)"),
        ("TABEL: equipment", "Entitas Armada Alat Berat", C_EMERALD,
         "• id (INT, PK, Auto Increment)\n• equipment_id (VARCHAR 10, misal RS-01)\n• equipment_type (REACH_STACKER / RTG)\n• brand_model (Kalmar DRG450-65S5)\n• operator_name (Budi Santoso)\n• operator_id (OPR-001)\n• status (idle / operating / maintenance)\n• gps_x, gps_y (DECIMAL 8,2)\n• current_container (VARCHAR 11)\n• last_block (CHAR 1)\n• fuel_percent (INT, level solar)\n• hours_today (DECIMAL 4,1 jam kerja)\n• last_updated (TIMESTAMP)"),
        ("TABEL: trucks & trains", "Entitas Moda Transportasi", C_INDIGO,
         "TABEL: trucks\n• id (INT, PK, Auto Increment)\n• license_plate (VARCHAR 15, Unique)\n• rfid_tag (RFID e-Pass supir)\n• driver_name (Nama supir truk)\n• company (Vendor trucking)\n• container_number (Box yang dibawa)\n• status (in_yard / gate_out)\n• gate_in_time, gate_out_time\n\nTABEL: trains\n• id (INT, PK, Auto Increment)\n• train_code (KA-LOG-JKT-SMG)\n• total_wagons, loaded_wagons\n• arrival_time, departure_time"),
        ("TABEL: yard_events", "Entitas Audit Trail & Billing", C_PURPLE,
         "• id (BIGINT, PK, Auto Increment)\n• event_type (GATE_IN / RELOCATION / RAIL_UNLOAD / REEFER_PLUG)\n• container_number (Peti kemas terkait)\n• equipment_id (Alat berat yang bekerja)\n• from_block, from_bay, from_row, from_tier\n• to_block, to_bay, to_row, to_tier\n• operator_name (Penanggung jawab)\n• billable_amount (Nominal rupiah jasa)\n• notes (Keterangan audit kronologis)\n• created_at (TIMESTAMP waktu pasti)\n\n*Sumber data sinkronisasi ke ERP Odoo")
    ]

    for i, (title, sub, col, body) in enumerate(entities):
        left_pos = Inches(0.8 + i * 2.95)
        create_card(s10, left_pos, Inches(1.8), e_w, e_h)
        tb = s10.shapes.add_textbox(left_pos + Inches(0.15), Inches(2.0), e_w - Inches(0.3), e_h - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(10)
        p.font.bold = True
        p.font.color.rgb = col
        p = tf.add_paragraph()
        p.text = sub
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(3)
        p = tf.add_paragraph()
        p.text = body
        p.font.name = "Consolas"
        p.font.size = Pt(7.8)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(5)

    # =========================================================================
    # SLIDE 11: REST API GATEWAY & PAYLOAD JSON RIIL
    # =========================================================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "Arsitektur REST API Gateway & Struktur Payload JSON Riil")
    add_footer(s11, 11)

    box_w = Inches(5.7)
    box_h = Inches(4.8)

    # Left: Move Container API
    create_card(s11, Inches(0.8), Inches(1.8), box_w, box_h, bg_color=C_CODE_BG)
    tb = s11.shapes.add_textbox(Inches(1.0), Inches(2.0), box_w - Inches(0.4), box_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "API 1: RELOKASI YARD OLEH REACH STACKER (Lo-Lo)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_AMBER

    json_move = """POST /api/simulator_action.php?action=move_container
Content-Type: application/json

{
  "container_number": "MSKU9182374",
  "equipment_id": "RS-02",
  "to_block": "B",
  "to_bay": "04",
  "to_row": "02",
  "to_tier": "03"
}

// HTTP 200 OK Response:
{
  "success": true,
  "message": "Kontainer MSKU9182374 berhasil dipindahkan",
  "event_id": 142,
  "billing": {
    "charge_name": "Jasa Lo-Lo / Relokasi Yard",
    "amount": 250000,
    "currency": "IDR"
  }
}"""
    p = tf.add_paragraph()
    p.text = json_move
    p.font.name = "Consolas"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(6)

    # Right: Gate-In API
    create_card(s11, Inches(6.8), Inches(1.8), box_w, box_h, bg_color=C_CODE_BG)
    tb = s11.shapes.add_textbox(Inches(7.0), Inches(2.0), box_w - Inches(0.4), box_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "API 2: INGESTION GATE-IN & VERIFIKASI TIMBANGAN VGM"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD

    json_gatein = """POST /api/simulator_action.php?action=gate_in
Content-Type: application/json

{
  "license_plate": "B 9481 UEK",
  "driver_name": "Sholehudin",
  "company": "PT Samudera Logistik Prima",
  "measured_gross_kg": 30500,
  "truck_tare_kg": 11200
}

// HTTP 200 OK Response:
{
  "success": true,
  "truck": {
    "license_plate": "B 9481 UEK",
    "vgm_net": 19300,
    "vgm_status": "SOLAS VERIFIED (PASS)"
  },
  "billing": {
    "charge_name": "Jasa Jembatan Timbang VGM SOLAS",
    "amount": 50000
  }
}"""
    p = tf.add_paragraph()
    p.text = json_gatein
    p.font.name = "Consolas"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 12: DIGITAL TWIN & SIMULATOR 3D (THREE.JS WEBGL ENGINE)
    # =========================================================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "Digital Twin & Simulasi 3D Virtual Terminal (Three.js WebGL Engine)")
    add_footer(s12, 12)

    c12_w = Inches(3.7)
    c12_h = Inches(4.8)

    # Card 1: 3D Graphics Canvas
    create_card(s12, Inches(0.8), Inches(1.8), c12_w, c12_h)
    tb = s12.shapes.add_textbox(Inches(1.0), Inches(2.0), c12_w - Inches(0.4), c12_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "GRAFIKA 3D WEBGL INTERAKTIF"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Visualisasi Realistis 35 Ha"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Three.js WebGL Engine v2.0:\n  Merender pemodelan spasial terminal 35 Hektar secara real-time pada 60 FPS langsung di browser web tanpa plugin tambahan.\n\n• Pewarnaan Kontainer Standar ISO:\n  - Biru: Peti kemas Dry Kargo umum (40HC / 20ft).\n  - Putih/Cyan: Reefer Cold Chain.\n  - Merah/Kuning: DG (Dangerous Goods).\n  - Abu-abu: Peti kemas kosong (Empty Depot).\n\n• Animasi Fisik Halus (Tween.js):\n  Menganimasikan gerakan boom crane, perputaran spreader twistlock, dan laju truk di jalan terminal."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: 5 Cinematic Camera Angles
    create_card(s12, Inches(4.8), Inches(1.8), c12_w, c12_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s12.shapes.add_textbox(Inches(5.0), Inches(2.0), c12_w - Inches(0.4), c12_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "PRESET KAMERA SINEMATIK"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "5 Sudut Pandang Operator"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• 1. Drone 35 Ha Overview: Sudut pandang makro manajerial seluruh fasilitas terminal.\n• 2. Gate & VGM Timbangan: Sudut pandang gerbang masuk pemeriksaan truk trailer.\n• 3. Blok Yard Penumpukan: Sudut pandang tumpukan kontainer Bay-Row-Tier Blok A-E.\n• 4. Rail Siding Intermodal: Sudut pandang sepur simpan alih muat kereta api logistik.\n• 5. Kabin RS (POV Operator): Sudut pandang pengemudi Reach Stacker dari dalam kabin crane!"
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: Raycaster Inspector & Lighting
    create_card(s12, Inches(8.8), Inches(1.8), c12_w, c12_h)
    tb = s12.shapes.add_textbox(Inches(9.0), Inches(2.0), c12_w - Inches(0.4), c12_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "INSPEKTOR INTERAKTIF"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_PURPLE
    p = tf.add_paragraph()
    p.text = "3D Raycasting & Lingkungan"
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• 3D Raycasting Inspector:\n  Setiap peti kemas di layar 3D dapat diklik oleh pengguna. Seketika muncul HUD modal berisi metadata riil: Nomor ISO 6346, Tag RFID, Berat VGM, Pemilik Kargo, dan Posisi Slot.\n\n• Dynamic Time-of-Day Lighting:\n  Dilengkapi kontrol simulasi pencahayaan Siang (Day), Senja (Sunset), dan Malam (Night) untuk mensimulasikan operasional pelabuhan kering 24 jam nonstop dengan lampu sorot tower high-mast."
    p.font.size = Pt(8.8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 13: QUALITY ASSURANCE (QA), POSTMAN TESTING & GS1 STANDARDS
    # =========================================================================
    s13 = prs.slides.add_slide(blank_layout)
    add_header(s13, "Quality Assurance (QA), Postman Testing Suite & Standar Mutu GS1")
    add_footer(s13, 13)

    c13_w = Inches(5.7)
    c13_h = Inches(4.8)

    # Left: Postman Automated Testing Suite
    create_card(s13, Inches(0.8), Inches(1.8), c13_w, c13_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s13.shapes.add_textbox(Inches(1.0), Inches(2.0), c13_w - Inches(0.4), c13_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "POSTMAN AUTOMATED TESTING SUITE"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Uji Penjaminan Mutu Software (QA)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Automated Assertion Scripts:\n  1. Status Code Check: pm.response.to.have.status(200);\n  2. Latency Threshold: pm.expect(pm.response.responseTime).to.be.below(150);\n  3. JSON Schema Validation: Memvalidasi keberadaan field wajib (container_number, vgm_status, billing_id).\n\n• Skenario Uji Stres & Kegagalan (Edge Cases):\n  - Uji penolakan kontainer overweight (>34 ton).\n  - Uji tabrakan slot (mencegah penumpukan di koordinat terisi).\n  - Uji toleransi koneksi basis data offline (graceful fallback ke mode demo)."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Right: Global Standards Compliance (GS1, SSCC, ISO)
    create_card(s13, Inches(6.8), Inches(1.8), c13_w, c13_h)
    tb = s13.shapes.add_textbox(Inches(7.0), Inches(2.0), c13_w - Inches(0.4), c13_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KEPATUHAN STANDAR MUTU GLOBAL"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Standardisasi AIDC, GS1 & ISO Maritim"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(3)
    p = tf.add_paragraph()
    p.text = "• Standar Identifikasi Peti Kemas ISO 6346:\n  Verifikasi otomatis Check Digit algoritma Modulo 11 (4 huruf kode pemilik + 6 angka seri + 1 angka pemeriksa).\n\n• Standar Barcode GS1-128 & SSCC-18:\n  Mendukung parsing Application Identifier (AI):\n  - (00) Serial Shipping Container Code (SSCC-18) untuk tracking unit kargo.\n  - (10) Nomor Batch / Lot pengiriman.\n  - (17) Tanggal Kedaluwarsa komoditas rantai dingin.\n\n• Standar RFID Maritim:\n  Struktur data EPCglobal Gen2 UHF (ISO 18000-6C) untuk identifikasi otomatis tanpa sentuh saat truk melintasi gerbang."
    p.font.size = Pt(9.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 14: BENCHMARKING SISTEM: CIDP YMS VS NAVIS N4 VS SOLVO.TOS
    # =========================================================================
    s14 = prs.slides.add_slide(blank_layout)
    add_header(s14, "Benchmarking Industri: Perbandingan Solusi CIDP YMS vs Navis N4 vs Solvo.TOS")
    add_footer(s14, 14)

    rows = 7
    cols = 4
    t14_shape = s14.shapes.add_table(rows, cols, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    t14 = t14_shape.table
    t14.columns[0].width = Inches(2.6)
    t14.columns[1].width = Inches(3.2)
    t14.columns[2].width = Inches(3.0)
    t14.columns[3].width = Inches(2.9)

    headers14 = ["Parameter Evaluasi Sistem", "CIDP YMS (Inovasi Konsultan)", "Navis N4 (Standard Port Global)", "Solvo.TOS (Intermodal Rusia/Eropa)"]
    for j, h in enumerate(headers14):
        cell = t14.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(9.5)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    bench_matrix = [
        ("Arsitektur & Penerapan", "Decoupled Web-Based, Zero Client Install, Modular", "On-Premise / Hybrid Heavy Enterprise Client", "Client-Server On-Premise Monolitik"),
        ("Kesiapan Simulasi IoT", "Tersedia Virtual Sandbox & Postman Ready", "Memerlukan lisensi emulator mahal tambahan", "Simulasi terbatas pada lingkungan testing tertutup"),
        ("Dukungan Antarmoda Rel", "Native Frauscher Axle Counter & SAKA KAI", "Modul add-on rail berbiaya lisensi tinggi", "Native rail support untuk standar rel CIS"),
        ("Integrasi Pabean Indonesia", "Terhubung langsung spesifikasi CEISA 4.0 DJBC", "Memerlukan bridging EDI kustomisasi khusus", "Memerlukan modul kustomisasi pihak ketiga"),
        ("Integrasi Finansial ERP", "Event-Driven Webhook langsung ke Odoo ERP", "Integrasi kompleks ke SAP via middleware IBM MQ", "Konektor kustom ke sistem akuntansi lokal"),
        ("Biaya Kepemilikan (TCO)", "Sangat Efisien, Bebas Royalti Lisensi Asing", "Sangat Tinggi ($500k - $2M+ per terminal)", "Tinggi ($250k - $800k per terminal)")
    ]

    for i, row in enumerate(bench_matrix):
        for j, val in enumerate(row):
            cell = t14.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_LIGHT_BG if i % 2 == 0 else C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(8.5)
                p.font.color.rgb = C_TEXT

    # =========================================================================
    # SLIDE 15: KESIMPULAN: TRANSFORMASI DATA MENJADI EFISIENSI & PROFITABILITAS
    # =========================================================================
    s15 = prs.slides.add_slide(blank_layout)
    add_header(s15, "Kesimpulan: Transformasi Data Menjadi Efisiensi, Transparansi, & Profitabilitas")
    add_footer(s15, 15)

    c15_w = Inches(5.7)
    c15_h = Inches(2.3)

    # Card 1: Operational Speed
    create_card(s15, Inches(0.8), Inches(1.8), c15_w, c15_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s15.shapes.add_textbox(Inches(1.0), Inches(1.9), c15_w - Inches(0.4), c15_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "1. KECEPATAN OPERASIONAL & ELIMINASI KEMACETAN"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "• Otomasi gerbang 28 detik per truk memangkas antrean di jalan akses logistik.\n• Algoritma auto-stacking meminimalkan pergerakan sia-sia (unproductive moves) armada Reach Stacker.\n• Dwell time berhasil ditekan hingga rata-rata 2.1 hari (jauh di bawah batas 3 hari)."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Card 2: Absolute Data Integrity
    create_card(s15, Inches(6.8), Inches(1.8), c15_w, c15_h)
    tb = s15.shapes.add_textbox(Inches(7.0), Inches(1.9), c15_w - Inches(0.4), c15_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "2. INTEGRITAS DATA ABSOLUT TANPA HUMAN ERROR"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "• 100% data transaksi operasional ditangkap otomatis oleh sensor dan divalidasi via API Gateway.\n• Menghilangkan pencatatan kertas manual (paperless terminal) dan kesalahan pengetikan nomor kontainer.\n• Memenuhi kepatuhan SOLAS VGM dan regulasi kepabeanan CEISA 4.0 secara presisi."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Card 3: Zero Revenue Leakage
    create_card(s15, Inches(0.8), Inches(4.3), c15_w, c15_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s15.shapes.add_textbox(Inches(1.0), Inches(4.4), c15_w - Inches(0.4), c15_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "3. AKUNTABILITAS FINANSIAL PENUH (ZERO LEAKAGE)"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "• Setiap tindakan fisik (Lo-Lo, timbang, steker listrik, penumpukan) otomatis menjadi entri tagihan ERP Odoo.\n• Mencegah kebocoran pendapatan terminal (zero unbilled services).\n• Memastikan pengembalian investasi (ROI) fasilitas 35 Ha tercapai sesuai target studi kelayakan."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Card 4: Multi-Stakeholder Intermodal Transparency
    create_card(s15, Inches(6.8), Inches(4.3), c15_w, c15_h)
    tb = s15.shapes.add_textbox(Inches(7.0), Inches(4.4), c15_w - Inches(0.4), c15_h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "4. VISIBILITAS KOLABORATIF MULTI-STAKEHOLDER"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p = tf.add_paragraph()
    p.text = "• Shipper / Pemilik Barang: Melacak kontainer dan mengunduh e-Invoice secara transparan.\n• Bea Cukai: Memantau kontainer jalur merah dan integritas E-Seal secara real-time.\n• PT KAI Logistik: Sinkronisasi jadwal kedatangan kereta dan rekonsiliasi kapasitas angkutan gandar rel."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Save presentation
    output_path = os.path.join(os.path.dirname(__file__), "PRESENTASI_SOFTWARE_CIDP.pptx")
    prs.save(output_path)
    print(f"[SUCCESS] Presentation created successfully at: {output_path}")

if __name__ == "__main__":
    create_software_presentation()
