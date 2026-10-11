import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

def create_ba_presentation():
    prs = Presentation()
    # 16:9 Widescreen (13.333 x 7.5 Inches)
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    TOTAL_SLIDES = 12

    # Professional Consultant Color Palette (Slate / Navy / Port Authority)
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
    C_PURPLE = RGBColor(147, 51, 234)   # #9333ea - Purple
    C_WHITE = RGBColor(255, 255, 255)
    C_GREEN_BG = RGBColor(236, 253, 245)# #ecfdf5
    C_BLUE_BG = RGBColor(239, 246, 255) # #eff6ff
    C_AMBER_BG = RGBColor(254, 243, 199) # #fef3c7
    C_RED_BG = RGBColor(254, 242, 242)  # #fef2f2
    C_RED = RGBColor(239, 68, 68)

    def add_header(slide, title_text, category_text="CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC BUSINESS BLUEPRINT", web_page=None, web_url=None):
        box_width = Inches(8.2) if web_page else Inches(11.7)
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.28), box_width, Inches(1.15))
        tf = header_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        
        p0 = tf.paragraphs[0]
        p0.text = category_text.upper()
        p0.font.size = Pt(8.5)
        p0.font.bold = True
        p0.font.color.rgb = C_BLUE
        
        p1 = tf.add_paragraph()
        p1.text = title_text
        p1.font.size = Pt(16.5) if web_page else Pt(18.5)
        p1.font.bold = True
        p1.font.color.rgb = C_NAVY
        p1.space_before = Pt(2)

        # Web Showcase Badge on Top Right
        if web_page:
            badge_card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(9.1), Inches(0.26), Inches(3.4), Inches(1.02))
            badge_card.fill.solid()
            badge_card.fill.fore_color.rgb = C_BLUE_BG
            badge_card.line.color.rgb = C_BLUE
            badge_card.line.width = Pt(1)
            btf = badge_card.text_frame
            btf.word_wrap = True
            btf.margin_left = Inches(0.12)
            btf.margin_right = Inches(0.12)
            btf.margin_top = Inches(0.08)
            btf.margin_bottom = Inches(0.08)
            
            bp0 = btf.paragraphs[0]
            bp0.text = "🌐 HALAMAN WEB / LIVE DEMO"
            bp0.font.size = Pt(7.5)
            bp0.font.bold = True
            bp0.font.color.rgb = C_BLUE
            
            bp1 = btf.add_paragraph()
            bp1.text = web_page
            bp1.font.size = Pt(9.5)
            bp1.font.bold = True
            bp1.font.color.rgb = C_NAVY
            bp1.space_before = Pt(1)
            
            if web_url:
                bp2 = btf.add_paragraph()
                bp2.text = web_url
                bp2.font.size = Pt(7)
                bp2.font.italic = True
                bp2.font.color.rgb = C_MUTED
                bp2.space_before = Pt(1)

    def add_footer(slide, current_slide):
        footer_box = slide.shapes.add_textbox(Inches(0.8), Inches(7.05), Inches(11.7), Inches(0.35))
        tf = footer_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = f"Conclusion Supply Chain Consultant  •  Naufal Andika Heditya & Zulfikar J. Fatah  •  Topik 7: CIDP 35 Ha                    Slide {current_slide} of {TOTAL_SLIDES}"
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

    def add_demo_banner(slide, text="[LIVE DEMO]: Buka Halaman Web YMS", left=Inches(0.8), top=Inches(6.40), width=Inches(11.7), height=Inches(0.48)):
        b = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        b.fill.solid()
        b.fill.fore_color.rgb = C_AMBER_BG
        b.line.color.rgb = C_AMBER
        b.line.width = Pt(1)
        tf = b.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = f"👉  {text}"
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = RGBColor(180, 83, 9)
        p.alignment = PP_ALIGN.LEFT

    # =========================================================================
    # SLIDE 1: COVER KONSULTAN
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
    p.text = "CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC ADVISORY REPORT"
    p.font.size = Pt(11)
    p.font.bold = True
    p.font.color.rgb = RGBColor(56, 189, 248)

    p = tf1.add_paragraph()
    p.text = "Business Analysis & Alur Proses Bisnis End-to-End:\nPemetaan Fitur Software Logistik CIDP 35 Ha"
    p.font.size = Pt(21)
    p.font.bold = True
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(4)

    p = tf1.add_paragraph()
    p.text = "STANDAR SILABUS: ALUR KEPUTUSAN (JIKA YA / JIKA TIDAK), ALAT & HARDWARE, VALIDASI BISNIS, SERTA FEASIBILITY"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p.space_before = Pt(6)

    # Consultant Team Cards
    team_top = Inches(2.85)
    col_w = Inches(5.6)
    card_h = Inches(3.25)

    # Box 1: Konsultan Utama (Presenter)
    c1 = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.1), team_top, col_w, card_h)
    c1.fill.solid()
    c1.fill.fore_color.rgb = RGBColor(15, 33, 64)
    c1.line.color.rgb = RGBColor(30, 58, 110)
    c1.line.width = Pt(1)

    tf_c1 = c1.text_frame
    tf_c1.word_wrap = True
    p = tf_c1.paragraphs[0]
    p.text = "TIM ADVISORY BUSINESS ANALYSIS & QA"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = RGBColor(56, 189, 248)

    p = tf_c1.add_paragraph()
    p.text = "• Naufal Andika Heditya (24D507001012) — Lead Business Analyst & QA\n  Fokus: End-to-End Decision Flows, Business Rules Mapping, Exception Handling, Validasi 3D Bay-Row-Tier, Standar GS1 SSCC-18, & Feasibility Study.\n\n• Zulfikar Jafarudin Fatah (24D507001001) — Lead System Architect\n  Fokus: Arsitektur Eksekutif Web YMS, Database MySQL, & Integrasi 5 Modul."
    p.font.size = Pt(9)
    p.font.color.rgb = RGBColor(226, 232, 240)
    p.space_before = Pt(6)

    # Box 2: Supporting Consultants
    c2 = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.9), team_top, col_w, card_h)
    c2.fill.solid()
    c2.fill.fore_color.rgb = RGBColor(15, 33, 64)
    c2.line.color.rgb = RGBColor(30, 58, 110)
    c2.line.width = Pt(1)

    tf_c2 = c2.text_frame
    tf_c2.word_wrap = True
    p = tf_c2.paragraphs[0]
    p.text = "DEWAN KONSULTAN PENDUKUNG (CONCLUSION CONSULTANT)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = RGBColor(56, 189, 248)

    p = tf_c2.add_paragraph()
    p.text = "• Armansyah Muchtarrom (24D507001010) — Hardware Specialist\n  (Spesifikasi 26 Sensor Lapangan, Jembatan Timbang & Otomasi Gate)\n\n• Afriansayah Ayubi (24D507001026) — Software & ERP Specialist\n  (Logika Penanganan Peti Kemas & Terminal Tariff Engine Odoo)\n\n• Juan Gamaliel (24D507001016) — Data Integration Specialist\n  (REST API Gateway, Payload JSON & Stacking Rules Matrix)"
    p.font.size = Pt(9)
    p.font.color.rgb = RGBColor(226, 232, 240)
    p.space_before = Pt(6)

    # Footer Box
    fbox = s1.shapes.add_textbox(Inches(1.1), Inches(6.25), Inches(11.4), Inches(0.9))
    ftf = fbox.text_frame
    ftf.word_wrap = True
    ftf.margin_left = ftf.margin_top = ftf.margin_right = ftf.margin_bottom = 0
    p = ftf.paragraphs[0]
    p.text = "Klien / Evaluator Akademik: Dr. Tigor Franky, S.T., M.T.  •  Mata Kuliah: Teknologi dan Perangkat Lunak Logistik"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = RGBColor(203, 213, 225)
    p = ftf.add_paragraph()
    p.text = "Program Studi S1 Manajemen Logistik  •  Institut Transportasi dan Logistik (ITL) Trisakti  •  Oktober 2026"
    p.font.size = Pt(9)
    p.font.color.rgb = RGBColor(148, 163, 184)
    p.space_before = Pt(1)
    p = ftf.add_paragraph()
    p.text = "🌐 Live Web Application Portal: http://localhost/Conclusion_Intermodal_Dry_Port/dashboard.php"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = RGBColor(56, 189, 248)
    p.space_before = Pt(2)

    # =========================================================================
    # SLIDE 2: PARADIGMA BUSINESS ANALYST: DEVELOPER VS BA & METODOLOGI
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "Paradigma Business Analyst: Developer vs BA & Metodologi 3-Langkah", web_page="dashboard.php?page=beranda", web_url="http://localhost/.../dashboard.php")
    add_footer(s2, 2)

    card_w = Inches(3.7)
    card_h = Inches(4.55)

    # Card 1: Developer vs BA
    create_card(s2, Inches(0.8), Inches(1.65), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(1.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "DEVELOPER VS BUSINESS ANALYST"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Perbedaan Sudut Pandang"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Pendekatan Developer (Koding):\n  - Fokus struktur koding, database sintaks, & tombol.\n  - Contoh: Membuat Button 'Confirm Putaway'.\n\n• Pendekatan Business Analyst (BA):\n  - Fokus logika bisnis, batasan input, & dampak operasional.\n  - Contoh: 'Sistem wajib verifikasi berat barang <= kapasitas rak bin sebelum status lokasi diperbarui di database.'"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    # Card 2: 3-Langkah Analisis BA
    create_card(s2, Inches(4.8), Inches(1.65), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s2.shapes.add_textbox(Inches(5.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "METODOLOGI PERKULIAHAN DR. TIGOR"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Rumus 3-Langkah Analisis Fitur"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "1. FITUR / EVENT TRIGGER:\n   Tombol UI / input sensor (misal: 'Trigger Gate-In').\n\n2. VALIDASI BUSINESS RULE:\n   Verifikasi aturan (misal: 'Berat <= 34.000 kg SOLAS VGM & ISO Modulo 11').\n\n3. DAMPAK & STATUS DATABASE:\n   Perubahan data & aksi fisik (misal: 'Status GATE_IN_APPROVED, e-Pass cetak, palang buka')."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    # Card 3: 5 Modul Terintegrasi
    create_card(s2, Inches(8.8), Inches(1.65), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(9.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ORKESTRASI 5 MODUL LOGISTIK"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p.space_before = Pt(0)
    p = tf.add_paragraph()
    p.text = "Ekosistem Terpadu CIDP"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• WCS: Kontrol sensor OCR, timbangan 80t, & RFID.\n• YMS: Penjadwalan gate & alokasi 3D Bay-Row-Tier.\n• WMS: Gudang CFS LCL (GS1 SSCC-18) & Reefer dingin.\n• TMS: Intermodal rel siding 600m & GPS E-Seal KA.\n• ERP: Odoo Billing & Webhook CEISA 4.0 Bea Cukai."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    add_demo_banner(s2, "PUSAT NAVIGASI SISTEM: Seluruh Alur Terintegrasi Diakses Melalui Portal 'dashboard.php?page=beranda'", top=Inches(6.32))

    # =========================================================================
    # SLIDE 3: MASTER END-TO-END DECISION MAP: PETA KEPUTUSAN TERMINAL (NEW SLIDE)
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "Master End-to-End Decision Flow: Peta Alur Keputusan Terminal (Gate-In s/d Gate-Out)", web_page="dashboard.php?page=simulator", web_url="http://localhost/.../simulator.php")
    add_footer(s3, 3)

    # 5 Sequential Decision Stage Cards
    stage_w = Inches(2.22)
    stage_h = Inches(4.55)
    gap = Inches(0.15)
    stages = [
        ("STAGE 1: GATE-IN", "Truk Timbang & OCR", C_BLUE,
         "• Decision Point:\n  Berat Total <= 34t & Check Digit ISO Sah?\n\n• [JIKA YA]:\n  Cetak e-Pass QR, palang buka, truk masuk ke zona tujuan.\n\n• [JIKA TIDAK]:\n  Palang terkunci, sirine bunyi, truk tolak belok Jalur U-Turn.\n\n• Menggunakan Apa:\n  Timbangan Pitless 80t Avery, Kamera ANPR 4K, Barrier Gate."),
        
        ("STAGE 2: YARD STACK", "Lift-Off & Penataan 3D", C_INDIGO,
         "• Decision Point:\n  Boks 20ft vs 40ft? Lolos Aturan Anti-Floating?\n\n• [JIKA YA]:\n  20ft ke Bay Ganjil; 40ft ke Bay Genap. Laden di Tier 1-2.\n\n• [JIKA TIDAK]:\n  YMS tolak putaway, alarm tablet operator bunyi, arahkan bin lain.\n\n• Menggunakan Apa:\n  3 RS 45t Kalmar (Spreader Telescopic), 1 RTG 40t, VMT."),
        
        ("STAGE 3: FASILITAS", "Reefer & CFS Inbound", C_PURPLE,
         "• Decision Point:\n  Suhu -18C s/d -25C? Barcode GS1 SSCC Valid?\n\n• [JIKA YA]:\n  Colok steker 380V; Forklift susun palet ke rak bin CFS.\n\n• [JIKA TIDAK]:\n  Alarm Reefer + failover Genset; Barcode cacat cetak ulang (BLOCKED).\n\n• Menggunakan Apa:\n  300 Soket CEE 380V, Genset 1.250kVA, Forklift 3t, PDA Zebra."),
        
        ("STAGE 4: KEPABEANAN", "CEISA 4.0 & Rel Kereta", C_BLUE,
         "• Decision Point:\n  Profil Hijau vs Merah? Smart E-Seal Rel Utuh?\n\n• [JIKA YA]:\n  SPPB rilis <5 mnt; Muat 30 GD KA (60 TEU) jalan 90 mnt.\n\n• [JIKA TIDAK]:\n  Jalur Merah ke Behandle X-Ray; Kawat putus picu Tamper Alert.\n\n• Menggunakan Apa:\n  Webhook CEISA, Gantry X-Ray 6 MeV, Lok CC206, E-Seal Satelit."),
        
        ("STAGE 5: GATE-OUT", "Billing Zero Leakage", C_EMERALD,
         "• Decision Point:\n  Invoice Odoo PAID & Dokumen SPPB Cleared?\n\n• [JIKA YA]:\n  Palang terbuka, foto ANPR e-Exit Pass, kargo rilis.\n\n• [JIKA TIDAK]:\n  Palang KUNCI PERMANEN, VMS suruh ke kasir, truk ke Holding Lane.\n\n• Menggunakan Apa:\n  ERP Odoo Finance Webhook, Barrier Gate, Display LED VMS.")
    ]

    for idx, (st_title, st_sub, st_color, st_text) in enumerate(stages):
        pos_x = Inches(0.8) + idx * (stage_w + gap)
        create_card(s3, pos_x, Inches(1.65), stage_w, stage_h)
        tb = s3.shapes.add_textbox(pos_x + Inches(0.1), Inches(1.8), stage_w - Inches(0.2), stage_h - Inches(0.3))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = st_title
        p.font.size = Pt(8.5)
        p.font.bold = True
        p.font.color.rgb = st_color
        p = tf.add_paragraph()
        p.text = st_sub
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(1)
        p = tf.add_paragraph()
        p.text = st_text
        p.font.size = Pt(7.5)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(3)

    add_demo_banner(s3, "ARSITEKTUR KEPUTUSAN TERPADU: 5 Gatepoints Menjamin Kepatuhan Regulasi & Zero Revenue Leakage!", top=Inches(6.32))

    # =========================================================================
    # SLIDE 4: FLOW 1: GERBANG MASUK & TIMBANGAN DINAMIS (WCS + YMS)
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "Flow 1: Gerbang Masuk & Timbangan Dinamis (WCS + YMS)", web_page="pages/gate.php", web_url="http://localhost/.../pages/gate.php")
    add_footer(s4, 4)

    col_w = Inches(5.7)
    card_h = Inches(3.2)

    # Top Left: JIKA YA
    create_card(s4, Inches(0.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s4.shapes.add_textbox(Inches(1.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "[DECISION POINT]: Berat <= 34.000 kg & ISO Modulo 11 Sah?"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "JIKA YA (PASS / APPROVED) -> TRUK MASUK KE ZONA"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Aksi Sistem: Tiket e-Pass (Kode QR) tercetak <90 detik, palang barrier terbuka otomatis.\n• Status Database: GATE_IN_APPROVED\n• Ke Mana Truk Pergi Sesuai Manifes?\n  - FCL Kering -> Masuk ke Lapangan Yard Blok A/B/C/D.\n  - Reefer Dingin -> Masuk ke Blok E (Area 300 Steker Listrik).\n  - Kargo LCL -> Menuju Rampa Dock Gudang CFS (D1-D5).\n  - Pabean Jalur Merah -> Menuju Hanggar Behandle Bea Cukai."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Top Right: JIKA TIDAK
    create_card(s4, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_RED_BG, border_color=C_RED)
    tb = s4.shapes.add_textbox(Inches(7.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "[DECISION POINT]: Berat > 34.000 kg ATAU ISO Check Digit Salah?"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_RED
    p = tf.add_paragraph()
    p.text = "JIKA TIDAK (REJECTED) -> TOLAK & RUTE U-TURN"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_RED
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Aksi Sistem: Palang Barrier TETAP TERKUNCI PERMANEN, sirine alarm 105 dB berbunyi, lampu LED merah menyala.\n• Status Database: OVERLOAD_REJECTED / INVALID_CHECK_DIGIT\n• Ke Mana Truk Pergi?\n  - Dilarang keras masuk ke pelataran terminal penumpukan!\n  - Sistem mengarahkan supir belok kiri ke Jalur U-Turn (Putar Balik) untuk keluar terminal atau ke Redress Area.\n  - Menyelamatkan jalan beton K-450 dari keretakan dini."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Bottom Full Card: MENGGUNAKAN APA
    create_card(s4, Inches(0.8), Inches(4.98), Inches(11.7), Inches(1.25), bg_color=C_CARD_BG, border_color=C_BLUE)
    tb = s4.shapes.add_textbox(Inches(1.0), Inches(5.08), Inches(11.3), Inches(1.05))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MENGGUNAKAN APA (ASET FISIK, SENSOR HARDWARE & SOFTWARE MODUL):"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p = tf.add_paragraph()
    p.text = "• Sensor & Timbangan: Jembatan Timbang Pitless 80 Ton (Load Cell Avery Weigh-Tronix) + Inductive Ground Loop Detektor WCS.\n• Kamera & Vision AI: Kamera ANPR Hikvision 4K (Plat Nomor Truk) + Kamera OCR Boks Kontainer (Standar ISO 6346:2022).\n• Otomasi Gerbang: Automatic Systems Barrier Gate Magnetic Control + Kiosk Cetak Tiket Thermal Zebra Kiosk KRD.\n• Perangkat Lunak: Modul WCS Gate Controller (Event Listener IoT) & YMS Gate Inbound Engine."
    p.font.size = Pt(8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(2)

    add_demo_banner(s4, "[LIVE DEMO]: Buka 'pages/gate.php' -> Jalankan Simulasi Kamera OCR, ANPR, & Uji Truk Overload SOLAS (>34t)", top=Inches(6.32))

    # =========================================================================
    # SLIDE 5: FLOW 2: PENGANGKATAN & STACKING 3D BAY-ROW-TIER (YMS + WCS)
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "Flow 2: Pengangkatan Kontainer & Stacking 3D Bay-Row-Tier (YMS + WCS)", web_page="pages/yard.php", web_url="http://localhost/.../pages/yard.php")
    add_footer(s5, 5)

    # Top Left: Siapa yang Mengangkat & Decision Ukuran
    create_card(s5, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "SIAPA YANG MENGANGKAT & DECISION UKURAN BOKS"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Reach Stacker 45t Telescopic Spreader vs RTG vs Forklift"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision 1: Kontainer 20ft vs 40ft?\n  - [JIKA 20 FT]: Reach Stacker 45t gunakan posisi Spreader 20ft -> Menaruh di Bay Ganjil (01, 03, 05, 07).\n  - [JIKA 40 FT]: Spreader Telescopic hidrolik memanjang otomatis ke 40ft -> Menaruh di Bay Genap (02, 06, 10).\n• Penumpukan Intensif (Blok A/B): Diangkat RTG Crane 40t 16-Roda.\n• Palet di Gudang CFS: Diangkat Forklift Heavy Duty 3-5 Ton.\n• Rangkaian Kereta Api: Diangkat Reach Stacker di Siding Rel 600m."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Top Right: Aturan Stacking JIKA YA vs JIKA TIDAK
    create_card(s5, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(7.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "DECISION ATURAN PENEMPATAN 3D BAY-ROW-TIER"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Logika Keputusan Penempatan Slot Lapangan"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision 2: Apakah Penempatan Memenuhi Aturan Stacking?\n  - [JIKA YA]: Boks diletakkan di koordinat 3D Bay-Row-Tier yang ditugaskan -> Status STACK_CONFIRMED. Rasio gerak derek optimal 1.1 moves/box (hemat solar HSD Rp 1,06 M/thn).\n  - [JIKA TIDAK]: Larangan Corner Casting (20ft di atas 40ft) atau Anti-Floating (Tier n saat n-1 kosong) -> Sistem YMS tolak perintah putaway! Alarm tablet kabin berbunyi, operator dialihkan ke bin lain."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Bottom Full Card: MENGGUNAKAN APA
    create_card(s5, Inches(0.8), Inches(4.98), Inches(11.7), Inches(1.25), bg_color=C_CARD_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(5.08), Inches(11.3), Inches(1.05))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MENGGUNAKAN APA (ARMADA ALAT BERAT, HARDWARE & SOFTWARE YMS):"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p = tf.add_paragraph()
    p.text = "• Armada Derek: 3 Unit Reach Stacker 45 Ton Kalmar DRG450-65S5M (Telescopic Spreader Bromma) + 1 Unit RTG Crane 40 Ton Konecranes.\n• Armada CFS: Forklift Elektrik Linde 3-5 Ton + Rampa Loading Dock Leveler Hidrolik.\n• Komunikasi Kabin: Komputer Kabin VMT (Vehicle Mounted Terminal) Advantech DLT-V72 terhubung Industrial Wireless Mesh.\n• Perangkat Lunak: Modul YMS 3D Yard Allocation Engine, WCS Equipment Dispatcher & Driver Mobile Job Order."
    p.font.size = Pt(8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(2)

    add_demo_banner(s5, "[LIVE DEMO]: Buka 'pages/yard.php' & 'pages/simulator.php' -> Uji Coba Telescopic Spreader & Alokasi Slot 3D", top=Inches(6.32))

    # =========================================================================
    # SLIDE 6: FLOW 3 & 4: RANTAI DINGIN REEFER & CFS INBOUND (WMS + WCS)
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "Flow 3 & 4: Rantai Dingin Reefer & Gudang CFS Inbound Stripping (WMS + WCS)", web_page="pages/reefer.php & pages/cfs.php", web_url="http://localhost/.../pages/reefer.php")
    add_footer(s6, 6)

    # Left: Flow 3 Reefer
    create_card(s6, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s6.shapes.add_textbox(Inches(1.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FLOW 3: COLD CHAIN REEFER (BLOK E - 300 PLUGS)"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Decision Suhu Dingin & Penanganan Daya"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision Point: Suhu -18C s/d -25C & Daya Listrik < 30 Menit?\n  - [JIKA YA]: Colok steker CEE 380V/32A, status REEFER_PLUGGED_ACTIVE, telemetri IoT merekam suhu tiap 60s, tarif listrik Rp 250rb/shift tercatat otomatis.\n  - [JIKA TIDAK (Suhu > -15C / Listrik Padam)]: WCS picu REEFER_BREACH_ALERT, sirine bunyi, Automatic Transfer Switch menyalakan Genset cadangan 1.250 kVA, teknisi reefer cek unit. Hindari klaim kargo $100.000+."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Right: Flow 4 CFS
    create_card(s6, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s6.shapes.add_textbox(Inches(7.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FLOW 4: GUDANG CFS INBOUND STRIPPING LCL"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p.space_before = Pt(0)
    p = tf.add_paragraph()
    p.text = "Decision Barcode GS1 SSCC-18 & ASN Matching"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision Point: Barcode GS1 SSCC-18 Valid & Kuantitas ASN Sesuai?\n  - [JIKA YA]: Blind count tally lolos, Forklift bawa palet ke rak bin terarah (Directed Putaway), status PALLET_QC_PASSED.\n  - [JIKA TIDAK (Cacat / Selisih)]: Barcode rusak -> Cetak ulang label GS1 baru via printer barcode; Kargo selisih/cacat -> Status diubah menjadi BLOCKED (Karantina QC) agar tidak keluar gudang sebelum diinvestigasi."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Bottom Full Card: MENGGUNAKAN APA
    create_card(s6, Inches(0.8), Inches(4.98), Inches(11.7), Inches(1.25), bg_color=C_CARD_BG, border_color=C_BLUE)
    tb = s6.shapes.add_textbox(Inches(1.0), Inches(5.08), Inches(11.3), Inches(1.05))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MENGGUNAKAN APA (ASET LISTRIK, SENSOR IOT, PDA SCANNER & WMS MODUL):"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p = tf.add_paragraph()
    p.text = "• Fasilitas Reefer: 300 Soket CEE Mennekes 380V/32A IP67, Trafo PLN 1.250 kVA, Genset Cummins 1.250 kVA + Sensor IoT LoRaWAN Dragino LHT65N.\n• Fasilitas CFS: 5 Dock Leveler Hidrolik Rite-Hite (D1-D5), Handheld PDA Zebra TC26 Android (2D Imager), Printer Barcode Zebra ZT411.\n• Armada Angkut CFS: Forklift Elektrik 3 Ton Linde E30.\n• Perangkat Lunak: Modul WMS Cold Chain Monitoring, WMS Inbound Stripping & Directed Putaway Engine."
    p.font.size = Pt(8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(2)

    add_demo_banner(s6, "[LIVE DEMO]: Buka 'pages/reefer.php' (Monitoring Suhu 300 Plugs) & 'pages/cfs.php' (Scan Barcode GS1 SSCC-18)", top=Inches(6.32))

    # =========================================================================
    # SLIDE 7: FLOW 5 & 6: KEPATUHAN PABEAN & KERETA API REL SIDING (ERP + TMS)
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "Flow 5 & 6: Kepatuhan Bea Cukai CEISA 4.0 & Kereta Api Rel Siding 600m (ERP + TMS)", web_page="pages/customs.php & pages/yard.php", web_url="http://localhost/.../pages/customs.php")
    add_footer(s7, 7)

    # Left: Flow 5 Bea Cukai
    create_card(s7, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s7.shapes.add_textbox(Inches(1.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FLOW 5: KEPATUHAN PABEAN CEISA 4.0 DJBC (PMK 190/2022)"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Decision Profil Risiko: Jalur Hijau vs Jalur Merah"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision Point: Profil Risiko Manifes Hijau atau Merah?\n  - [JIKA HIJAU (94,2%)]: Webhook CEISA terbitkan SPPB digital <5 menit -> Status SPPB_CLEARED, kontainer langsung siap rilis.\n  - [JIKA MERAH (5,8%)]: Status dikunci CUSTOMS_HOLD -> Reach Stacker otomatis bawa boks ke Hanggar Behandle Bea Cukai untuk dipindai Portal Gantry X-Ray 6 MeV. Dilarang Gate-Out sebelum inspektur pabean setujui SPPB manual."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Right: Flow 6 Kereta Api
    create_card(s7, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s7.shapes.add_textbox(Inches(7.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FLOW 6: INTERMODAL KERETA API REL SIDING 600M"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Decision Integritas Segel Smart GPS E-Seal KA"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision Point: Beban Gandar <= 18t & Smart E-Seal Utuh?\n  - [JIKA YA]: RS muat 30 Gerbong Datar (60 TEU), berangkat tepat waktu 90 mnt ke Tanjung Priok, status TRAIN_DISPATCHED_ON_TIME.\n  - [JIKA TIDAK (Kawat Segel Putus / Tamper)]: Satelit GPS kirim peringatan darurat PABEAN_TAMPER_ALERT ke CIDP, boks langsung diisolasi saat tiba, Polsuska & Bea Cukai lakukan penyegelan ulang."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Bottom Full Card: MENGGUNAKAN APA
    create_card(s7, Inches(0.8), Inches(4.98), Inches(11.7), Inches(1.25), bg_color=C_CARD_BG, border_color=C_BLUE)
    tb = s7.shapes.add_textbox(Inches(1.0), Inches(5.08), Inches(11.3), Inches(1.05))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MENGGUNAKAN APA (PORTAL X-RAY, INFRASTRUKTUR REL, E-SEAL & TMS MODUL):"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p = tf.add_paragraph()
    p.text = "• Mesin Pemindai Pabean: Nuctech FG9056 Portal Gantry X-Ray 6 MeV (Daya tembus 300 mm baja) + Hanggar Behandle Tertutup 1.500 m².\n• Kereta Api: 2 Sepur Simpan Rel R54 600m, Lokomotif CC206 KAI, 30 Gerbong Datar PPCW (Kapasitas 60 TEU).\n• Perangkat Keamanan: Smart GPS E-Seal Satelit LoRa/GSM dengan sensor pemutus kawat kargo.\n• Perangkat Lunak: Modul TMS Rail Freight Dispatcher, 3D Train Load Planner & Webhook REST API Gateway CEISA 4.0 DJBC."
    p.font.size = Pt(8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(2)

    add_demo_banner(s7, "[LIVE DEMO]: Buka 'pages/customs.php' (Webhook SPPB) & 'dashboard.php' (Simulasi Rangkaian Rel Kereta Api)", top=Inches(6.32))

    # =========================================================================
    # SLIDE 8: FLOW 7: MODEL PENDAPATAN & ZERO REVENUE LEAKAGE (ERP + YMS)
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "Flow 7: Model Pendapatan & Penguncian Gate-Out Zero Revenue Leakage (ERP + YMS)", web_page="pages/billing.php", web_url="http://localhost/.../pages/billing.php")
    add_footer(s8, 8)

    # Left: 7 Revenue Streams
    create_card(s8, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "7 ALIRAN PENDAPATAN KOMERSIAL (GROSS REVENUE)"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Target Throughput 150.000 TEU: Rp 76.025.000.000 / Tahun"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "1. Lift-On / Lift-Off (Lo-Lo): Rp 33,75 M (44,4% - 150rb gerakan boks)\n2. Penumpukan Progresif: Rp 10,80 M (14,2% - Denda Inap Hari 4+)\n   - Masa I (1-3 hari) = Rp 0 | Masa II = Rp 45rb | Masa III = Rp 90rb/hari\n3. Bagi Hasil Kereta Api Logistik: Rp 9,00 M (11,8% - 6.000 GD)\n4. Gudang CFS Stuffing/Stripping: Rp 8,10 M (10,7%)\n5. Steker Pendingin Reefer Yard: Rp 6,25 M (8,2%)\n6. Jembatan Timbang VGM SOLAS: Rp 5,50 M (7,2%)\n7. Behandle Karantina Bea Cukai: Rp 2,62 M (3,5%)"
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Right: Decision Interlock Gate-Out
    create_card(s8, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s8.shapes.add_textbox(Inches(7.0), Inches(1.8), col_w - Inches(0.4), card_h - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "DECISION INTERLOCK GATE-OUT: ZERO REVENUE LEAKAGE"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p.space_before = Pt(0)
    p = tf.add_paragraph()
    p.text = "Penguncian Palang Gerbang Keluar Otomatis"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(1)
    p = tf.add_paragraph()
    p.text = "• Decision Point: Faktur ERP Odoo LUNAS (PAID) & Dokumen SPPB Cleared?\n  - [JIKA YA]: Palang barrier terbuka, kamera ANPR ambil foto e-Exit Pass, status GATE_OUT_COMPLETED, Zero Revenue Leakage terwujud!\n  - [JIKA TIDAK]: Palang TETAP TERKUNCI PERMANEN, Layar VMS tampilkan 'TAGIHAN BELUM LUNAS / TAHAN BEA CUKAI', truk diarahkan ke Holding Lane agar tidak menghambat lalu lintas."
    p.font.size = Pt(8.2)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(3)

    # Bottom Full Card: MENGGUNAKAN APA
    create_card(s8, Inches(0.8), Inches(4.98), Inches(11.7), Inches(1.25), bg_color=C_CARD_BG, border_color=C_BLUE)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(5.08), Inches(11.3), Inches(1.05))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MENGGUNAKAN APA (PALANG GATE-OUT, SISTEM BILLING & KASIR ODOO):"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p = tf.add_paragraph()
    p.text = "• Perangkat Keras Gerbang Keluar: Automatic Magnetic Barrier Gate Outbound + Layar Digital VMS (Variable Message Sign) 3-Warna.\n• Vision AI: Kamera ANPR Gate-Out Hikvision + RFID Card Reader.\n• Sistem Akuntansi: ERP Odoo Finance v17 Community Edition (Modul Invoicing, Journal Entries, Bank Reconciliation & Payment Gateway).\n• Integrasi Middleware: Event-Driven Webhook Tariff Engine yang mengunci palang gerbang secara otomatis."
    p.font.size = Pt(8)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(2)

    add_demo_banner(s8, "[LIVE DEMO]: Buka 'pages/billing.php' -> Simulasi Auto-Invoice Odoo & Penguncian Palang Gate-Out", top=Inches(6.32))

    # =========================================================================
    # SLIDE 9: EVALUASI KELAYAKAN INVESTASI FINANSIAL (PBP, NPV, IRR, ROI)
    # =========================================================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "Evaluasi Kelayakan Investasi Finansial: PBP, NPV, IRR & ROI", web_page="MODEL_FINANSIAL_CIDP.xlsx", web_url="Excel Financial Engine (Sheet Cash Flow 10 Thn)")
    add_footer(s9, 9)

    card_w4 = Inches(2.75)
    card_h4 = Inches(4.5)

    # Card 1: Cash Flow
    create_card(s9, Inches(0.8), Inches(1.65), card_w4, card_h4)
    tb = s9.shapes.add_textbox(Inches(0.95), Inches(1.85), card_w4 - Inches(0.3), card_h4 - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ARUS KAS & EBITDA"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Laba Bersih & Arus Kas"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Gross Revenue: Rp 76,02 M\n• Beban OPEX: Rp 24,79 M\n• EBITDA: Rp 51,23 Miliar\n  (Margin EBITDA: 67,4%)\n\n• Depresiasi (20 Thn): Rp 12,50 M\n• PPh Badan 22%: Rp 8,52 M\n• Laba Bersih EAT: Rp 30,21 M/thn\n• Net Cash Flow: Rp 42,71 M/thn"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: PBP
    create_card(s9, Inches(3.78), Inches(1.65), card_w4, card_h4, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s9.shapes.add_textbox(Inches(3.93), Inches(1.85), card_w4 - Inches(0.3), card_h4 - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "PAYBACK PERIOD (PBP)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "5.84 Tahun (~5 Thn 10 Bln)"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "Formula:\nPBP = CAPEX / Net Cash Flow\nPBP = Rp 249,5 M / Rp 42,71 M\n    = 5,84 Tahun\n\nInterpretasi Bisnis:\nModal Rp 250 Miliar kembali lunas dalam waktu kurang dari 6 tahun.\n\nBatas Aman Pelabuhan:\nStandar industri pelabuhan 8 - 10 tahun. CIDP 2 tahun lebih cepat!"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: NPV
    create_card(s9, Inches(6.76), Inches(1.65), card_w4, card_h4, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s9.shapes.add_textbox(Inches(6.91), Inches(1.85), card_w4 - Inches(0.3), card_h4 - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "NET PRESENT VALUE (NPV)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "+Rp 22.913.000.000"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "Parameter Analisis:\n• Horizon: 10 Tahun | WACC: 10%\n• Arus Kas Kumulatif: Rp 262,45 M\n• Nilai Investasi: Rp 249,50 M\n\nNPV = +Rp 22,91 Miliar (> 0)\n\nInterpretasi Bisnis:\nNilai NPV Positif membuktikan secara absolut bahwa proyek dry port FEASIBLE (Sangat Layak) dan memberi nilai tambah bersih."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 4: IRR & ROI
    create_card(s9, Inches(9.74), Inches(1.65), card_w4, card_h4)
    tb = s9.shapes.add_textbox(Inches(9.89), Inches(1.85), card_w4 - Inches(0.3), card_h4 - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "IRR & RETURN ON INVESTMENT"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "IRR: 11.90% | ROI: 140.6%"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Internal Rate of Return (IRR):\n  IRR 11,90% melampaui Hurdle Rate suku bunga kredit komersial bank (8,50%). Sangat bankable!\n\n• Return on Investment (10 Thn):\n  Total laba bersih 10 tahun (Rp 350,9 M) / CAPEX (Rp 249,5 M) = 140,6%.\n\n• Kesimpulan Investor:\n  Kombinasi PBP <6 tahun dan IRR >11% sangat aman bagi perbankan."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    add_demo_banner(s9, "[MODEL FINANSIAL EXCEL]: Buka Sheet 'Cash Flow 10 Thn' di MODEL_FINANSIAL_DAN_BLUEPRINT_CIDP.xlsx -> Formula NPV & IRR Asli", top=Inches(6.32))

    # =========================================================================
    # SLIDE 10: DASHBOARD INTELLIGENCE: EXECUTIVE COMMAND CENTER
    # =========================================================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "Dashboard Intelligence: Executive Command Center (C-Level DSS)", web_page="dashboard.php?page=beranda", web_url="http://localhost/.../dashboard.php")
    add_footer(s10, 10)

    # Col 1: 4 Metrik Pimpinan
    create_card(s10, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s10.shapes.add_textbox(Inches(1.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "4 METRIK KEPUTUSAN DIREKSI (C-LEVEL DSS)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Visibilitas Waktu-Nyata Kinerja Terminal"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "1. Yard Occupancy Rate (YOR) — 68,4%:\n   Ambang batas aman (<80%). Mencegah kemacetan penumpukan dan denda port congestion surcharge ($50/TEU).\n\n2. Throughput Harian — 482 TEU:\n   Mencapai 96,4% dari target harian (500 TEU). Mengukur kecepatan intermodal gate truk dan rail siding KA.\n\n3. Average Dwell Time — 2,3 Hari:\n   Sangat produktif dibanding rata-rata nasional pelabuhan laut (4 - 5 hari), mempercepat perputaran kargo.\n\n4. Gross Daily Revenue — Rp 184,5 Juta:\n   Arus kas harian tertagih real-time dengan Collection Rate 98,2%."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 2: Analitik Visual BI
    create_card(s10, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s10.shapes.add_textbox(Inches(7.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "BUSINESS INTELLIGENCE & ANALITIK DATA"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "Visualisasi Chart.js & Heatmap Dwell Time"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Hourly Traffic Flow Chart:\n  Grafik fluktuasi arus truk per jam untuk perencanaan penugasan operator gate & Reach Stacker (peak hour 10:00 - 15:00).\n\n• Dwell Time Heatmap Blok Yard:\n  Peta warna mendeteksi kontainer yang menginap kritis (>7 hari) di Blok A-E untuk pemicu denda Masa III otomatis.\n\n• Live Event Stream & Consignment Tracking:\n  Memonitor 5 event operasional terakhir (gate-in, timbang, lift-off, rilis SPPB) secara detik-per-detik untuk transparansi audit."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    add_demo_banner(s10, "[LIVE DEMO]: Buka Halaman 'Dashboard' (dashboard.php?page=beranda) -> Gauge YOR 68.4%, Chart.js, & Heatmap", top=Inches(6.32))

    # =========================================================================
    # SLIDE 11: MATRIKS PENANGANAN ANOMALI & QA TESTING MATRIX (PASS 100%)
    # =========================================================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "Matriks Penanganan Anomali & QA Testing Matrix (PASS 100%)", web_page="pages/simulator.php", web_url="http://localhost/.../pages/simulator.php")
    add_footer(s11, 11)

    # Integrated QA & Exception Table
    rows, cols = 8, 4
    left, top, width, height = Inches(0.8), Inches(1.65), Inches(11.7), Inches(4.3)
    table_shape = s11.shapes.add_table(rows, cols, left, top, width, height)
    table = table_shape.table
    table.columns[0].width = Inches(1.8)
    table.columns[1].width = Inches(2.2)
    table.columns[2].width = Inches(6.0)
    table.columns[3].width = Inches(1.7)

    headers = ["Kondisi Uji / Anomali", "Decision Logika BA", "Perilaku Sistem yang Diharapkan & Penanganan Sistem", "Status Hasil"]
    for c_idx, h in enumerate(headers):
        cell = table.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.size = Pt(9)
        p.font.bold = True
        p.font.color.rgb = C_WHITE

    qa_data = [
        ("Truk Gate-In Normal (<34t)", "Berat <= 34t & ISO Sah", "Palang buka <30s, e-Pass cetak, arahkan truk ke blok yard/reefer/CFS", "PASS (100%)"),
        ("Truk Overload SOLAS (>34t)", "Berat > 34.000 kg", "Palang kunci permanen, sirine bunyi, tolak masuk & arahkan ke Jalur U-Turn", "PASS (100%)"),
        ("Check Digit ISO Salah", "Modulo 11 Gagal", "Operator salah ketik digit kontainer; pendaftaran diblokir otomatis oleh sistem", "PASS (100%)"),
        ("Barcode Palet Cacat di CFS", "GS1 SSCC Gagal Baca", "Scanner gagal -> Trigger tombol Emergency Relabeling & cetak barcode baru", "PASS (100%)"),
        ("Pencegahan Tabrakan Yard", "Anti-Floating Violation", "Sistem tolak perintah taruh boks di Tier 3 tanpa adanya Tier 2 di bawahnya", "PASS (100%)"),
        ("Kontainer Jalur Merah", "Customs Hold Active", "Kontainer dilarang keras Gate-Out sebelum ada rilis resmi SPPB Bea Cukai", "PASS (100%)"),
        ("Kalkulasi Denda Inap", "Event-Driven Billing", "Masa I (Rp 0), Masa II (Rp 45rb), Masa III (Rp 90rb) tertagih tanpa kebocoran", "PASS (100%)")
    ]

    for r_idx, row in enumerate(qa_data):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx + 1, c_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_CARD_BG if r_idx % 2 == 0 else C_LIGHT_BG
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.size = Pt(8.2)
            p.font.color.rgb = C_TEXT
            if c_idx == 3:
                p.font.bold = True
                p.font.color.rgb = C_EMERALD

    sla_bar = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(6.12), Inches(11.7), Inches(0.68))
    sla_bar.fill.solid()
    sla_bar.fill.fore_color.rgb = C_GREEN_BG
    sla_bar.line.color.rgb = C_EMERALD
    sla_bar.line.width = Pt(1)
    tf = sla_bar.text_frame
    p = tf.paragraphs[0]
    p.text = "PENJAMINAN MUTU NON-FUNGSIONAL: Respon API 85 ms (<150 ms)  •  Siklus Gate 90s (<120s)  •  Zero Revenue Leakage (0% Bocor)  •  QA PASS 100%"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = RGBColor(6, 95, 70)
    p.alignment = PP_ALIGN.CENTER

    # =========================================================================
    # SLIDE 12: KESIMPULAN REKOMENDASI KONSULTAN & ROADMAP MENUJU GO-LIVE
    # =========================================================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "Kesimpulan Eksekutif Business Analyst & Rekomendasi Konsultan", web_page="dashboard.php (Integrated Portal)", web_url="http://localhost/Conclusion_Intermodal_Dry_Port/")
    add_footer(s12, 12)

    card_w = Inches(2.75)
    card_h = Inches(3.4)

    # 4 Pillar Cards
    pillars = [
        ("1. KELAYAKAN FINANSIAL", "Balik Modal 5.8 Tahun", C_BLUE, "• CAPEX Rp 250 M lunas dalam 5,84 tahun.\n• Net Profit Rp 30,2 M/thn.\n• NPV Positif +Rp 22,9 Miliar.\n• IRR 11,90% di atas bunga bank 8,5%."),
        ("2. EFISIENSI PROSES (BPR)", "Dwell Time Pangkas 66%", C_INDIGO, "• Waktu gate turun 15 mnt -> 90 detik.\n• Dwell time turun 6,8 -> 2,3 hari.\n• Eliminasi 100% box terselip via alokasi 3D Bay-Row-Tier."),
        ("3. STANDAR & KEPATUHAN", "GS1 SSCC & CEISA 4.0", C_PURPLE, "• Standar global GS1 AI (00) & ISO 6346.\n• Kepatuhan PMK 190/2022 DJBC.\n• Otomasi SPPB 94,2% & Smart E-Seal transit KA."),
        ("4. PENJAMINAN MUTU (QA)", "Zero Revenue Leakage", C_EMERALD, "• 100% kasus ekstrem tertangani (SOLAS overload & cek digit).\n• Palang Gate-Out kunci tagihan belum lunas.")
    ]

    for idx, (title, val, color, text) in enumerate(pillars):
        left_pos = Inches(0.8) + idx * Inches(2.98)
        create_card(s12, left_pos, Inches(1.65), card_w, card_h)
        tb = s12.shapes.add_textbox(left_pos + Inches(0.12), Inches(1.85), card_w - Inches(0.24), card_h - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(8.5)
        p.font.bold = True
        p.font.color.rgb = color
        p = tf.add_paragraph()
        p.text = val
        p.font.size = Pt(13)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        p.space_before = Pt(2)
        p = tf.add_paragraph()
        p.text = text
        p.font.size = Pt(8)
        p.font.color.rgb = C_TEXT
        p.space_before = Pt(4)

    # Recommendation Box (Bottom)
    r_box = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(5.25), Inches(11.7), Inches(1.55))
    r_box.fill.solid()
    r_box.fill.fore_color.rgb = C_NAVY
    r_box.line.color.rgb = C_BLUE
    r_box.line.width = Pt(1)
    tf = r_box.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "REKOMENDASI FINAL STRATEGIS CONCLUSION SUPPLY CHAIN CONSULTANT"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = RGBColor(56, 189, 248)

    p = tf.add_paragraph()
    p.text = "Sistem CIDP YMS terbukti secara empiris dan kuantitatif SANGAT LAYAK (FEASIBLE) untuk dilanjutkan ke tahap implementasi komersial. Solusi ini menyelesaikan inefisiensi pelabuhan konvensional, menjamin keuntungan finansial pemodal, dan memenuhi seluruh standar regulasi logistik nasional maupun internasional."
    p.font.size = Pt(8.5)
    p.font.color.rgb = RGBColor(226, 232, 240)
    p.space_before = Pt(3)

    p = tf.add_paragraph()
    p.text = "🌐 LIVE PORTAL: http://localhost/Conclusion_Intermodal_Dry_Port/  •  SESI TANYA JAWAB (Q&A)  •  DR. TIGOR FRANKY, S.T., M.T."
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p.space_before = Pt(4)
    p.alignment = PP_ALIGN.CENTER

    # Save presentation to multiple target paths
    out_dir = os.path.dirname(os.path.abspath(__file__))
    out_paths = [
        os.path.join(out_dir, "Conclusion Intermodal Dry Port_Business Analysis.pptx"),
        os.path.join(out_dir, "PRESENTASI_BUSINESS_ANALYST_CIDP.pptx"),
        os.path.join(out_dir, "..", "..", "business_analyst", "PRESENTASI_BUSINESS_ANALYST_CIDP.pptx")
    ]
    for p_path in out_paths:
        norm_path = os.path.normpath(p_path)
        os.makedirs(os.path.dirname(norm_path), exist_ok=True)
        prs.save(norm_path)
        print(f"Presentation saved to: {norm_path}")

if __name__ == "__main__":
    create_ba_presentation()
