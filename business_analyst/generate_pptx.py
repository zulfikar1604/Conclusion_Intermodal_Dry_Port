import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_ba_presentation():
    prs = Presentation()
    # 16:9 Widescreen (13.333 x 7.5 Inches)
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    TOTAL_SLIDES = 12

    # Professional Consultant Color Palette
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

    def add_header(slide, title_text, category_text="CONCLUSION SUPPLY CHAIN CONSULTANT — STRATEGIC BUSINESS BLUEPRINT"):
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
        p1.space_before = Pt(2)

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

    def add_demo_banner(slide, text="[LIVE DEMO]: Buka Halaman Web YMS", left=Inches(0.8), top=Inches(6.42), width=Inches(11.7), height=Inches(0.48)):
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
    p.text = "Business Analysis, Multi-Dashboard Intelligence &\nStudi Kelayakan Finansial CIDP 35 Ha"
    p.font.size = Pt(21)
    p.font.bold = True
    p.font.color.rgb = C_WHITE
    p.space_before = Pt(4)

    p = tf1.add_paragraph()
    p.text = "KAJIAN STRATEGIS: FINANCIAL FEASIBILITY (CAPEX/OPEX/ROI), BPR AS-IS TO-BE, MULTI-DASHBOARD BI & QA MATRIX"
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
    p.text = "• Naufal Andika Heditya (24D507001012) — Lead Business Analyst & QA\n  Fokus: Financial Feasibility (CAPEX/OPEX/PBP/ROI), Standar AIDC GS1, Kepatuhan CEISA 4.0, & Multi-Dashboard BI.\n\n• Zulfikar Jafarudin Fatah (24D507001001) — Lead System Architect\n  Fokus: Arsitektur Eksekutif Web YMS, Database MySQL, & Integrasi Data."
    p.font.size = Pt(9.5)
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
    p.font.size = Pt(9.5)
    p.font.color.rgb = RGBColor(226, 232, 240)
    p.space_before = Pt(6)

    # Footer Box
    fbox = s1.shapes.add_textbox(Inches(1.1), Inches(6.35), Inches(11.4), Inches(0.7))
    ftf = fbox.text_frame
    ftf.word_wrap = True
    ftf.margin_left = ftf.margin_top = ftf.margin_right = ftf.margin_bottom = 0
    p = ftf.paragraphs[0]
    p.text = "Klien / Evaluator Akademik: Dr. Tigor Franky, S.T., M.T.  •  Mata Kuliah: Teknologi dan Perangkat Lunak Logistik"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = RGBColor(203, 213, 225)
    p = ftf.add_paragraph()
    p.text = "Program Studi S1 Manajemen Logistik  •  Institut Transportasi dan Logistik (ITL) Trisakti  •  Oktober 2026"
    p.font.size = Pt(9.5)
    p.font.color.rgb = RGBColor(148, 163, 184)
    p.space_before = Pt(2)

    # =========================================================================
    # SLIDE 2: KERANGKA KERJA KONSULTAN (FINANCIAL FLOW & 4-LAYER)
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "Kerangka Kerja Konsultan: Pengendalian Aliran Finansial (Financial Flow)")
    add_footer(s2, 2)

    card_w = Inches(3.7)
    card_h = Inches(5.1)

    # Card 1: 3 Aliran Logistik
    create_card(s2, Inches(0.8), Inches(1.65), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(1.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "PERSPEKTIF KONSULTAN BISNIS"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Fokus Pengendalian Financial Flow"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Goods Flow (Fisik):\n  Gerak peti kemas di area 35 Ha (Gate ➔ Yard ➔ Rail Siding).\n\n• Information Flow (Data Telemetri):\n  Status boks, nomor ISO, bobot VGM, koordinat 3D Bay-Row-Tier, suhu reefer, & kode palet SSCC.\n\n• Financial Flow (INTI KONSULTAN):\n  Mengubah peristiwa fisik menjadi penagihan sah (billable event) secara otomatis tanpa kebocoran (Zero Revenue Leakage)."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: 4-Layer IT
    create_card(s2, Inches(4.8), Inches(1.65), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s2.shapes.add_textbox(Inches(5.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "FRAMEWORK DR. TIGOR FRANKY"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Posisi BA dalam 4 Lapisan IT"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Layer 1 (Sensing):\n  Audit utilisasi 26 alat fisik & sensor bernilai miliaran rupiah.\n\n• Layer 2 (Network):\n  Menjamin kontinuitas data penagihan & telemetri tanpa paket hilang.\n\n• Layer 3 (Application YMS):\n  Merancang aturan tarif progresif, aturan penumpukan, dan kepatuhan pabean.\n\n• Layer 4 (Enterprise Integration):\n  Sinkronisasi data ke ERP Odoo Finansial & CEISA 4.0 Bea Cukai."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: Mandat BA & QA
    create_card(s2, Inches(8.8), Inches(1.65), card_w, card_h)
    tb = s2.shapes.add_textbox(Inches(9.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "MANDAT RESMI SILABUS (SLIDE 13)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p.space_before = Pt(0)
    p = tf.add_paragraph()
    p.text = "3 Tugas Pokok BA & QA"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "1. OPEX / ROI Analysis (Kelayakan Bisnis):\n   Menghitung belanja operasional rutin, PBP, NPV, IRR, dan efisiensi biaya terminal.\n\n2. Mockup Dashboard KPI Eksekutif:\n   Visualisasi Business Intelligence penentu keputusan (Throughput, YOR, Dwell Time, Gross Daily Revenue).\n\n3. Dokumen SRS & Penjaminan Mutu (QA):\n   Standardisasi format GS1, pengujian kasus batas (Edge-Cases), dan kepatuhan regulasi."
    p.font.size = Pt(9.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # =========================================================================
    # SLIDE 3: BPR AS-IS VS TO-BE
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "Rekayasa Ulang Proses Bisnis (Business Process Reengineering - BPR)")
    add_footer(s3, 3)

    rows, cols = 7, 4
    left, top, width, height = Inches(0.8), Inches(1.65), Inches(11.7), Inches(4.3)
    table_shape = s3.shapes.add_table(rows, cols, left, top, width, height)
    table = table_shape.table
    table.columns[0].width = Inches(2.2)
    table.columns[1].width = Inches(3.6)
    table.columns[2].width = Inches(4.1)
    table.columns[3].width = Inches(1.8)

    headers = ["Parameter Proses", "Kondisi As-Is (Depo Konvensional)", "Rekomendasi To-Be (CIDP Smart YMS)", "Dampak Efisiensi"]
    for c_idx, h in enumerate(headers):
        cell = table.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = C_WHITE

    bpr_data = [
        ("Waktu Siklus Gerbang", "12 - 18 Menit (Pencatatan manual kertas, stempel tiket, cek boks fisik)", "60 - 90 Detik (Kamera ANPR + OCR boks otomatis, timbang dinamis, e-Pass QR)", "Efisiensi 89% Waktu"),
        ("Alokasi Slot Yard", "Pencarian slot acak; sering salah blok & terjadi box terselip (misplacement)", "100% Terarah Otomatis via Algoritma 3D Bay-Row-Tier & panduan kabin RS", "0% Box Terselip"),
        ("Rata-rata Waktu Inap", "6.8 Hari (Penumpukan tidak terkontrol, notifikasi masa bebas lambat)", "2.3 Hari (Notifikasi otomatis Masa Bebas, SPPB instan, jadwal rel pasti)", "Dwell Time Turun 66%"),
        ("Verifikasi Timbang VGM", "Timbangan luar depo terpisah; sertifikat manual rawan manipulasi", "Terintegrasi Jembatan Timbang Gate 80t; tolak otomatis truk overload >34t", "SOLAS 100% Patuh"),
        ("Penagihan Biaya (Billing)", "Rekap manual 3-5 hari pasca keluar; denda inap sering bocor tidak tertagih", "Real-Time Event-Driven: Invoice terbit detik itu juga ke modul ERP Odoo Finance", "Zero Revenue Leakage"),
        ("Rilis Pabean (Customs)", "Petugas bawa berkas fisik ke pos pabean; waktu tunggu SPPB 24-48 jam", "API CEISA 4.0 DJBC: Rilis SPPB Jalur Hijau <5 menit, alur Behandle terisolasi", "Rilis 95% Lebih Cepat")
    ]

    for r_idx, row in enumerate(bpr_data):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx + 1, c_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_CARD_BG if r_idx % 2 == 0 else C_LIGHT_BG
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.size = Pt(8.5)
            p.font.color.rgb = C_TEXT
            if c_idx == 3:
                p.font.bold = True
                p.font.color.rgb = C_EMERALD

    f_bar = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(6.15), Inches(11.7), Inches(0.65))
    f_bar.fill.solid()
    f_bar.fill.fore_color.rgb = C_BLUE_BG
    f_bar.line.color.rgb = C_BLUE
    f_bar.line.width = Pt(1)
    tf = f_bar.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "DIAGNOSIS KONSULTAN: Rekayasa To-Be memangkas siklus kargo sebesar 66% dan mengeliminasi 100% biaya shifting kontainer sia-sia di lapangan!"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.alignment = PP_ALIGN.CENTER

    # =========================================================================
    # SLIDE 4: STANDAR MUTU GS1 SSCC-18 VS ISO 6346 [LIVE DEMO]
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "Standar Identifikasi Global: GS1 SSCC-18 vs Kontainer ISO 6346")
    add_footer(s4, 4)

    col_w = Inches(5.7)
    card_h = Inches(4.5)

    # Col 1: ISO 6346
    create_card(s4, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s4.shapes.add_textbox(Inches(1.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "IDENTITAS WADAH BAJA LUAR"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "ISO 6346:2022 (Container ID)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Mengidentifikasi FISIK PETI KEMAS:\n  Contoh format: MSKU 918237 4 (11 Karakter).\n  - Kode Pemilik (3 char): MSK = Maersk Line.\n  - Kategori Alat (1 char): U = Freight Container.\n  - Nomor Seri: 6 Angka unik terdaftar di BIC.\n  - Check Digit: 1 Angka hasil kalkulasi Modulo 11.\n\n• Penjaminan Mutu (QA Data):\n  Sistem otomatis memvalidasi rumus Modulo 11 di `pages/scanner.php`. Jika salah satu digit meleset, sistem langsung menandai 'INVALID CHECK DIGIT' dan menolak registrasi."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 2: GS1 SSCC-18
    create_card(s4, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s4.shapes.add_textbox(Inches(7.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "PASPOR UNIT PALET KARGO DI DALAM BOKS"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_EMERALD
    p = tf.add_paragraph()
    p.text = "GS1 SSCC-18 (AI 00 - 18 Digit)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Mengidentifikasi ISI MUATAN LCL / PALET:\n  Dalam 1 kontainer CFS terdapat puluhan palet milik berbagai importir berbeda.\n\n• Anatomi SSCC-18:\n  (00) + Ekstensi (1) + Prefix Perusahaan (7-10) + Seri Palet + Cek Digit Modulo 10.\n\n• Nilai Tambah Operasional:\n  Satu kali scan barcode GS1-128 di rampa dock hidrolik langsung memetakan palet ke dokumen ASN, nomor batch, dan tanggal expired (FEFO) tanpa bongkar kardus fisik."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    add_demo_banner(s4, "[LIVE DEMO 1]: Buka Menu 'Scanner SSCC / GS1' (pages/scanner.php) ➔ Pindai Barcode SSCC-18 & Validasi Cek Digit ISO 6346")

    # =========================================================================
    # SLIDE 5: CAPEX & OPEX
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "Studi Kelayakan Finansial: Struktur CAPEX Rp 250 M & OPEX Rp 24.8 M/Thn")
    add_footer(s5, 5)

    col_w = Inches(5.7)
    card_h = Inches(4.5)

    # Col 1: CAPEX
    create_card(s5, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s5.shapes.add_textbox(Inches(1.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "STRUKTUR INVESTASI AWAL (CAPEX)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Total CAPEX: Rp 249,5 Miliar (± Rp 250 M)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Lahan & Perkerasan Rigid 80t (35 Ha): Rp 130,0 M (52,1%)\n• Armada 3 RS Kalmar + 1 RTG Crane: Rp 55,0 M (22,0%)\n• Sepur Simpan Rel KA (Double Track): Rp 35,0 M (14,0%)\n• Gudang CFS 4.000m² & Hanggar DJBC: Rp 15,0 M (6,0%)\n• Infrastruktur Reefer (300 Plugs): Rp 6,5 M (2,6%)\n• Otomasi Gerbang & Sensor IoT: Rp 4,5 M (1,8%)\n• Software YMS, Cloud & ERP Odoo: Rp 3,5 M (1,4%)\n\nInsight Kunci Konsultan:\nInvestasi IT & IoT hanya 3,2% dari CAPEX, namun mengontrol dan mendigitalkan 96,8% aset fisik terminal lainnya!"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    # Col 2: OPEX
    create_card(s5, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s5.shapes.add_textbox(Inches(7.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "STRUKTUR BIAYA OPERASIONAL (OPEX)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Total OPEX: Rp 24,78 Miliar / Thn (~Rp 2,06 M/Bln)"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Solar Industri & Listrik PLN 380V: Rp 9,44 M / Thn (38,1%)\n  Solar HSD 3 RS (Rp 4,82 M) + Listrik gardu RTG/Reefer (Rp 4,62 M).\n\n• Gaji & Payroll 82 Personil: Rp 6,89 M / Thn (27,8%)\n  8 Operator SIO, 12 Gate, 16 CFS, 8 M&R, 6 IT, 12 Safety, 20 Admin.\n\n• Sewa Jalur Rel KA (TAC KAI Logistik): Rp 5,40 M / Thn (21,8%)\n  Track Access Charge & traksi lokomotif koridor Tanjung Priok.\n\n• Perawatan Mesin M&R & Asuransi/IT: Rp 3,06 M / Thn (12,3%)\n  Ban heavy-duty, pelumas hidrolik, tera timbangan 80t, & cloud."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    s_bar = s5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(6.3), Inches(11.7), Inches(0.55))
    s_bar.fill.solid()
    s_bar.fill.fore_color.rgb = C_AMBER_BG
    s_bar.line.color.rgb = C_AMBER
    s_bar.line.width = Pt(1)
    tf = s_bar.text_frame
    p = tf.paragraphs[0]
    p.text = "RASIO EFISIENSI OPEX: Beban operasional tahunan hanya 32.6% dari total proyeksi pendapatan kotor terminal!"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = RGBColor(180, 83, 9)
    p.alignment = PP_ALIGN.CENTER

    # =========================================================================
    # SLIDE 6: REVENUE STREAMS & ZERO LEAKAGE [LIVE DEMO]
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "Model Pendapatan (Revenue Streams) & Strategi Zero Revenue Leakage")
    add_footer(s6, 6)

    rows, cols = 8, 4
    left, top, width, height = Inches(0.8), Inches(1.65), Inches(11.7), Inches(4.1)
    table_shape = s6.shapes.add_table(rows, cols, left, top, width, height)
    table = table_shape.table
    table.columns[0].width = Inches(2.6)
    table.columns[1].width = Inches(3.2)
    table.columns[2].width = Inches(2.7)
    table.columns[3].width = Inches(3.2)

    headers = ["Jenis Layanan Jasa", "Besaran Tarif Resmi", "Volume Tahunan (Target)", "Estimasi Pendapatan Tahunan"]
    for c_idx, h in enumerate(headers):
        cell = table.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = C_WHITE

    rev_data = [
        ("Lift-On / Lift-Off (Lo-Lo)", "Rp 175rb (20ft) / Rp 275rb (40ft)", "150.000 Box Handling", "Rp 33.750.000.000 (44.4%)"),
        ("Penumpukan Lapangan (Storage)", "Masa I Rp 0 | Masa II Rp 45rb | Masa III Rp 90rb", "90.000 Box Kena Inap", "Rp 10.800.000.000 (14.2%)"),
        ("Bagi Hasil KA Logistik", "Rp 1.500.000 / Gerbong Datar", "6.000 Gerbong KA Barang", "Rp 9.000.000.000 (11.8%)"),
        ("Gudang CFS Stuffing / Stripping", "Rp 450.000 / Box Kontainer LCL", "18.000 Box CFS LCL", "Rp 8.100.000.000 (10.7%)"),
        ("Steker Pendingin Reefer Yard", "Rp 250.000 / Shift (8 Jam)", "25.000 Shift Reefer", "Rp 6.250.000.000 (8.2%)"),
        ("Jembatan Timbang VGM SOLAS", "Rp 50.000 / Truk Masuk Gate", "110.000 Truk Ditimbang", "Rp 5.500.000.000 (7.2%)"),
        ("Jasa Behandle Karantina Pabean", "Rp 350.000 / Box Jalur Merah", "7.500 Box Behandle", "Rp 2.625.000.000 (3.5%)")
    ]

    for r_idx, row in enumerate(rev_data):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx + 1, c_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_CARD_BG if r_idx % 2 == 0 else C_LIGHT_BG
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.size = Pt(8.5)
            p.font.color.rgb = C_TEXT
            if c_idx == 3:
                p.font.bold = True
                p.font.color.rgb = C_EMERALD

    add_demo_banner(s6, "TOTAL GROSS REVENUE: Rp 76.025.000.000 / Thn  |  [LIVE DEMO 2]: Buka Menu 'Billing & Faktur ERP' (pages/billing.php) ➔ Simulasi Auto-Invoice Odoo", top=Inches(5.9))

    # =========================================================================
    # SLIDE 7: EVALUASI INVESTASI FINANSIAL
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "Evaluasi Kelayakan Investasi Finansial: PBP, NPV, IRR & ROI")
    add_footer(s7, 7)

    card_w = Inches(2.75)
    card_h = Inches(4.5)

    # Card 1: Cash Flow
    create_card(s7, Inches(0.8), Inches(1.65), card_w, card_h)
    tb = s7.shapes.add_textbox(Inches(0.95), Inches(1.85), card_w - Inches(0.3), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ARUS KAS OPERASIONAL"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Laba Bersih & EBITDA"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Pendapatan Kotor: Rp 76,02 M\n• Beban OPEX: Rp 24,79 M\n• EBITDA: Rp 51,23 Miliar\n  (Margin EBITDA: 67,4%)\n\n• Depresiasi (20 Thn): Rp 12,50 M\n• Pajak PPh 22%: Rp 8,52 M\n• Laba Bersih (EAT): Rp 30,21 M/thn\n• Arus Kas Bersih (Net Cash Flow):\n  Rp 42,71 Miliar / Tahun"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 2: PBP
    create_card(s7, Inches(3.78), Inches(1.65), card_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s7.shapes.add_textbox(Inches(3.93), Inches(1.85), card_w - Inches(0.3), card_h - Inches(0.4))
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
    p.text = "Formula:\nPBP = CAPEX / Net Cash Flow\nPBP = Rp 249,5 M / Rp 42,71 M\n    = 5,84 Tahun\n\nInterpretasi Bisnis:\nModal Rp 250 Miliar kembali lunas dalam waktu kurang dari 6 tahun.\n\nBatas Aman Pelabuhan:\nStandar kelayakan pelabuhan umumnya 8 - 10 tahun. CIDP 2 tahun lebih cepat!"
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 3: NPV
    create_card(s7, Inches(6.76), Inches(1.65), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s7.shapes.add_textbox(Inches(6.91), Inches(1.85), card_w - Inches(0.3), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "NET PRESENT VALUE (NPV)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "+Rp 12.955.000.000"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "Parameter Analisis:\n• Horizon: 10 Tahun | WACC: 10%\n• Cumulative PV CF: Rp 262,45 M\n• Nilai Investasi: Rp 249,50 M\n\nNPV = +Rp 12,95 Miliar (> 0)\n\nInterpretasi Bisnis:\nNilai NPV Positif membuktikan secara absolut bahwa proyek dry port FEASIBLE (Sangat Layak) dan memberi nilai tambah bersih."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Card 4: IRR & ROI
    create_card(s7, Inches(9.74), Inches(1.65), card_w, card_h)
    tb = s7.shapes.add_textbox(Inches(9.89), Inches(1.85), card_w - Inches(0.3), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "IRR & RETURN ON INVESTMENT"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "IRR: 11.42% | ROI: 121.1%"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Internal Rate of Return (IRR):\n  IRR 11,42% melampaui Hurdle Rate suku bunga kredit komersial bank (8,50%). Sangat bankable!\n\n• Return on Investment (10 Thn):\n  Total laba bersih 10 tahun (Rp 302,1 M) / CAPEX (Rp 249,5 M) = 121,1%.\n\n• Kesimpulan Investor:\n  Kombinasi PBP <6 tahun dan IRR >11% menjadikan proyek sangat aman bagi perbankan."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    e_bar = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(6.3), Inches(11.7), Inches(0.55))
    e_bar.fill.solid()
    e_bar.fill.fore_color.rgb = C_AMBER_BG
    e_bar.line.color.rgb = C_AMBER
    e_bar.line.width = Pt(1)
    tf = e_bar.text_frame
    p = tf.paragraphs[0]
    p.text = "KESIMPULAN STUDI FINANSIAL:  Payback Period 5.84 Tahun  •  NPV Positif +Rp 13 Miliar  •  IRR 11.42%  •  Margin EBITDA 67.4%"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = RGBColor(180, 83, 9)
    p.alignment = PP_ALIGN.CENTER

    # =========================================================================
    # SLIDE 8: DASHBOARD INTELLIGENCE 1 — EXECUTIVE COMMAND CENTER
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "Dashboard Intelligence 1: Executive Command Center (dashboard.php)")
    add_footer(s8, 8)

    col_w = Inches(5.7)
    card_h = Inches(4.5)

    # Col 1: 4 Metrik Pimpinan
    create_card(s8, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s8.shapes.add_textbox(Inches(1.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "4 METRIK KEPUTUSAN DIREKSI (C-LEVEL DSS)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Visibilitas Waktu-Nyata Kinerja Terminal"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "1. Yard Occupancy Rate (YOR) — 68,4%:\n   Ambang batas aman (<80%). Mencegah kemacetan penumpukan dan denda port congestion surcharge ($50/TEU).\n\n2. Throughput Harian — 482 TEU:\n   Mencapai 96,4% dari target harian (500 TEU). Mengukur kecepatan intermodal gate truk dan rail siding KA.\n\n3. Average Dwell Time — 2,3 Hari:\n   Sangat produktif dibanding rata-rata nasional pelabuhan laut (4 - 5 hari), mempercepat perputaran kargo.\n\n4. Gross Daily Revenue — Rp 184,5 Juta:\n   Arus kas harian tertagih real-time dengan rasio penagihan piutang (Collection Rate) mencapai 98,2%."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 2: Analitik Visual BI
    create_card(s8, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_GREEN_BG, border_color=C_EMERALD)
    tb = s8.shapes.add_textbox(Inches(7.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "BUSINESS INTELLIGENCE & ANALITIK DATA"
    p.font.size = Pt(10)
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
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    add_demo_banner(s8, "[LIVE DEMO 3]: Buka Halaman 'Dashboard' (dashboard.php) ➔ Demonstrasikan Gauge YOR 68.4%, Chart.js, & Heatmap Dwell Time")

    # =========================================================================
    # SLIDE 9: DASHBOARD INTELLIGENCE 2 — OPERASIONAL & YARD OPTIMIZATION
    # =========================================================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "Dashboard Intelligence 2: Efisiensi Lapangan & Mitigasi Biaya Operasional")
    add_footer(s9, 9)

    col_w = Inches(5.7)
    card_h = Inches(4.5)

    # Col 1: Gate & Trucking BI
    create_card(s9, Inches(0.8), Inches(1.65), col_w, card_h)
    tb = s9.shapes.add_textbox(Inches(1.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ANALISIS GERBANG & TRUCKING (page=gate & page=trucking)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Pemangkasan Waktu Antrean & Kepatuhan SOLAS"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Gate Turnaround Time (TAT) Analisis:\n  - Depo Konvensional: 15 menit/truk (kemacetan parah di jalan arteri).\n  - CIDP YMS: 90 detik/truk (ANPR + OCR + timbang dinamis).\n  - Nilai Bisnis: Menghemat biaya tunggu armada truk Rp 250.000/jam.\n\n• Validasi Otomatis SOLAS VGM (<34.000 kg):\n  - Sistem menolak otomatis truk overload >34t di gerbang.\n  - Menghilangkan risiko tuntutan hukum maritim internasional dan denda kelebihan muatan jalan raya dari Kemenhub."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    # Col 2: Yard & Stacking BI
    create_card(s9, Inches(6.8), Inches(1.65), col_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s9.shapes.add_textbox(Inches(7.0), Inches(1.85), col_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "ANALISIS YARD & ALAT BERAT (page=yard & page=alat)"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Optimasi Rasio Gerak Derek & Efisiensi Solar"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Optimasi Rasio Shifting Derek (Moves per Box):\n  - Algoritma 3D Bay-Row-Tier menata penumpukan berdasarkan pelabuhan tujuan dan jadwal keberangkatan kereta api.\n  - Rasio shifting turun dari 1.8 moves/box ➔ 1.1 moves/box.\n  - Nilai Bisnis: Menghemat konsumsi Solar HSD Reach Stacker sebesar 22% (efisiensi OPEX Rp 1,06 Miliar per tahun).\n\n• Telemetri GPS Alat Berat (RS-01/02/03 & RTG):\n  - Mencegah idling time operator dan mengukur utilisasi jam kerja riil."
    p.font.size = Pt(9)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(6)

    add_demo_banner(s9, "[LIVE DEMO 4]: Buka Menu 'Gate' (pages/gate.php) ➔ Jalankan Simulasi Truk Lolos vs Truk Overload Ditolak SOLAS")

    # =========================================================================
    # SLIDE 10: DASHBOARD INTELLIGENCE 3 — MITIGASI RISIKO KHUSUS
    # =========================================================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "Dashboard Intelligence 3: Mitigasi Risiko Cold Chain, Pabean & CFS")
    add_footer(s10, 10)

    card_w = Inches(3.7)
    card_h = Inches(4.5)

    # Card 1: Reefer Cold Chain
    create_card(s10, Inches(0.8), Inches(1.65), card_w, card_h)
    tb = s10.shapes.add_textbox(Inches(1.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "COLD CHAIN (page=reefer)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Mitigasi Klaim Rusak Kargo"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• 300 Plugs & IoT LoRaWAN:\n  Monitoring suhu real-time (target -18°C hingga -25°C) setiap 60 detik.\n\n• Nilai Bisnis BA:\n  Mencegah pembusukan produk beku (daging, seafood, vaksin farmasi). Menghindari risiko klaim ganti rugi asuransi kargo bernilai hingga $100.000+ per kontainer.\n\n• Pendapatan: Jasa plugging Rp 250rb/shift."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    # Card 2: Bea Cukai CEISA 4.0
    create_card(s10, Inches(4.8), Inches(1.65), card_w, card_h, bg_color=C_BLUE_BG, border_color=C_BLUE)
    tb = s10.shapes.add_textbox(Inches(5.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "KEPABEANAN (page=customs)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p = tf.add_paragraph()
    p.text = "Kepatuhan PMK 190/2022"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Bifurkasi Hijau vs Merah:\n  - Jalur Hijau (94,2%): Rilis SPPB instan <5 menit via webhook REST API.\n  - Jalur Merah (5,8%): Kunci sistemik CUSTOMS HOLD & Gantry X-Ray 6 MeV penetrasi 300 mm.\n\n• Smart GPS E-Seal:\n  Pengamanan kargo transit rel KA dari Tanjung Priok (tamper alert otomatis jika kawat dipotong)."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    # Card 3: CFS LCL
    create_card(s10, Inches(8.8), Inches(1.65), card_w, card_h)
    tb = s10.shapes.add_textbox(Inches(9.0), Inches(1.85), card_w - Inches(0.4), card_h - Inches(0.4))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "GUDANG CFS (page=cfs)"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_INDIGO
    p = tf.add_paragraph()
    p.text = "Pusat Konsolidasi Ekspor LCL"
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = C_NAVY
    p.space_before = Pt(2)
    p = tf.add_paragraph()
    p.text = "• Fasilitas 4.000 m² & 5 Rampa:\n  Dock hidrolik D1-D5 melayani stripping FCL impor dan konsolidasi ekspor UMKM.\n\n• Digital e-Tally Sheet:\n  Pencatatan kerusakan kargo dan overage/shortage instan via tablet tally.\n\n• Nilai Bisnis: Kontribusi pendapatan jasa CFS Rp 8,10 Miliar per tahun."
    p.font.size = Pt(8.5)
    p.font.color.rgb = C_TEXT
    p.space_before = Pt(4)

    add_demo_banner(s10, "[LIVE DEMO 5]: Buka Menu 'Reefer' (page=reefer) & 'Kepabeanan' (page=customs) ➔ Tunjukkan Telemetri Suhu & Rilis SPPB")

    # =========================================================================
    # SLIDE 11: QA MATRIX & EDGE CASES
    # =========================================================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "Penjaminan Mutu Sistem (QA Testing Matrix) & Kepatuhan SLA")
    add_footer(s11, 11)

    # Table QA Matrix
    rows, cols = 8, 4
    left, top, width, height = Inches(0.8), Inches(1.65), Inches(11.7), Inches(4.3)
    table_shape = s11.shapes.add_table(rows, cols, left, top, width, height)
    table = table_shape.table
    table.columns[0].width = Inches(1.5)
    table.columns[1].width = Inches(3.4)
    table.columns[2].width = Inches(5.0)
    table.columns[3].width = Inches(1.8)

    headers = ["Test ID", "Skenario Uji Operasional", "Perilaku Sistem yang Diharapkan (Expected Result)", "Status Uji"]
    for c_idx, h in enumerate(headers):
        cell = table.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = C_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.size = Pt(9.5)
        p.font.bold = True
        p.font.color.rgb = C_WHITE

    qa_data = [
        ("QA-TC-01", "Truk Gate-In Normal (<34t)", "ANPR + OCR lolos, timbangan validasi SOLAS Pass, palang gate terbuka otomatis (<30 detik)", "PASS (100%)"),
        ("QA-TC-02", "Penolakan Truk Overweight (>34t)", "Sistem picu alarm merah OVERLOAD ALERT, palang terkunci, LED tolak truk masuk", "PASS (100%)"),
        ("QA-TC-03", "Validasi Check Digit ISO 6346", "Algoritma Modulo 11 mendeteksi salah ketik nomor kontainer; pendaftaran ditolak", "PASS (100%)"),
        ("QA-TC-04", "Pemindaian Barcode GS1 SSCC-18", "Engine optik mem-parsing AI (00), memvalidasi digit cek Modulo 10, dan arahkan ke rak CFS", "PASS (100%)"),
        ("QA-TC-05", "Pencegahan Tabrakan Slot Yard", "Algoritma Stacking tolak perintah meletakkan box di Tier 3 tanpa adanya Tier 2 di bawahnya", "PASS (100%)"),
        ("QA-TC-06", "Penguncian Kontainer Jalur Merah", "Kontainer berstatus CUSTOMS HOLD dilarang keras Gate-Out sebelum ada rilis resmi SPPB", "PASS (100%)"),
        ("QA-TC-07", "Kalkulasi Tarif Dwell Inap Progresif", "Menghitung durasi Masa I (Rp 0), Masa II (Rp 45rb), dan Masa III (Rp 90rb) tanpa bocor", "PASS (100%)")
    ]

    for r_idx, row in enumerate(qa_data):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx + 1, c_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = C_CARD_BG if r_idx % 2 == 0 else C_LIGHT_BG
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.size = Pt(8.5)
            p.font.color.rgb = C_TEXT
            if c_idx == 3:
                p.font.bold = True
                p.font.color.rgb = C_EMERALD

    sla_bar = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(6.15), Inches(11.7), Inches(0.65))
    sla_bar.fill.solid()
    sla_bar.fill.fore_color.rgb = C_GREEN_BG
    sla_bar.line.color.rgb = C_EMERALD
    sla_bar.line.width = Pt(1)
    tf = sla_bar.text_frame
    p = tf.paragraphs[0]
    p.text = "PENJAMINAN MUTU NON-FUNGSIONAL: Waktu Respon API 85 ms (<150 ms)  •  Siklus Gate 92s (<120s)  •  Zero Revenue Leakage (0% Bocor)"
    p.font.size = Pt(9)
    p.font.bold = True
    p.font.color.rgb = RGBColor(6, 95, 70)
    p.alignment = PP_ALIGN.CENTER

    # =========================================================================
    # SLIDE 12: KESIMPULAN REKOMENDASI KONSULTAN
    # =========================================================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "Kesimpulan Eksekutif Business Analyst & Rekomendasi Konsultan")
    add_footer(s12, 12)

    card_w = Inches(2.75)
    card_h = Inches(3.4)

    # 4 Pillar Cards
    pillars = [
        ("1. KELAYAKAN FINANSIAL", "Balik Modal 5.8 Tahun", C_BLUE, "• CAPEX Rp 250 M lunas dalam 5,84 tahun.\n• Net Profit Rp 30,2 M/thn.\n• NPV Positif +Rp 13 Miliar.\n• IRR 11,42% di atas bunga bank 8,5%."),
        ("2. EFISIENSI PROSES (BPR)", "Dwell Time Pangkas 66%", C_INDIGO, "• Waktu gate turun 15 mnt ➔ 90 detik.\n• Dwell time turun 6,8 ➔ 2,3 hari.\n• Eliminasi 100% box terselip via alokasi 3D Bay-Row-Tier."),
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
    p.text = "SESI TANYA JAWAB (Q&A)  •  DOSEN PENGAMPU: DR. TIGOR FRANKY, S.T., M.T.  •  TERIMA KASIH"
    p.font.size = Pt(9.5)
    p.font.bold = True
    p.font.color.rgb = C_AMBER
    p.space_before = Pt(4)
    p.alignment = PP_ALIGN.CENTER

    # Save presentation
    output_dir = os.path.dirname(os.path.abspath(__file__))
    output_path = os.path.join(output_dir, "PRESENTASI_BUSINESS_ANALYST_CIDP.pptx")
    prs.save(output_path)
    print(f"Presentation successfully saved to: {output_path}")

if __name__ == "__main__":
    create_ba_presentation()
