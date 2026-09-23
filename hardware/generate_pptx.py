import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_presentation():
    prs = Presentation()
    # 16:9 Widescreen (13.333 x 7.5 Inches)
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    TOTAL_SLIDES = 15

    # Color Palette Standard
    C_NAVY = RGBColor(0, 47, 94)        # #002f5e - Navy ITL / Pelindo
    C_BLUE = RGBColor(1, 112, 185)      # #0170b9 - Blue Primary
    C_LIGHT_BG = RGBColor(248, 250, 252)# #f8fafc - Slate 50
    C_CARD_BG = RGBColor(255, 255, 255) # #ffffff
    C_BORDER = RGBColor(226, 232, 240)  # #e2e8f0
    C_DARK = RGBColor(15, 23, 42)       # #0f172a
    C_TEXT = RGBColor(30, 41, 59)       # #1e293b
    C_MUTED = RGBColor(100, 116, 139)   # #64748b
    C_EMERALD = RGBColor(16, 185, 129)  # #10b981
    C_AMBER = RGBColor(245, 158, 11)    # #f59e0b
    C_WHITE = RGBColor(255, 255, 255)
    C_CODE_BG = RGBColor(15, 23, 42)
    C_GREEN_BG = RGBColor(236, 253, 245)
    C_BLUE_BG = RGBColor(239, 246, 255)

    def add_header(slide, title_text, category_text="CONCLUSION INTERMODAL DRY PORT (CIDP) 35 HA"):
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.7), Inches(1.1))
        tf = header_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        
        p0 = tf.paragraphs[0]
        p0.text = category_text.upper()
        p0.font.size = Pt(10)
        p0.font.bold = True
        p0.font.color.rgb = C_BLUE
        
        p1 = tf.add_paragraph()
        p1.text = title_text
        p1.font.size = Pt(20)
        p1.font.bold = True
        p1.font.color.rgb = C_NAVY
        p1.space_before = Pt(4)

    def add_footer(slide, current_slide):
        footer_box = slide.shapes.add_textbox(Inches(0.8), Inches(7.0), Inches(11.7), Inches(0.35))
        tf = footer_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = f"Armansyah Muchtarrom — Hardware & Infrastructure Specialist | CIDP 35 Ha YMS & ERP Odoo                    Slide {current_slide} of {TOTAL_SLIDES}"
        p.font.size = Pt(9)
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

    bar = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.2), Inches(0.15), Inches(4.5))
    bar.fill.solid()
    bar.fill.fore_color.rgb = C_BLUE
    bar.line.fill.background()

    tbox1 = s1.shapes.add_textbox(Inches(1.2), Inches(1.2), Inches(11.0), Inches(4.8))
    tf1 = tbox1.text_frame
    tf1.word_wrap = True
    
    p = tf1.paragraphs[0]
    p.text = "PROYEK YARD MANAGEMENT SYSTEM & LOGISTIK INTERMODAL"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = RGBColor(147, 197, 253)

    p = tf1.add_paragraph()
    p.text = "Arsitektur Perangkat Keras, Otomasi Telemetri Lapangan, & Integrasi Dua Arah YMS–ERP Odoo"
    p.font.size = Pt(28)
    p.font.bold = True
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(12)

    p = tf1.add_paragraph()
    p.text = "Pemetaan 26 Hardware • 11 Perangkat Simulasi Postman JSON • 15 Perangkat Blueprint Fasilitas 35 Ha"
    p.font.size = Pt(14)
    p.font.color.rgb = RGBColor(226, 232, 240)
    p.space_before = Pt(14)

    pbox = s1.shapes.add_textbox(Inches(1.2), Inches(5.2), Inches(11.0), Inches(1.5))
    ptf = pbox.text_frame
    ptf.word_wrap = True
    
    p = ptf.paragraphs[0]
    p.text = "Presenter: Armansyah Muchtarrom  |  Role: Hardware & Infrastructure Specialist"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_WHITE

    p = ptf.add_paragraph()
    p.text = "Mata Kuliah: Teknologi & Perangkat Lunak Logistik  •  Dosen Pengampu: Dr. Tigor Franky, S.T., M.T."
    p.font.size = Pt(11)
    p.font.color.rgb = RGBColor(203, 213, 225)
    p.space_before = Pt(4)

    p = ptf.add_paragraph()
    p.text = "Institut Transportasi dan Logistik (ITL) Trisakti  •  Conclusion Supply Chain Consultant  •  September 2026"
    p.font.size = Pt(10)
    p.font.color.rgb = RGBColor(148, 163, 184)
    p.space_before = Pt(4)

    # =========================================================================
    # SLIDE 2: FILOSOFI ARSITEKTUR & MENGAPA POSTMAN / JSON
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "Filosofi Arsitektur: Mengapa Validasi Menggunakan Postman & JSON?")
    add_footer(s2, 2)

    card_w = Inches(3.7)
    card_h = Inches(4.8)
    
    # Card 1: Layer 1 Sensor Fisik
    create_card(s2, Inches(0.8), Inches(1.8), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(1.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LAYER 1: SENSOR FISIK"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Menangkap Pemicu Lapangan"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Induction loop mendeteksi massa truk.\n• Photocell memicu kamera OCR portal.\n• Load cell 80t membaca bobot kotor.\n• RTK GNSS membaca satelit < 2 cm.\n• Twistlock spreader mendeteksi jepitan box.\n• Current Transformer membaca ampere reefer."
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 2: Layer 2 Edge PC & YMS
    create_card(s2, Inches(4.8), Inches(1.8), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s2.shapes.add_textbox(Inches(5.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LAYER 2: EDGE AI PC & YMS"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Penerjemah & Pengendali Lokal"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Advantech ARK-3532 Fanless memproses data serial/LAN secara lokal (28 detik).\n• Mengonversi sinyal fisik menjadi REST API JSON.\n• Mengendalikan relay buka barrier gate.\n• Mengalokasikan 3D slot Bay-Row-Tier di YMS.\n• Mengapa Postman? Karena data contract JSON di Postman 100% identik dengan output Edge PC!"
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 3: Layer 3 ERP Odoo
    create_card(s2, Inches(8.8), Inches(1.8), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(9.0), Inches(2.0), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "LAYER 3: ERP ODOO"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Rekonsiliasi Finansial & Aset"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Menerima Webhook Billable Charge Events dari YMS secara otomatis.\n• Odoo Invoicing: Menerbitkan draf faktur VGM (Rp 50rb), Lo-Lo (Rp 250rb), Reefer (Rp 250rb).\n• Odoo Maintenance: Melacak jam jalan alat berat.\n• Odoo Inventory: Mengunci izin rilis DO jika segel Bea Cukai belum aman.\n• Hasil: Zero Revenue Leakage!"
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # =========================================================================
    # SLIDE 3: STRATEGI KLASIFIKASI: 11 SIMULASI VS 15 BLUEPRINT
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "Strategi Klasifikasi: 11 Perangkat Simulasi vs 15 Perangkat Blueprint")
    add_footer(s3, 3)

    col_w = Inches(5.7)
    col_h = Inches(4.8)

    # Column Left: 11 Simulasi
    create_card(s3, Inches(0.8), Inches(1.8), col_w, col_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s3.shapes.add_textbox(Inches(1.0), Inches(2.0), col_w - Inches(0.4), col_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "11 PERANGKAT SIMULASI (LIVE SOFTWARE & POSTMAN)"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD

    p = tf.add_paragraph()
    p.text = "Perangkat Aktif Penghasil Aliran Data Telemetri Dinamis"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)

    items_live = [
        "1. Kamera ANPR Gate (Hikvision DS-TCG406-E) — Plat Truk",
        "2. Kamera OCR Portal (Hikvision iDS-TCV300) — Nomor ISO 6346",
        "6. Jembatan Timbang 80t (Fangda Scale) — Validasi SOLAS VGM",
        "7. RFID Reader UHF 20m — Identifikasi e-Pass Supir",
        "10. LED Display Gerbang — Panduan Blok Yard Sopir",
        "18. Industrial Edge AI PC (Advantech ARK-3532) — Gateway JSON",
        "21. DGPS / RTK GNSS (<2 cm) — Auto Bay-Row-Tier",
        "22. Spreader Twistlock & Load Cell — Konfirmasi Lift-Off / Lo-Lo",
        "23. Smart Reefer Socket 380V — Monitoring Daya kWh & Trip",
        "25. Rail Axle Counter (Frauscher) — Hitung Gandar Kereta Api",
        "26. Electronic Cargo Smart Seal (Jointech) — E-Seal Anti-Tamper"
    ]
    p = tf.add_paragraph()
    p.text = "\n".join(items_live)
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(8)

    # Column Right: 15 Blueprint
    create_card(s3, Inches(6.8), Inches(1.8), col_w, col_h, bg_color=C_CARD_BG, border_color=C_BORDER)
    tb = s3.shapes.add_textbox(Inches(7.0), Inches(2.0), col_w - Inches(0.4), col_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "15 PERANGKAT BLUEPRINT (INFRASTRUKTUR FISIK 35 HA)"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_BLUE

    p = tf.add_paragraph()
    p.text = "Utilitas Jaringan, Kelistrikan, Daya Cadangan & Keselamatan"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)

    items_bp = [
        "3. CCTV Yard 4 MP (Hikvision) — 32 Titik Tiang Penumpukan",
        "4. CCTV Yard 8 MP 4K ColorVu — 16 Titik Pagar Perimeter",
        "5. CCTV PTZ 360° TandemVu — 8 Titik Menara Pengawas Pusat",
        "8. RFID Tag Pasif UHF ISO — 500 Tag Ditempel di Kaca Truk",
        "9. Automatic Barrier Gate — 4 Unit Palang Gerbang Masuk/Keluar",
        "11. Industrial Switch PoE+ IP40 — 12 Junction Box Lapangan",
        "12. Access Point WiFi 7 Outdoor (Ubiquiti) — 18 Tiang Yard & Rel",
        "13. Server NVR 64-Ch & Database (Hikvision) — Datacenter",
        "14. Online UPS 5000VA Rackmount (APC) — Daya Darurat 4.5 kW",
        "15. Barcode / QR Scanner Meja (Zebra) — 8 Unit Loket Pos",
        "16. Thermal Camera Reefer Yard — 4 Unit Pencegah Kebakaran",
        "17. GPS Tracker Truk 4G (Teltonika) — 40 Unit Armada Jalan Raya",
        "19. Gantry Container X-Ray 6 MeV (Nuctech) — Rp 12,5 Miliar Bea Cukai",
        "20. Vehicle Mounted Terminal (Zebra VC8300) — 6 Kabin Alat Berat",
        "24. Rugged Mobile PDA (Zebra TC57x) — 10 Unit Tallyman Lapangan"
    ]
    p = tf.add_paragraph()
    p.text = "\n".join(items_bp)
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_MUTED
    p.space_before = Pt(8)

    # =========================================================================
    # SLIDE 4: KLASTER 1: OTOMASI GERBANG & RANTAI TRIGGER
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "Klaster 1: Otomasi Gerbang & Rantai Trigger (28 Detik Selesai)")
    add_footer(s4, 4)

    steps = [
        ("TAHAP 1: ANPR", "Induction Loop", "Massa logam truk melindas loop -> Kamera ANPR capture plat B 9812 UIK via TCP/IP."),
        ("TAHAP 2: OCR ISO", "Portal Photocell", "Peti kemas memotong sinar -> Portal OCR membaca nomor TCKU 8291042 (40HC)."),
        ("TAHAP 3: TIMBANGAN", "Load Cell 80t", "Truk berhenti di jembatan timbang -> Indikator kirim berat 32.450 kg via RS-232."),
        ("TAHAP 4: EDGE AI PC", "Advantech ARK-3532", "Validasi lokal tiket & berat SOLAS VGM -> picu relay DC 24V buka palang & kirim JSON."),
        ("TAHAP 5: PALANG & LED", "Barrier & LED", "LED tampilkan 'MENUJU BLOK B04' -> Palang terbuka -> Webhook Odoo terbit Rp 50.000.")
    ]

    sw = Inches(2.25)
    sh = Inches(4.8)
    for i, (stitle, ssub, sdesc) in enumerate(steps):
        sx = Inches(0.8 + i * 2.4)
        create_card(s4, sx, Inches(1.8), sw, sh, bg_color=C_CARD_BG, border_color=C_BORDER)
        tb = s4.shapes.add_textbox(sx + Inches(0.15), Inches(2.0), sw - Inches(0.3), sh - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = stitle
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = C_BLUE

        p = tf.add_paragraph()
        p.text = ssub
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(4)

        p = tf.add_paragraph()
        p.text = sdesc
        p.font.size = Pt(10)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(10)

    # =========================================================================
    # SLIDE 5: KLASTER 2: TELEMETRI ALAT BERAT & AUTO BAY-ROW-TIER
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "Klaster 2: Telemetri Alat Berat & Penumpukan 3D Presisi Tinggi")
    add_footer(s5, 5)

    c_w = Inches(3.7)
    c_h = Inches(4.8)

    # Card 1: Spreader Twistlock
    create_card(s5, Inches(0.8), Inches(1.8), c_w, c_h)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "SPREADER TWISTLOCK KIT (#22)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Deteksi Penguncian Fisik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: Bromma SmartSpreader Kit\n• Pemicu: Pin hidrolik berputar 90° dalam corner casting peti kemas.\n• Sensor: Proximity sensor mendeteksi logam pin (LOCKED / UNLOCKED).\n• Load Cell: Mengukur tonase angkat riil saat boom bergerak.\n• Aksi Odoo: Transisi ke UNLOCKED memicu tagihan Jasa Lift-Off Rp 250.000!"
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 2: DGPS RTK
    create_card(s5, Inches(4.8), Inches(1.8), c_w, c_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(5.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "DGPS / RTK GNSS RECEIVER (#21)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Akurasi Sentimeter (< 2 cm)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: CHCNAV CGI-610 Dual-Antenna\n• Pemicu: Sinyal satelit + koreksi diferensial base station lokal + IMU boom.\n• Drop Trigger: Saat twistlock terbuka, koordinat spatio-temporal (X,Y,Z) dikunci detik itu juga.\n• YMS: Mengonversi koordinat ke format Block B, Bay 04, Row 02, Tier 03.\n• Meniadakan kesalahan salah letak kontainer!"
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 3: VMT & PDA
    create_card(s5, Inches(8.8), Inches(1.8), c_w, c_h)
    tb = s5.shapes.add_textbox(Inches(9.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "VMT KABIN & MOBILE PDA (#20 & #24)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_MUTED
    p = tf.add_paragraph()
    p.text = "Komputer Lapangan (Blueprint)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: Zebra VC8300 (VMT) & Zebra TC57x (PDA)\n• Layar ultra-bright 8 inci & scanner 2D tahan banting IP66/IP68.\n• Menampilkan job dispatching antrean pemindahan kontainer ke operator crane.\n• PDA digunakan tallyman untuk inspeksi fisik bodi kontainer (EIR - Equipment Interchange Receipt)."
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # =========================================================================
    # SLIDE 6: KLASTER 3, 4, 5: COLD CHAIN, REL KA, & PABEAN
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "Klaster 3, 4, & 5: Cold Chain Reefer, Rel Kereta Api, & Pabean")
    add_footer(s6, 6)

    # Card 1: Smart Reefer
    create_card(s6, Inches(0.8), Inches(1.8), c_w, c_h)
    tb = s6.shapes.add_textbox(Inches(1.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "SMART REEFER SOCKET (#23)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Cold Chain 300 Titik"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: Marechal 380V/32A Decontactor\n• Pemicu: Steker dicolok -> Trafo arus (CT) mendeteksi arus start > 5A.\n• Proteksi: Jika arus drop mendadak ke 0A (kompresor mati), alarm trip menyala dalam 3 detik.\n• Aksi Odoo: Setiap shift (8 jam), terbit tagihan Jasa Steker Reefer Rp 250.000 / shift."
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 2: Axle Counter
    create_card(s6, Inches(4.8), Inches(1.8), c_w, c_h)
    tb = s6.shapes.add_textbox(Inches(5.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "RAIL AXLE COUNTER (#25)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Intermodal Rail Siding"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: Frauscher RSR123 (SIL 4, IP68)\n• Pemicu: Pelek roda baja KA memotong medan induktif di leher rel sepur simpan.\n• Logika: Mencacah as roda (120 as = 30 gerbong datar = 60 TEUs).\n• YMS: Ubah status rel menjadi OCCUPIED.\n• Aksi Odoo: Validasi sewa rel (Track Access Charge) ke PT KAI Logistik."
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # Card 3: E-Seal
    create_card(s6, Inches(8.8), Inches(1.8), c_w, c_h)
    tb = s6.shapes.add_textbox(Inches(9.0), Inches(2.0), c_w - Inches(0.4), c_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "SMART E-SEAL GPS (#26)"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Integritas Bea Cukai"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(4)
    p = tf.add_paragraph()
    p.text = "• Model: Jointech JT701 GPS Smart Lock\n• Pemicu: Kawat baja dikunci di Priok -> GPS aktifkan status SEAL_ARMED.\n• Tamper Alert: Jika kawat dipotong paksa di jalan, alarm sabotase dikirim via 4G.\n• Aksi Odoo: Wajib berstatus SPPB Cleared sebelum modul Odoo Inventory mengizinkan rilis Delivery Order (DO)."
    p.font.size = Pt(10.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(10)

    # =========================================================================
    # SLIDE 7: MATRIKS INTEGRASI DATA LENGKAP
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "Matriks Integrasi Data: Hardware ➔ YMS ➔ ERP Odoo")
    add_footer(s7, 7)

    rows = 11
    cols = 6
    table_shape = s7.shapes.add_table(rows, cols, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    t = table_shape.table
    t.columns[0].width = Inches(2.3)
    t.columns[1].width = Inches(1.4)
    t.columns[2].width = Inches(1.8)
    t.columns[3].width = Inches(2.3)
    t.columns[4].width = Inches(2.0)
    t.columns[5].width = Inches(1.9)

    headers = ["Hardware", "Kategori", "Protokol", "Aksi di YMS", "Modul ERP Odoo", "Dampak Finansial"]
    for j, h in enumerate(headers):
        cell = t.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(9.5)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    matrix_data = [
        ("Kamera ANPR (#1)", "Simulasi", "TCP/IP HTTP Post", "Validasi plat truk di DB", "Odoo Fleet", "Rekonsiliasi Armada"),
        ("Kamera OCR ISO (#2)", "Simulasi", "Ethernet Socket", "Identifikasi nomor box 40HC", "Odoo Sales", "Penetapan Tarif 40ft"),
        ("Timbangan 80t (#6)", "Simulasi", "Serial RS-232", "Validasi SOLAS VGM (<34t)", "Odoo Invoicing", "Tagihan Timbang Rp 50rb"),
        ("RFID Reader (#7)", "Simulasi", "Wiegand / TCP", "Identifikasi e-Pass supir", "Odoo Accounting", "Potong Deposit Supir"),
        ("Edge AI PC (#18)", "Simulasi", "REST API JSON", "Gateway keputusan gerbang", "Odoo API Gateway", "Pengirim Webhook Event"),
        ("DGPS RTK (<2cm) (#21)", "Simulasi", "NMEA-0183 Serial", "Auto-update Bay-Row-Tier", "Odoo Maintenance", "Jam Jalan Alat Berat"),
        ("Twistlock Spreader (#22)", "Simulasi", "CANopen / Proximity", "Konfirmasi box dilepas di yard", "Odoo Invoicing", "Tagihan Lo-Lo Rp 250rb"),
        ("Smart Reefer (#23)", "Simulasi", "Modbus / LoRaWAN", "Monitoring suhu & trip listrik", "Odoo Invoicing", "Tagihan Steker Rp 250rb"),
        ("Axle Counter (#25)", "Simulasi", "Pulsa / Modbus TCP", "Hitung gerbong & okupansi rel", "Odoo Purchase", "Validasi Sewa Rel (TAC)"),
        ("15 Item Blueprint Lain", "Blueprint", "FO, NVR, UPS, CCTV", "Utilitas fisik & keamanan 35Ha", "Odoo Asset", "Depresiasi Aset Tetap")
    ]

    for i, row in enumerate(matrix_data):
        for j, val in enumerate(row):
            cell = t.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            if i % 2 == 0:
                cell.fill.fore_color.rgb = C_LIGHT_BG
            else:
                cell.fill.fore_color.rgb = C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(8.5)
                p.font.color.rgb = C_TEXT

    # =========================================================================
    # SLIDE 8: BUKTI UJI POSTMAN: STRUKTUR JSON RIIL
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "Bukti Uji Postman: Struktur Payload JSON Riil")
    add_footer(s8, 8)

    box_w = Inches(5.7)
    box_h = Inches(4.8)

    # Left Box: Gate-In JSON
    create_card(s8, Inches(0.8), Inches(1.8), box_w, box_h, bg_color=C_CODE_BG)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(2.0), box_w - Inches(0.4), box_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "POSTMAN REQUEST: TRIGGER GATE-IN & VGM SCALE"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER

    json_gate = """POST /api/simulator_action.php?action=gate_in
Content-Type: application/json

{
  "device_source": "ADVANTECH_EDGE_PC_GATE_01",
  "sensor_telemetry": {
    "anpr_license_plate": "B 9812 UIK",
    "ocr_container_number": "TCKU8291042",
    "ocr_iso_code": "42G1",
    "container_size": "40FT_HIGH_CUBE",
    "rfid_driver_tag": "RFID-TRK-9812",
    "measured_gross_kg": 32450,
    "truck_tare_kg": 11200,
    "vgm_net_kg": 21250,
    "vgm_solas_status": "VERIFIED_PASS"
  }
}"""
    p = tf.add_paragraph()
    p.text = json_gate
    p.font.name = "Consolas"
    p.font.size = Pt(9)
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(8)

    # Right Box: Odoo Webhook JSON
    create_card(s8, Inches(6.8), Inches(1.8), box_w, box_h, bg_color=C_CODE_BG)
    tb = s8.shapes.add_textbox(Inches(7.0), Inches(2.0), box_w - Inches(0.4), box_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "WEBHOOK OUTPUT: YMS ➔ ERP ODOO INVOICING"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD

    json_odoo = """POST https://erp.cidp.ac.id/api/v1/billing/charge-event
Authorization: Bearer SECRET_TOKEN_CIDP_2026

{
  "event_id": "EVT-20260923-0042",
  "customer_id": "CUST-SAMUDERA-01",
  "customer_name": "PT Samudera Logistik Prima",
  "container_number": "TCKU8291042",
  "billing_breakdown": [
    {
      "service_code": "SRV-VGM",
      "description": "Jembatan Timbang VGM 80t",
      "amount": 50000
    },
    {
      "service_code": "SRV-LIFT-OFF",
      "description": "Jasa Lift-Off Peti Kemas 40ft",
      "amount": 250000
    }
  ],
  "total_billable_idr": 300000,
  "status": "AUTO_INVOICE_GENERATED"
}"""
    p = tf.add_paragraph()
    p.text = json_odoo
    p.font.name = "Consolas"
    p.font.size = Pt(9)
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(8)

    # =========================================================================
    # SLIDE 9: REKAPITULASI CAPEX
    # =========================================================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "Rekapitulasi Rencana Anggaran Belanja Modal (CAPEX Rp 18,94 Miliar)")
    add_footer(s9, 9)

    table_shape9 = s9.shapes.add_table(8, 6, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.5))
    t9 = table_shape9.table
    t9.columns[0].width = Inches(0.8)
    t9.columns[1].width = Inches(3.8)
    t9.columns[2].width = Inches(1.6)
    t9.columns[3].width = Inches(1.8)
    t9.columns[4].width = Inches(2.2)
    t9.columns[5].width = Inches(1.5)

    headers9 = ["No", "Klaster Operasional", "Jumlah Item", "Total Unit", "Subtotal CAPEX", "Persentase"]
    for j, h in enumerate(headers9):
        cell = t9.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(10)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    capex_rows = [
        ("1", "Otomasi Gate & Kontrol Akses", "8 Jenis", "522 Unit / Pack", "Rp 1.460.500.000", "7.7%"),
        ("2", "Telemetri Lapangan Penumpukan & Crane", "4 Jenis", "28 Unit", "Rp 1.084.000.000", "5.7%"),
        ("3", "Cold Chain & Monitoring Reefer Yard", "2 Jenis", "304 Unit / Titik", "Rp 2.670.000.000", "14.1%"),
        ("4", "Infrastruktur Intermodal Rel KA", "3 Jenis", "62 Unit", "Rp 569.600.000", "3.0%"),
        ("5", "Keamanan Terminal & Bea Cukai (X-Ray)", "5 Jenis", "157 Unit / Sistem", "Rp 13.118.400.000", "69.3%"),
        ("6", "Core Datacenter, Edge PC & Jaringan", "4 Jenis", "26 Unit", "Rp 660.800.000", "3.5%"),
        ("TOTAL", "Seluruh Fasilitas CIDP 35 Hektar", "26 Item", "1.099 Unit", "Rp 18.943.300.000", "100.0%")
    ]

    for i, row in enumerate(capex_rows):
        is_total = (i == len(capex_rows) - 1)
        for j, val in enumerate(row):
            cell = t9.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            if is_total:
                cell.fill.fore_color.rgb = C_BLUE_BG
            elif i % 2 == 0:
                cell.fill.fore_color.rgb = C_LIGHT_BG
            else:
                cell.fill.fore_color.rgb = C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(9.5)
                p.font.bold = is_total
                p.font.color.rgb = C_NAVY if is_total else C_TEXT

    # =========================================================================
    # SLIDE 10: KESIMPULAN & NILAI TAMBAH BISNIS
    # =========================================================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "Kesimpulan: Efisiensi Operasional & Akuntabilitas Finansial")
    add_footer(s10, 10)

    qw = Inches(5.6)
    qh = Inches(2.2)

    quads = [
        ("AKURASI PENEMPATAN 100%", "Meniadakan Kesalahan Salah Letak Kontainer", "Kombinasi dual-antenna DGPS RTK (< 2 cm) dan proximity sensor twistlock memastikan koordinat 3D Bay-Row-Tier ter-update secara otomatis tanpa input radio manual.", Inches(0.8), Inches(1.8)),
        ("WAKTU TRANSAKSI GATE 28 DETIK", "Mengeliminasi Kemacetan Antrean Truk", "Otomasi paralel kamera ANPR, OCR portal, jembatan timbang 80t, dan pembaca e-Pass RFID UHF memangkas waktu proses gerbang hingga 37% dibanding sistem manual.", Inches(6.9), Inches(1.8)),
        ("ZERO REVENUE LEAKAGE", "Nol Kebocoran Pendapatan Jasa Terminal", "Setiap aktivitas fisik alat di lapangan seketika menjadi bukti transaksi sah yang menembakkan tagihan otomatis ke modul ERP Odoo Invoicing tanpa campur tangan staf admin.", Inches(0.8), Inches(4.3)),
        ("KESIAPAN ARSITEKTUR PLUG-AND-PLAY", "Teruji Sepenuhnya Menggunakan Postman JSON", "Pemisahan sistem 3-layer menjamin bahwa hardware fisik di lapangan kelak tinggal mengarahkan pengiriman data ke endpoint API yang sama tanpa mengubah kode software.", Inches(6.9), Inches(4.3))
    ]

    for qtitle, qsub, qdesc, qx, qy in quads:
        create_card(s10, qx, qy, qw, qh)
        tb = s10.shapes.add_textbox(qx + Inches(0.2), qy + Inches(0.15), qw - Inches(0.4), qh - Inches(0.3))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = qtitle
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        p = tf.add_paragraph()
        p.text = qsub
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(2)
        p = tf.add_paragraph()
        p.text = qdesc
        p.font.size = Pt(9.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(4)

    # =========================================================================
    # SLIDE 11: LAMPIRAN VISUAL HARDWARE SIMULASI (BAGIAN 1)
    # =========================================================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "Lampiran Visual: Perangkat Simulasi Live Software (Bagian 1)", "LAMPIRAN A: DOKUMENTASI RESMI PERANGKAT")
    add_footer(s11, 11)

    cards11 = [
        ("ITEM #18: INDUSTRIAL EDGE AI PC", "Advantech ARK-3532 Fanless", "Intel Core i7, Fanless -20°C s/d 60°C, 4x COM RS-232/422/485, Dual GbE PoE. Pengirim utama JSON ke YMS & Odoo.", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_18.png", Inches(0.8)),
        ("ITEM #21: DGPS / RTK GNSS RECEIVER", "CHCNAV CGI-610 Dual-Antenna", "Akurasi RTK < 2 cm, 50 Hz, IMU sensor, IP67. Terpasang pada boom Reach Stacker untuk auto Bay-Row-Tier.", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_21.png", Inches(4.8)),
        ("ITEM #22: SPREADER TWISTLOCK KIT", "Bromma SmartSpreader Kit", "4x Proximity sensor + 4x load cell, CANopen. Deteksi status LOCKED/UNLOCKED pemicu tagihan Lo-Lo Rp 250rb.", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_22.png", Inches(8.8))
    ]

    for ititle, isub, idesc, ipath, ix in cards11:
        create_card(s11, ix, Inches(1.8), Inches(3.7), Inches(4.8))
        if os.path.exists(ipath):
            try:
                s11.shapes.add_picture(ipath, ix + Inches(0.35), Inches(2.0), width=Inches(3.0), height=Inches(2.0))
            except Exception as e:
                pass
        tb = s11.shapes.add_textbox(ix + Inches(0.2), Inches(4.1), Inches(3.3), Inches(2.3))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = ititle
        p.font.size = Pt(10.5)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        p = tf.add_paragraph()
        p.text = isub
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(2)
        p = tf.add_paragraph()
        p.text = idesc
        p.font.size = Pt(9.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(4)

    # =========================================================================
    # SLIDE 12: LAMPIRAN VISUAL HARDWARE SIMULASI (BAGIAN 2)
    # =========================================================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "Lampiran Visual: Perangkat Simulasi Live Software (Bagian 2)", "LAMPIRAN A: DOKUMENTASI RESMI PERANGKAT")
    add_footer(s12, 12)

    cards12 = [
        ("ITEM #23: SMART REEFER SOCKET", "Marechal 380V/32A Receptacle", "Daya 380V/32A IP67, energy meter kWh digital, Modbus/LoRaWAN, trip alarm. Pemicu tagihan plugging Rp 250rb/shift.", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_23.png", Inches(0.8)),
        ("ITEM #25: RAIL AXLE COUNTER", "Frauscher RSR123 Wheel Sensor", "Sensor induktif pelek roda KA, SIL 4, IP68. Menghitung gerbong kontainer & okupansi sepur rel intermodal.", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_25.png", Inches(4.8)),
        ("ITEM #26: SMART E-SEAL GPS", "Jointech JT701 GPS Smart Lock", "Gembok elektronik GPS/GSM/RFID, sensor kabel anti-potong. Integritas pabean Priok-CIDP (syarat DO Odoo).", "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_26.png", Inches(8.8))
    ]

    for ititle, isub, idesc, ipath, ix in cards12:
        create_card(s12, ix, Inches(1.8), Inches(3.7), Inches(4.8))
        if os.path.exists(ipath):
            try:
                s12.shapes.add_picture(ipath, ix + Inches(0.35), Inches(2.0), width=Inches(3.0), height=Inches(2.0))
            except Exception as e:
                pass
        tb = s12.shapes.add_textbox(ix + Inches(0.2), Inches(4.1), Inches(3.3), Inches(2.3))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = ititle
        p.font.size = Pt(10.5)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        p = tf.add_paragraph()
        p.text = isub
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(2)
        p = tf.add_paragraph()
        p.text = idesc
        p.font.size = Pt(9.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(4)

    # =========================================================================
    # SLIDE 13: LAMPIRAN VISUAL HARDWARE BLUEPRINT & FASILITAS
    # =========================================================================
    s13 = prs.slides.add_slide(blank_layout)
    add_header(s13, "Lampiran Visual: Perangkat Blueprint Fasilitas Fisik", "LAMPIRAN B: DOKUMENTASI RESMI PERANGKAT")
    add_footer(s13, 13)

    create_card(s13, Inches(0.8), Inches(1.8), Inches(3.7), Inches(4.8))
    pda_path = "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/images/converted/item_24.png"
    if os.path.exists(pda_path):
        try:
            s13.shapes.add_picture(pda_path, Inches(1.15), Inches(2.0), width=Inches(3.0), height=Inches(2.0))
        except Exception as e:
            pass
    tb = s13.shapes.add_textbox(Inches(1.0), Inches(4.1), Inches(3.3), Inches(2.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ITEM #24: RUGGED MOBILE PDA"
    p.font.size = Pt(10.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Zebra TC57x / Honeywell CT60"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "Android Enterprise, 2D Barcode Imager, IP68 tahan air & jatuh 1.8m. Digunakan petugas tallyman untuk pembuatan bukti serah terima kontainer (EIR)."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    create_card(s13, Inches(4.8), Inches(1.8), Inches(7.7), Inches(4.8))
    tb = s13.shapes.add_textbox(Inches(5.0), Inches(2.0), Inches(7.3), Inches(4.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "REFERENSI KATALOG RESMI 14 PERANGKAT BLUEPRINT LAINNYA"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = C_NAVY

    bp_details = [
        "• Item #3: CCTV Yard 4 MP (Hikvision DS-2CD2046G2H-IU) — 32 Unit Tiang Tumpukan",
        "• Item #4: CCTV Yard 8 MP 4K ColorVu (Hikvision DS-2CD2T87G2H-LI) — 16 Unit Pagar Perimeter",
        "• Item #5: CCTV PTZ 360° TandemVu (Hikvision DS-2SE4C425MWG) — 8 Unit Menara Pengawas",
        "• Item #8: RFID Tag Pasif UHF ISO 18000-6C — 500 Tag Ditempel di Kaca Truk Mitra",
        "• Item #9: High-Speed Automatic Barrier Gate — 4 Unit Palang Gerbang Buka 1.2 Detik",
        "• Item #11: Industrial Switch PoE+ Managed IP40 — 12 Unit Junction Box Lapangan",
        "• Item #12: Outdoor Access Point WiFi 7 (Ubiquiti U7 Pro) — 18 Tiang Yard & Rel KA",
        "• Item #13: Server NVR 64-Ch & Database (Hikvision DS-9664NI-M16/R) — Datacenter",
        "• Item #14: Online UPS 5000VA Rackmount (APC SRT5KRMXLI) — Daya Darurat 4.5 kW",
        "• Item #15: Barcode / QR Scanner Meja (Zebra Symbol LS2208) — 8 Unit Loket Pos",
        "• Item #16: Thermal Camera Reefer Yard (Hikvision DS-2TD2636B) — 4 Unit Anti Kebakaran",
        "• Item #17: GPS Tracker Truk 4G (Teltonika TAT240) — 40 Unit Armada Koridor Priok-CIDP",
        "• Item #19: Gantry Container X-Ray Scanner 6 MeV (Nuctech MB1215DE) — Rp 12,5 Miliar",
        "• Item #20: Vehicle Mounted Terminal (Zebra VC8300 Rugged 8\") — 6 Kabin Crane"
    ]
    p = tf.add_paragraph()
    p.text = "\n".join(bp_details)
    p.font.size = Pt(9)
    p.font.color.rgb = C_MUTED
    p.space_before = Pt(8)

    # =========================================================================
    # SLIDE 14: MASTER BOM TABEL LENGKAP ITEM 1 - 13
    # =========================================================================
    s14 = prs.slides.add_slide(blank_layout)
    add_header(s14, "Tabel Lengkap 26 Hardware & Spesifikasi (Item #1 s/d #13)", "LAMPIRAN C: MASTER BILL OF MATERIALS (BOM)")
    add_footer(s14, 14)

    t_shape14 = s14.shapes.add_table(14, 6, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    t14 = t_shape14.table
    t14.columns[0].width = Inches(0.6)
    t14.columns[1].width = Inches(2.6)
    t14.columns[2].width = Inches(2.8)
    t14.columns[3].width = Inches(1.5)
    t14.columns[4].width = Inches(2.0)
    t14.columns[5].width = Inches(2.2)

    headers_bom = ["No", "Nama Hardware", "Model / Tipe Rekomendasi", "Klasifikasi", "Harga Satuan", "Subtotal CAPEX"]
    for j, h in enumerate(headers_bom):
        cell = t14.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(9)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    bom_part1 = [
        ("1", "Kamera ANPR Gate", "Hikvision DS-TCG406-E Series", "Simulasi", "Rp 16.000.000", "Rp 64.000.000 (4 unit)"),
        ("2", "Kamera OCR Kontainer", "Hikvision iDS-TCV300-A6I", "Simulasi", "Rp 55.000.000", "Rp 220.000.000 (4 unit)"),
        ("3", "CCTV Yard 4 MP", "Hikvision DS-2CD2046G2H-IU", "Blueprint", "Rp 3.500.000", "Rp 112.000.000 (32 unit)"),
        ("4", "CCTV Yard 8 MP 4K", "Hikvision DS-2CD2T87G2H-LI", "Blueprint", "Rp 6.500.000", "Rp 104.000.000 (16 unit)"),
        ("5", "CCTV PTZ 360°", "Hikvision DS-2SE4C425MWG", "Blueprint", "Rp 10.300.000", "Rp 82.400.000 (8 unit)"),
        ("6", "Weighbridge 80 Ton", "Fangda Electronic Truck Scale", "Simulasi", "Rp 267.000.000", "Rp 534.000.000 (2 unit)"),
        ("7", "RFID Reader UHF 20m", "Long-Range UHF Reader 20m", "Simulasi", "Rp 3.000.000", "Rp 12.000.000 (4 unit)"),
        ("8", "RFID Tag UHF ISO", "UHF Anti-Metal ISO 18000-6C", "Blueprint", "Rp 690.000 / pack", "Rp 34.500.000 (50 pack)"),
        ("9", "Automatic Barrier Gate", "High-Speed DC Brushless Gate", "Blueprint", "Rp 60.000.000", "Rp 240.000.000 (4 unit)"),
        ("10", "LED Info Display", "Chipshow Outdoor P6/P8 LED", "Simulasi", "Rp 47.000.000", "Rp 188.000.000 (4 unit)"),
        ("11", "Industrial Switch PoE+", "Managed 24-Port GbE IP40", "Blueprint", "Rp 10.000.000", "Rp 120.000.000 (12 unit)"),
        ("12", "Outdoor Access Point", "Ubiquiti U7 Pro WiFi 7", "Blueprint", "Rp 7.200.000", "Rp 129.600.000 (18 unit)"),
        ("13", "Server NVR & DB Core", "Hikvision DS-9664NI-M16/R", "Blueprint", "Rp 102.000.000", "Rp 204.000.000 (2 unit)")
    ]

    for i, row in enumerate(bom_part1):
        for j, val in enumerate(row):
            cell = t14.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            if i % 2 == 0:
                cell.fill.fore_color.rgb = C_LIGHT_BG
            else:
                cell.fill.fore_color.rgb = C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(8)
                p.font.color.rgb = C_TEXT

    # =========================================================================
    # SLIDE 15: MASTER BOM TABEL LENGKAP ITEM 14 - 26
    # =========================================================================
    s15 = prs.slides.add_slide(blank_layout)
    add_header(s15, "Tabel Lengkap 26 Hardware & Spesifikasi (Item #14 s/d #26)", "LAMPIRAN C: MASTER BILL OF MATERIALS (BOM)")
    add_footer(s15, 15)

    t_shape15 = s15.shapes.add_table(14, 6, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    t15 = t_shape15.table
    t15.columns[0].width = Inches(0.6)
    t15.columns[1].width = Inches(2.6)
    t15.columns[2].width = Inches(2.8)
    t15.columns[3].width = Inches(1.5)
    t15.columns[4].width = Inches(2.0)
    t15.columns[5].width = Inches(2.2)

    for j, h in enumerate(headers_bom):
        cell = t15.cell(0, j)
        cell.text = h
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        for p in cell.text_frame.paragraphs:
            p.font.size = Pt(9)
            p.font.bold = True
            p.font.color.rgb = C_WHITE

    bom_part2 = [
        ("14", "Online UPS 5000VA", "APC Smart-UPS SRT5KRMXLI", "Blueprint", "Rp 81.000.000", "Rp 324.000.000 (4 unit)"),
        ("15", "Barcode / QR Scanner", "Zebra Symbol LS2208 USB", "Blueprint", "Rp 1.600.000", "Rp 12.800.000 (8 unit)"),
        ("16", "Thermal Camera Reefer", "Hikvision DS-2TD2636B-13/P", "Blueprint", "Rp 30.000.000", "Rp 120.000.000 (4 unit)"),
        ("17", "GPS Tracker Truk 4G", "Teltonika TAT240 4G LTE", "Blueprint", "Rp 2.500.000", "Rp 100.000.000 (40 unit)"),
        ("18", "Industrial Edge AI PC", "Advantech ARK-3532 Fanless", "Simulasi", "Rp 42.000.000", "Rp 168.000.000 (4 unit)"),
        ("19", "Gantry X-Ray Scanner", "Nuctech MB1215DE 6 MeV", "Blueprint", "Rp 12.500.000.000", "Rp 12.500.000.000 (1 set)"),
        ("20", "VMT Kabin Alat Berat", "Zebra VC8300 Rugged 8\"", "Blueprint", "Rp 74.000.000", "Rp 444.000.000 (6 unit)"),
        ("21", "DGPS / RTK Receiver", "CHCNAV CGI-610 GNSS/INS", "Simulasi", "Rp 45.000.000", "Rp 270.000.000 (6 unit)"),
        ("22", "Spreader Twistlock Kit", "Bromma SmartSpreader Kit", "Simulasi", "Rp 35.000.000", "Rp 210.000.000 (6 unit)"),
        ("23", "Smart Reefer Socket", "Marechal 380V/32A Receptacle", "Simulasi", "Rp 8.500.000", "Rp 2.550.000.000 (300 unit)"),
        ("24", "Rugged Mobile PDA", "Zebra TC57x Handheld IP68", "Blueprint", "Rp 16.000.000", "Rp 160.000.000 (10 unit)"),
        ("25", "Rail Axle Counter", "Frauscher RSR123 Wheel Sensor", "Simulasi", "Rp 85.000.000", "Rp 340.000.000 (4 sensor)"),
        ("26", "Smart E-Seal GPS", "Jointech JT701 GPS Smart Lock", "Simulasi", "Rp 3.200.000", "Rp 320.000.000 (100 unit)")
    ]

    for i, row in enumerate(bom_part2):
        for j, val in enumerate(row):
            cell = t15.cell(i + 1, j)
            cell.text = val
            cell.fill.solid()
            if i % 2 == 0:
                cell.fill.fore_color.rgb = C_LIGHT_BG
            else:
                cell.fill.fore_color.rgb = C_WHITE
            for p in cell.text_frame.paragraphs:
                p.font.size = Pt(8)
                p.font.color.rgb = C_TEXT

    # Save presentation
    output_path = "c:/xampp/htdocs/Conclusion_Intermodal_Dry_Port/hardware/PRESENTASI_HARDWARE_CIDP.pptx"
    prs.save(output_path)
    print(f"Presentation saved successfully to {output_path} with {TOTAL_SLIDES} slides.")

if __name__ == "__main__":
    create_presentation()
