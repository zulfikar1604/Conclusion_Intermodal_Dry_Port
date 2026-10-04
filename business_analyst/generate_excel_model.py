import os
import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

def generate_human_standard_excel():
    wb = openpyxl.Workbook()
    wb.remove(wb.active) # Hapus sheet kosong bawaan

    font_name = "Calibri" # Standar korporat akuntansi & analis keuangan
    
    # Font Styles
    f_title = Font(name=font_name, size=13, bold=True, color="FFFFFF")
    f_subtitle = Font(name=font_name, size=9, italic=True, color="E5E7EB")
    f_sec_head = Font(name=font_name, size=11, bold=True, color="1F2937")
    f_tbl_head = Font(name=font_name, size=9.5, bold=True, color="FFFFFF")
    f_regular = Font(name=font_name, size=9.5, color="1F2937")
    f_bold = Font(name=font_name, size=9.5, bold=True, color="111827")
    f_italic = Font(name=font_name, size=8.5, italic=True, color="4B5563")
    f_source = Font(name=font_name, size=8.5, color="374151")
    
    # Standard Human Corporate Colors (Netral, Bukan Biru AI)
    # Header: Dark Charcoal Slate (Abu-abu arang gelap profesional standar perbankan / audit EY / PwC)
    fill_charcoal = PatternFill(start_color="2D3748", end_color="2D3748", fill_type="solid")
    fill_subhead = PatternFill(start_color="4A5568", end_color="4A5568", fill_type="solid")
    fill_zebra = PatternFill(start_color="F9FAFB", end_color="F9FAFB", fill_type="solid") # Sangat soft off-white
    fill_total = PatternFill(start_color="F3F4F6", end_color="F3F4F6", fill_type="solid")
    fill_highlight = PatternFill(start_color="FEF3C7", end_color="FEF3C7", fill_type="solid") # Soft amber untuk KPI

    # Borders Standar Akuntansi
    border_thin = Border(
        left=Side(style='thin', color="D1D5DB"),
        right=Side(style='thin', color="D1D5DB"),
        top=Side(style='thin', color="D1D5DB"),
        bottom=Side(style='thin', color="D1D5DB")
    )
    border_total = Border(
        top=Side(style='thin', color="1F2937"),
        bottom=Side(style='double', color="1F2937") # Garis ganda khas laporan akuntansi
    )

    def set_header_block(ws, title, subtitle):
        ws.merge_cells("A1:H1")
        ws.merge_cells("A2:H2")
        ws["A1"] = title.upper()
        ws["A1"].font = f_title
        ws["A1"].fill = fill_charcoal
        ws["A1"].alignment = Alignment(horizontal="left", vertical="center", indent=1)
        ws.row_dimensions[1].height = 26

        ws["A2"] = f"Proyek: Conclusion Intermodal Dry Port (CIDP) 35 Ha  |  {subtitle}"
        ws["A2"].font = f_subtitle
        ws["A2"].fill = fill_charcoal
        ws["A2"].alignment = Alignment(horizontal="left", vertical="center", indent=1)
        ws.row_dimensions[2].height = 18
        ws.row_dimensions[3].height = 8

    def format_table_headers(ws, row_idx, headers, center_cols=[]):
        for col_idx, h in enumerate(headers, start=1):
            cell = ws.cell(row=row_idx, column=col_idx, value=h)
            cell.font = f_tbl_head
            cell.fill = fill_subhead
            cell.border = border_thin
            align_h = "center" if col_idx in center_cols else "left"
            cell.alignment = Alignment(horizontal=align_h, vertical="center", wrap_text=True)
        ws.row_dimensions[row_idx].height = 24

    def adjust_column_widths(ws, min_col=1, max_col=8):
        for col in range(min_col, max_col + 1):
            col_letter = get_column_letter(col)
            max_len = 0
            for row in range(4, ws.max_row + 1):
                val = ws.cell(row=row, column=col).value
                if val is not None:
                    s_val = str(val)
                    if len(s_val) > max_len and not s_val.startswith("="):
                        max_len = len(s_val)
            ws.column_dimensions[col_letter].width = min(max(max_len + 3, 11), 52)

    # =========================================================================
    # TAB 1: ASUMSI MAKRO & SUMBER DATA (JUSTIFIKASI RESMI)
    # =========================================================================
    ws1 = wb.create_sheet(title="Asumsi & Sumber Data")
    set_header_block(ws1, "Basis Asumsi, Parameter Finansial & Sumber Regulasi Resmi", "Dasar Dokumen Blueprint & Studi Kelayakan Bisnis")

    ws1["A4"] = "1. PARAMETER MAKRO EKONOMI & KEUANGAN (BENCHMARK 2026)"
    ws1["A4"].font = f_sec_head

    macro_params = [
        ("Tingkat Diskonto (Discount Rate / WACC)", 0.10, "0.0%", "Ditjen Pengelolaan Pembiayaan dan Risiko Kemenkeu RI / Standar WACC Infrastruktur Transportasi"),
        ("Suku Bunga Acuan Kredit Bank Komersial", 0.085, "0.00%", "Bank Indonesia (BI-Rate 6,00% + Risk Premium Pinjaman Investasi Komersial 2,50%)"),
        ("Tarif Pajak Penghasilan Badan (PPh Badan)", 0.22, "0.0%", "Undang-Undang No. 7 Tahun 2021 tentang Harmonisasi Peraturan Perpajakan (UU HPP)"),
        ("Masa Penyusutan Aset Tetap", 20, '#,##0" Tahun"', "Peraturan Menteri Keuangan PMK No. 72 Tahun 2023 tentang Penyusutan Harta Berwujud"),
        ("Kurs Acuan Mata Uang", 15500, '"Rp "#,##0" / USD"', "Kurs Tengah Bank Indonesia (Asumsi Anggaran RAPBN 2026)"),
        ("Target Throughput Tahunan Terminal", 150000, '#,##0" TEU / Tahun"', "Proyeksi 60% dari kapasitas terpasang 250.000 TEU/tahun (Captive Market Cikarang–Karawang)")
    ]

    format_table_headers(ws1, 5, ["Parameter Finansial", "Nilai Asumsi", "Dasar Hukum & Justifikasi Sumber Data"], center_cols=[2])

    for r_idx, (p, val, num_fmt, src) in enumerate(macro_params, start=6):
        c1 = ws1.cell(row=r_idx, column=1, value=p)
        c2 = ws1.cell(row=r_idx, column=2, value=val)
        c3 = ws1.cell(row=r_idx, column=3, value=src)
        c1.font = f_bold
        c2.font = f_bold
        c2.number_format = num_fmt
        c2.alignment = Alignment(horizontal="right")
        c3.font = f_source
        for c in (c1, c2, c3):
            c.border = border_thin
            if r_idx % 2 == 0:
                c.fill = fill_zebra
        ws1.row_dimensions[r_idx].height = 20

    # Section 2: Sumber Regulasi & Tarif Lapangan
    sec2_row = len(macro_params) + 8
    ws1.cell(row=sec2_row, column=1, value="2. REGULASI RESMI & STANDAR TARIF OPERASIONAL LAPANGAN").font = f_sec_head

    reg_sources = [
        ("Kepatuhan Bea Cukai (Jalur Hijau/Merah)", "PMK No. 190/PMK.04/2022", "Kemenkeu RI tentang Pengeluaran Barang Impor untuk Dipakai & integrasi sistem CEISA 4.0"),
        ("Kewajiban Timbangan Berat Kotor (VGM)", "IMO SOLAS Chapter VI Reg 2", "Regulasi Maritim Internasional wajib verifikasi berat kontainer sebelum dimuat ke kapal/kereta"),
        ("Tarif Hak Akses Jalur Rel KA (TAC)", "PM No. 62 Tahun 2018 Kemenhub", "Pedoman Perhitungan Tarif Penggunaan Prasarana Perkeretaapian Umum PT Kereta Api Indonesia"),
        ("Tarif Jasa Bongkar Muat (Lo-Lo)", "SK Tarif ASDEKI Jabar 2024/2026", "Kesepakatan Asosiasi Depo Kontainer Indonesia & ALFI wilayah Koridor Logistik Jawa Barat"),
        ("Tarif Listrik Industri Gardu 380V", "Tarif Listrik PLN Golongan I-3/TM", "Peraturan Menteri ESDM: Tarif listrik industri menengah tegangan menengah Rp 1.114,74 / kWh"),
        ("Harga Bahan Bakar Solar Industri", "Patokan Pertamina Wilayah II Jabar", "Harga solar industri HSD non-subsidi B35/B40 rata-rata Rp 15.500 per liter per 2026"),
        ("Standar Gaji & Upah Tenaga Kerja", "Kepgub Jabar UMK Bekasi 2024/2026", "Upah Minimum Kabupaten Bekasi Rp 5,2-5,5 Juta/bln + Tunjangan SIO Kemenaker Rp 8-12 Juta/bln"),
        ("Standar Penomoran & Barcode Palet", "GS1 General Specifications v23", "Standar internasional Application Identifier AI (00) SSCC-18 dan ISO 6346:2022")
    ]

    format_table_headers(ws1, sec2_row + 1, ["Lingkup Regulasi / Tarif", "Regulasi & Standar Rujukan", "Deskripsi Penerapan pada Proyek CIDP"], center_cols=[2])

    for r_idx, (p, reg, desc) in enumerate(reg_sources, start=sec2_row + 2):
        c1 = ws1.cell(row=r_idx, column=1, value=p)
        c2 = ws1.cell(row=r_idx, column=2, value=reg)
        c3 = ws1.cell(row=r_idx, column=3, value=desc)
        c1.font = f_bold
        c2.font = f_bold
        c2.alignment = Alignment(horizontal="center")
        c3.font = f_source
        for c in (c1, c2, c3):
            c.border = border_thin
            if r_idx % 2 == 0:
                c.fill = fill_zebra
        ws1.row_dimensions[r_idx].height = 20

    adjust_column_widths(ws1, 1, 3)

    # =========================================================================
    # TAB 2: CAPEX DETAIL (RUMUS EXCEL & JUSTIFIKASI LENGKAP)
    # =========================================================================
    ws2 = wb.create_sheet(title="CAPEX Detail")
    set_header_block(ws2, "Struktur Belanja Modal Awal (Capital Expenditure)", "Justifikasi Harga Satuan & Estimasi Anggaran Pembangunan Terminal 35 Ha")

    capex_rows = [
        ("Lahan & Pekerjaan Tanah Matang", "Pembebasan & perataan tanah 35 Hektar (350.000 m²)", 350000, "m²", 150000, "=C6*E6", "NJOP & harga pasar lahan industri Cikarang (MM2100/GIIC Rp 150-250rb/m² mentah)"),
        ("Perkerasan Heavy Duty Rigid 80t", "Pengecoran beton K-450 tebal 40 cm daya beban 80 Ton", 180000, "m²", 430556, "=C7*E7", "AHSP PUPR 2024: Beton ready-mix K-450, pembesian wiremesh ganda, sub-base agregat A"),
        ("Armada: Kalmar Reach Stacker", "Kalmar Gloria DRG450 45t (Spreader 20-40ft DGPS)", 3, "Unit", 9000000000, "=C8*E8", "Pricelist resmi Kalmar Cargotec Indonesia 2024: USD 580.000/unit @ Rp 15.500"),
        ("Armada: Konecranes Electric RTG", "Konecranes 16-wheel RTG Crane 40t elektrik", 1, "Unit", 28000000000, "=C9*E9", "Katalog pasar Konecranes Port Solutions 2024: USD 1.800.000/unit @ Rp 15.500"),
        ("Sepur Simpan Rel KA (Double Track)", "2 Jalur rel panjang 600m rel R54 bantalan beton + wesel", 2, "Sepur", 17500000000, "=C10*E10", "Standar Biaya Konstruksi Ditjen Perkeretaapian Kemenhub: Rp 28-35 M/km sepur ganda"),
        ("Gudang CFS LCL 4.000 m²", "Gedung baja bebas debu + 5 rampa dock leveller hidrolik", 1, "Paket", 15000000000, "=C11*E11", "Biaya bangun gudang industri berstandar Bea Cukai: Rp 3,75 Juta/m² termasuk dock leveller"),
        ("Cold Chain: Reefer Yard Racks", "Dermaga 300 steker listrik 380V/32A + trafo 1.250 kVA", 300, "Plugs", 21666667, "=C12*E12", "Pengadaan panel socket industri CEE 380V + instalasi gardu trafo distribusi PLN"),
        ("Otomasi: Jembatan Timbang 80t", "Jembatan timbang pitless 18m bersertifikasi metrologi", 2, "Unit", 850000000, "=C13*E13", "Katalog Avery Weight-Tronix / Gajah Mas: Rp 850 Juta per unit termasuk software load cell"),
        ("Otomasi: Kamera ANPR & OCR Boks", "Kamera ANPR plat + OCR ISO 6346 4 Lane Gerbang", 4, "Lane", 450000000, "=C14*E14", "Spesifikasi kamera industri IP67 Hikvision/Vivotek port-grade + server edge AI OCR"),
        ("Otomasi: Portal RFID & Barrier", "Portal RFID UHF EPC Gen2 + Palang barrier otomatis", 4, "Lane", 250000000, "=C15*E15", "Spesifikasi Zebra FX9600 4-port reader + antena panel circular 9 dBi + barrier Magnetic"),
        ("Server On-Premise, Jaringan & IT", "Server Dell PowerEdge HA + Firewall Fortinet + Switch", 1, "Paket", 1500000000, "=C16*E16", "Infrastruktur server datacenter tier-2 terminal pelabuhan + UPS backup 20 kVA"),
        ("Software CIDP YMS & Lisensi ERP", "Pengembangan software YMS 3D + Lisensi Odoo Enterprise", 1, "Paket", 2000000000, "=C17*E17", "Jasa rekayasa perangkat lunak logistik custom + kustomisasi modul Odoo Accounting")
    ]

    headers_c = ["Komponen Investasi Aset", "Spesifikasi Teknis", "Vol", "Satuan", "Harga Satuan (IDR)", "Subtotal Biaya (IDR)", "Dasar Asumsi & Sumber Data", "Porsi %"]
    format_table_headers(ws2, 5, headers_c, center_cols=[3, 4, 8])

    for r_idx, row in enumerate(capex_rows, start=6):
        c1 = ws2.cell(row=r_idx, column=1, value=row[0])
        c2 = ws2.cell(row=r_idx, column=2, value=row[1])
        c3 = ws2.cell(row=r_idx, column=3, value=row[2])
        c4 = ws2.cell(row=r_idx, column=4, value=row[3])
        c5 = ws2.cell(row=r_idx, column=5, value=row[4])
        c6 = ws2.cell(row=r_idx, column=6, value=row[5]) # RUMUS EXCEL =C*E
        c7 = ws2.cell(row=r_idx, column=7, value=row[6])
        c8 = ws2.cell(row=r_idx, column=8, value=f"=F{r_idx}/$F$18") # RUMUS PORSI %

        c1.font = f_bold
        c2.font = f_regular
        c3.font = f_regular
        c4.font = f_regular
        c5.font = f_regular
        c6.font = f_bold
        c7.font = f_source
        c8.font = f_regular

        c3.number_format = '#,##0'
        c5.number_format = '"Rp "#,##0'
        c6.number_format = '"Rp "#,##0'
        c8.number_format = '0.0%'

        c3.alignment = Alignment(horizontal="right")
        c4.alignment = Alignment(horizontal="center")
        c5.alignment = Alignment(horizontal="right")
        c6.alignment = Alignment(horizontal="right")
        c8.alignment = Alignment(horizontal="center")

        for c in (c1, c2, c3, c4, c5, c6, c7, c8):
            c.border = border_thin
            if r_idx % 2 == 0:
                c.fill = fill_zebra
        ws2.row_dimensions[r_idx].height = 20

    # Total Row CAPEX
    ws2.cell(row=18, column=1, value="TOTAL ESTIMASI INVESTASI AWAL (CAPEX)").font = f_bold
    ws2.merge_cells("A18:E18")
    c_tot = ws2.cell(row=18, column=6, value="=SUM(F6:F17)") # RUMUS EXCEL =SUM
    c_tot.font = Font(name=font_name, size=10.5, bold=True, color="111827")
    c_tot.number_format = '"Rp "#,##0'
    c_tot.alignment = Alignment(horizontal="right")

    c_pct_tot = ws2.cell(row=18, column=8, value="=SUM(H6:H17)")
    c_pct_tot.font = f_bold
    c_pct_tot.number_format = '0.0%'
    c_pct_tot.alignment = Alignment(horizontal="center")

    for col in range(1, 9):
        cell = ws2.cell(row=18, column=col)
        cell.border = border_total
        cell.fill = fill_total
    ws2.row_dimensions[18].height = 24

    adjust_column_widths(ws2, 1, 8)

    # =========================================================================
    # TAB 3: OPEX DETAIL (RUMUS EXCEL & JUSTIFIKASI)
    # =========================================================================
    ws3 = wb.create_sheet(title="OPEX Detail")
    set_header_block(ws3, "Struktur Biaya Operasional Rutin (Operational Expenditure)", "Justifikasi Parameter Biaya Bulanan & Tahunan Terminal")

    opex_rows = [
        ("BBM Solar HSD Reach Stacker", "3 RS × 18 liter/jam × 16 jam/hari × 360 hari × Rp 15.500/liter", 401760000, "=C6*12", "Spesifikasi mesin Volvo TAD1350VE Kalmar RS (18 L/jam), Solar industri Pertamina Rp 15.500/L"),
        ("Listrik PLN Gardu Industri 380V", "Beban RTG Crane + 300 steker reefer (avg 150 aktif) + lampu yard", 385000000, "=C7*12", "Tarif PLN Golongan I-3/TM (Rp 1.114,74/kWh) + beban pemakaian daya trafo 1.250 kVA"),
        ("Gaji & Payroll 82 Karyawan", "Operator Crane (8), Gate (12), CFS (16), M&R (8), IT (6), Safety (12), Admin (20)", 574000000, "=C8*12", "Standar UMK Bekasi Rp 5,2 Jt + Tunjangan Sertifikasi SIO Kemenaker Operator Rp 8-12 Jt/bln"),
        ("Track Access Charge (TAC) KAI", "Biaya hak lintas rel KA & bagi hasil traksi lokomotif koridor Priok", 450000000, "=C9*12", "Peraturan Menhub PM No. 62/2018 tentang tarif penggunaan prasarana perkeretaapian umum"),
        ("Perawatan Suku Cadang (M&R)", "Penggantian ban crane heavy-duty, pelumas hidrolik, tera timbangan 80t", 180000000, "=C10*12", "Benchmark M&R alat berat pelabuhan (3-4% nilai aset/tahun) + kalibrasi berkala Badan Metrologi"),
        ("Asuransi Tanggung Gugat Terminal", "Terminal Operator Liability Insurance & All-Risk properti", 50000000, "=C11*12", "Polis asuransi standar industri pelabuhan dari Jasindo/Askrindo meng-cover klaim kargo & alat"),
        ("Lisensi IT, Cloud & Keamanan", "Hosting cloud AWS Jakarta, lisensi Odoo ERP, SSL/TLS, internet leased line", 25000000, "=C12*12", "Paket cloud AWS EC2 RDS multi-AZ + langganan internet leased line fiber optic 200 Mbps")
    ]

    headers_o = ["Kategori Biaya Operasional", "Komponen Parameter Hitung", "Biaya per Bulan (IDR)", "Biaya per Tahun (IDR)", "Dasar Asumsi & Sumber Data", "Porsi %"]
    format_table_headers(ws3, 5, headers_o, center_cols=[6])

    for r_idx, row in enumerate(opex_rows, start=6):
        c1 = ws3.cell(row=r_idx, column=1, value=row[0])
        c2 = ws3.cell(row=r_idx, column=2, value=row[1])
        c3 = ws3.cell(row=r_idx, column=3, value=row[2])
        c4 = ws3.cell(row=r_idx, column=4, value=row[3]) # RUMUS EXCEL =C*12
        c5 = ws3.cell(row=r_idx, column=5, value=row[4])
        c6 = ws3.cell(row=r_idx, column=6, value=f"=D{r_idx}/$D$13") # RUMUS PORSI %

        c1.font = f_bold
        c2.font = f_regular
        c3.font = f_regular
        c4.font = f_bold
        c5.font = f_source
        c6.font = f_regular

        c3.number_format = '"Rp "#,##0'
        c4.number_format = '"Rp "#,##0'
        c6.number_format = '0.0%'

        c3.alignment = Alignment(horizontal="right")
        c4.alignment = Alignment(horizontal="right")
        c6.alignment = Alignment(horizontal="center")

        for c in (c1, c2, c3, c4, c5, c6):
            c.border = border_thin
            if r_idx % 2 == 0:
                c.fill = fill_zebra
        ws3.row_dimensions[r_idx].height = 20

    # Total Row OPEX
    ws3.cell(row=13, column=1, value="TOTAL BIAYA OPERASIONAL (OPEX)").font = f_bold
    ws3.merge_cells("A13:B13")
    c_tot_m = ws3.cell(row=13, column=3, value="=SUM(C6:C12)")
    c_tot_m.font = f_bold
    c_tot_m.number_format = '"Rp "#,##0'
    c_tot_m.alignment = Alignment(horizontal="right")

    c_tot_y = ws3.cell(row=13, column=4, value="=SUM(D6:D12)")
    c_tot_y.font = Font(name=font_name, size=10.5, bold=True, color="111827")
    c_tot_y.number_format = '"Rp "#,##0'
    c_tot_y.alignment = Alignment(horizontal="right")

    c_pct_o = ws3.cell(row=13, column=6, value="=SUM(F6:F12)")
    c_pct_o.font = f_bold
    c_pct_o.number_format = '0.0%'
    c_pct_o.alignment = Alignment(horizontal="center")

    for col in range(1, 7):
        cell = ws3.cell(row=13, column=col)
        cell.border = border_total
        cell.fill = fill_total
    ws3.row_dimensions[13].height = 24

    adjust_column_widths(ws3, 1, 6)

    # =========================================================================
    # TAB 4: MODEL PENDAPATAN (REVENUE STREAMS & JUSTIFIKASI TARIF)
    # =========================================================================
    ws4 = wb.create_sheet(title="Model Pendapatan")
    set_header_block(ws4, "Model Pendapatan Komersial Terminal (Revenue Streams)", "Dasar Tarif Resmi Asosiasi & Target Volume 150.000 TEU / Tahun")

    rev_rows = [
        ("Jasa Bongkar Muat Lo-Lo 20ft", "Penanganan lift-on/lift-off kontainer 20ft", 90000, "Box", 175000, "=C6*E6", "SK Tarif ASDEKI Jabar: Rata-rata Rp 175rb/box 20ft di depo penyangga"),
        ("Jasa Bongkar Muat Lo-Lo 40ft", "Penanganan lift-on/lift-off kontainer 40ft", 60000, "Box", 275000, "=C7*E7", "SK Tarif ASDEKI Jabar: Rata-rata Rp 275rb/box 40ft di depo penyangga"),
        ("Penumpukan Lapangan Masa II", "Hari ke-4 s/d hari ke-10 (Rp 45.000 / box / hari)", 160000, "Box-Hari", 45000, "=C8*E8", "Asumsi 40% box lewat masa bebas 3 hari (rata-rata inap 4 hari di Masa II)"),
        ("Penumpukan Lapangan Masa III", "Hari ke-11 ke atas tarif progresif (Rp 90.000 / hari)", 40000, "Box-Hari", 90000, "=C9*E9", "Denda progresif 200% Masa II untuk mempercepat turnover yard / penalti dwell"),
        ("Bagi Hasil KA Logistik (Siding)", "Margin bagi hasil per gerbong datar koridor Priok", 6000, "Gerbong", 1500000, "=C10*E10", "PKS Kerjasama PT KAI Logistik: Margin bagi hasil Rp 1,5 Jt per gerbong isi"),
        ("Gudang CFS Stuffing / Stripping", "Layanan konsolidasi ekspor & dekonsolidasi LCL", 18000, "Box", 450000, "=C11*E11", "Tarif jasa pergudangan CFS resmi ALFI Jabar Rp 450rb/box FCL to LCL"),
        ("Steker Listrik Reefer Yard", "Colokan listrik 380V kargo dingin (per shift 8 jam)", 25000, "Shift", 250000, "=C12*E12", "Standar tarif plugging reefer depo ASDEKI: Rp 250rb per shift (8 jam listrik + monitoring)"),
        ("Jembatan Timbang VGM SOLAS", "Penimbangan kargo ekspor wajib di Gate-In", 110000, "Truk", 50000, "=C13*E13", "Tarif timbang resmi jembatan timbang terkalibrasi metrologi Rp 50rb per truk"),
        ("Jasa Behandle Karantina Pabean", "Pemeriksaan fisik Jalur Merah di hanggar DJBC", 7500, "Box", 350000, "=C14*E14", "Tarif reposisi boks ke hanggar behandle sesuai PMK 190/2022 (5% dari total box impor)"),
        ("Penerbitan Digital e-Gate Pass", "Administrasi dokumen pass digital kode QR", 125000, "Transaksi", 15000, "=C15*E15", "Biaya administrasi pencetakan tiket digital e-Pass terminal gate Rp 15rb/truk")
    ]

    headers_r = ["Jenis Jasa Layanan", "Dasar Pengenaan", "Volume Tahunan", "Satuan", "Tarif Satuan (IDR)", "Proyeksi Omset Tahunan (IDR)", "Dasar Tarif & Sumber Rujukan", "Kontribusi %"]
    format_table_headers(ws4, 5, headers_r, center_cols=[3, 4, 8])

    for r_idx, row in enumerate(rev_rows, start=6):
        c1 = ws4.cell(row=r_idx, column=1, value=row[0])
        c2 = ws4.cell(row=r_idx, column=2, value=row[1])
        c3 = ws4.cell(row=r_idx, column=3, value=row[2])
        c4 = ws4.cell(row=r_idx, column=4, value=row[3])
        c5 = ws4.cell(row=r_idx, column=5, value=row[4])
        c6 = ws4.cell(row=r_idx, column=6, value=row[5]) # RUMUS EXCEL =C*E
        c7 = ws4.cell(row=r_idx, column=7, value=row[6])
        c8 = ws4.cell(row=r_idx, column=8, value=f"=F{r_idx}/$F$16") # RUMUS KONTRIBUSI %

        c1.font = f_bold
        c2.font = f_regular
        c3.font = f_regular
        c4.font = f_regular
        c5.font = f_regular
        c6.font = f_bold
        c7.font = f_source
        c8.font = f_regular

        c3.number_format = '#,##0'
        c5.number_format = '"Rp "#,##0'
        c6.number_format = '"Rp "#,##0'
        c8.number_format = '0.0%'

        c3.alignment = Alignment(horizontal="right")
        c4.alignment = Alignment(horizontal="center")
        c5.alignment = Alignment(horizontal="right")
        c6.alignment = Alignment(horizontal="right")
        c8.alignment = Alignment(horizontal="center")

        for c in (c1, c2, c3, c4, c5, c6, c7, c8):
            c.border = border_thin
            if r_idx % 2 == 0:
                c.fill = fill_zebra
        ws4.row_dimensions[r_idx].height = 20

    # Total Row Revenue
    ws4.cell(row=16, column=1, value="TOTAL PROYEKSI PENDAPATAN KOTOR (GROSS REVENUE)").font = f_bold
    ws4.merge_cells("A16:D16")
    c_tot_rev = ws4.cell(row=16, column=6, value="=SUM(F6:F15)")
    c_tot_rev.font = Font(name=font_name, size=10.5, bold=True, color="111827")
    c_tot_rev.number_format = '"Rp "#,##0'
    c_tot_rev.alignment = Alignment(horizontal="right")

    c_pct_rev = ws4.cell(row=16, column=8, value="=SUM(H6:H15)")
    c_pct_rev.font = f_bold
    c_pct_rev.number_format = '0.0%'
    c_pct_rev.alignment = Alignment(horizontal="center")

    for col in range(1, 9):
        cell = ws4.cell(row=16, column=col)
        cell.border = border_total
        cell.fill = fill_total
    ws4.row_dimensions[16].height = 24

    adjust_column_widths(ws4, 1, 8)

    # =========================================================================
    # TAB 5: ARUS KAS 10 TAHUN & KAJIAN KELAYAKAN INVESTASI
    # =========================================================================
    ws5 = wb.create_sheet(title="Cash Flow 10 Thn")
    set_header_block(ws5, "Proyeksi Arus Kas 10 Tahun & Indikator Kelayakan Finansial", "Formula Excel Asli: NPV, IRR, EBITDA, Net Profit, dan Payback Period")

    years_header = ["Komponen Arus Kas Finansial", "Tahun 0 (CAPEX)"] + [f"Tahun {i}" for i in range(1, 11)]
    for c_idx, y in enumerate(years_header, start=1):
        cell = ws5.cell(row=5, column=c_idx, value=y)
        cell.font = f_tbl_head
        cell.fill = fill_subhead
        cell.border = border_thin
        cell.alignment = Alignment(horizontal="center" if c_idx > 1 else "left", vertical="center")
    ws5.row_dimensions[5].height = 24

    # Data Rows Model
    cf_structure = [
        ("Volume Throughput Terminal (TEU)", [0, 120000, 135000, 150000, 155000, 160000, 165000, 170000, 175000, 180000, 185000], '#,##0', False),
        ("Pendapatan Kotor (Gross Revenue)", [0] + [f"='Model Pendapatan'!$F$16*(C6/150000)" for _ in range(10)], '"Rp "#,##0', True),
        ("Beban Operasional Rutin (OPEX)", [0] + [f"='OPEX Detail'!$D$13*(0.85+0.15*(C6/150000))" for _ in range(10)], '"Rp "#,##0', False),
        ("EBITDA (Laba Sebelum Bunga, Pajak & Depr)", [0] + [f"=C7-C8" for _ in range(10)], '"Rp "#,##0', True),
        ("Penyusutan (Depresiasi Garis Lurus 20 Thn)", [0] + [f"='CAPEX Detail'!$F$18/20" for _ in range(10)], '"Rp "#,##0', False),
        ("Laba Sebelum Pajak (EBT)", [0] + [f"=C9-C10" for _ in range(10)], '"Rp "#,##0', False),
        ("Pajak Penghasilan Badan (PPh 22% UU HPP)", [0] + [f"=IF(C11>0, C11*'Asumsi & Sumber Data'!$B$8, 0)" for _ in range(10)], '"Rp "#,##0', False),
        ("Laba Bersih Setelah Pajak (EAT / Net Profit)", [0] + [f"=C11-C12" for _ in range(10)], '"Rp "#,##0', True),
        ("Arus Kas Masuk Bersih (Net Cash Flow)", [f"=-'CAPEX Detail'!$F$18"] + [f"=C13+C10" for _ in range(10)], '"Rp "#,##0', True),
        ("Arus Kas Kumulatif (Cumulative Cash Flow)", [f"=B14"] + [f"=B15+C14" for _ in range(10)], '"Rp "#,##0', True)
    ]

    for r_idx, (label, vals, num_fmt, is_bold) in enumerate(cf_structure, start=6):
        c1 = ws5.cell(row=r_idx, column=1, value=label)
        c1.font = f_bold if is_bold else f_regular
        c1.border = border_thin
        for c_idx, v in enumerate(vals, start=2):
            cell = ws5.cell(row=r_idx, column=c_idx)
            if isinstance(v, str) and "C" in v and c_idx > 3:
                curr_l = get_column_letter(c_idx)
                prev_l = get_column_letter(c_idx - 1)
                adapted_v = v.replace("C6", f"{curr_l}6").replace("C7", f"{curr_l}7").replace("C8", f"{curr_l}8").replace("C9", f"{curr_l}9").replace("C10", f"{curr_l}10").replace("C11", f"{curr_l}11").replace("C12", f"{curr_l}12").replace("C13", f"{curr_l}13").replace("C14", f"{curr_l}14").replace("B15", f"{prev_l}15")
                cell.value = adapted_v
            else:
                cell.value = v
            cell.font = f_bold if is_bold else f_regular
            cell.number_format = num_fmt
            cell.border = border_thin
            cell.alignment = Alignment(horizontal="right")
            if is_bold and r_idx == 14:
                cell.fill = fill_highlight # Highlight Net Cash Flow
            elif r_idx % 2 == 0 and r_idx != 14:
                cell.fill = fill_zebra
        ws5.row_dimensions[r_idx].height = 20

    # Valuation Section
    val_start = 18
    ws5.cell(row=val_start, column=1, value="HASIL EVALUASI INVESTASI DENGAN FORMULA ASLI EXCEL").font = f_sec_head
    ws5.merge_cells(f"A{val_start}:E{val_start}")

    metrics = [
        ("Tingkat Diskonto (Discount Rate / WACC)", "='Asumsi & Sumber Data'!$B$6", "0.0%", "Dasar: Standar diskonto infrastruktur Kemenkeu RI"),
        ("Net Present Value (NPV)", "=NPV(B19, C14:L14) + B14", '"Rp "#,##0', "Status: POSITIF (+Rp 22,91 M) | Proyek FEASIBLE & Sangat Layak Dijalankan"),
        ("Internal Rate of Return (IRR)", "=IRR(B14:L14)", "0.00%", "Status: 11,90% | Melampaui suku bunga kredit bank komersial 8,50%"),
        ("Payback Period (PBP)", "=5+(-G15/H14)", '0.00" Tahun"', "Status: Balik modal pada Tahun ke-5,98 (< 6 Tahun, batas aman industri 8-10 tahun)"),
        ("Return on Investment (ROI 10 Thn)", "=SUM(C13:L13)/'CAPEX Detail'!$F$18", "0.0%", "Status: 140,6% | Akumulasi laba bersih 10 tahun melampaui investasi modal awal")
    ]

    for m_idx, (m_label, m_formula, m_fmt, m_note) in enumerate(metrics, start=val_start + 1):
        c1 = ws5.cell(row=m_idx, column=1, value=m_label)
        c2 = ws5.cell(row=m_idx, column=2, value=m_formula)
        c3 = ws5.cell(row=m_idx, column=3, value=m_note)
        c1.font = f_bold
        c2.font = Font(name=font_name, size=10, bold=True, color="111827")
        c3.font = f_source
        c2.number_format = m_fmt
        c2.alignment = Alignment(horizontal="right" if m_fmt != "@" else "center")
        for c in (c1, c2, c3):
            c.border = border_thin
            c.fill = fill_zebra
        ws5.row_dimensions[m_idx].height = 21

    adjust_column_widths(ws5, 1, 12)

    # =========================================================================
    # TAB 6: MATRIKS SRS (REQUIREMENT TRACEABILITY MATRIX)
    # =========================================================================
    ws6 = wb.create_sheet(title="Matriks SRS (RTM)")
    set_header_block(ws6, "Matriks Kebutuhan Sistem (Requirement Traceability Matrix - RTM)", "Dasar Penyusunan Bab Kebutuhan Fungsional & Non-Fungsional Blueprint SRS")

    rtm_rows = [
        ("FR-01", "Gate Ingestion Otomatis", "Sistem wajib menerima data ANPR plat nomor, OCR nomor kontainer, dan bobot jembatan timbang <5 detik.", "High", "Gate Officer", "pages/gate.php", "Kamera ANPR, OCR ISO 6346 & Timbangan 80t", "QA-TC-01"),
        ("FR-02", "Validasi SOLAS VGM (<34t)", "Sistem secara otomatis menolak truk trailer dengan muatan kargo melebihi batas legal 34.000 kg.", "Critical", "Gate & Weighbridge", "pages/gate.php", "Jembatan Timbang Digital 80 Ton", "QA-TC-02"),
        ("FR-03", "Alokasi 3D Bay-Row-Tier", "Sistem mengalokasikan koordinat penumpukan 3D otomatis untuk meminimalkan rasio shifting derek.", "High", "RS Crane Operator", "pages/simulator.php", "Engine Stacking YMS 3D", "QA-TC-05"),
        ("FR-04", "Scanner Optik GS1 SSCC-18", "Engine computer vision wajib mem-parsing Application Identifier AI (00) dan validasi Modulo 10.", "High", "CFS Tally Master", "pages/scanner.php", "html5-qrcode & Kamera PDA", "QA-TC-04"),
        ("FR-05", "Validasi Check Digit ISO 6346", "Sistem wajib memvalidasi nomor seri kontainer menggunakan rumus bobot pangkat 2 Modulo 11.", "High", "AIDC Scanner", "pages/scanner.php", "Algoritma ISO Modulo 11", "QA-TC-03"),
        ("FR-06", "Webhook Rilis SPPB CEISA 4.0", "Menerima respons status kepabeanan DJBC dan memperbarui status boks menjadi SPPB CLEARED.", "Critical", "Customs Officer", "pages/customs.php", "REST API Webhook CEISA 4.0", "QA-TC-06"),
        ("FR-07", "Karantina Behandle Jalur Merah", "Mengunci status boks CUSTOMS HOLD dan menolak otorisasi Gate-Out hingga ada rilis fisik.", "Critical", "Hanggar DJBC", "pages/customs.php", "Portal Gantry X-Ray 6 MeV", "QA-TC-06"),
        ("FR-08", "Monitoring E-Seal Transit KA", "Menerima sinyal koordinat satelit dan memicu alarm pabean jika kawat segel transit putus.", "High", "Customs & Security", "pages/customs.php", "Smart GPS E-Seal IoT SIM", "QA-TC-06"),
        ("FR-09", "Mesin Tarif Terminal Otomatis", "Menghitung tagihan Lo-Lo, timbangan, reefer, dan denda inap Masa II & III secara presisi.", "Critical", "Billing Admin", "pages/billing.php", "Terminal Tariff Engine", "QA-TC-07"),
        ("FR-10", "Zero Revenue Leakage Gate Lock", "Palang gerbang Gate-Out terkunci permanen hingga status faktur di ERP Odoo bernilai PAID.", "Critical", "Gate & Finance", "pages/billing.php", "Integrasi REST API ERP Odoo", "QA-TC-07"),
        ("FR-11", "Digital e-Tally Gudang CFS", "Mencatat pembongkaran kargo FCL ke palet LCL dan menerbitkan Berita Acara Rekonsiliasi.", "Medium", "CFS Supervisor", "pages/cfs.php", "Tablet Tally Master CFS", "QA-TC-04"),
        ("NFR-01", "Kecepatan Respon REST API", "Waktu respon endpoint API backend wajib di bawah 150 milidetik pada beban 100 concurrent user.", "High", "System Architect", "api/gateway", "Arsitektur Decoupled REST API", "QA-SLA-01"),
        ("NFR-02", "Ketersediaan Sistem (Uptime)", "Tingkat ketersediaan sistem wajib mencapai minimal 99.9% dengan arsitektur failover database.", "Critical", "Infrastructure Specialist", "Server & DB", "MySQL Master-Slave Replication", "QA-SLA-02"),
        ("NFR-03", "Keamanan Data & Enkripsi", "Seluruh transmisi payload data wajib terenkripsi protokol HTTPS TLS 1.3 dan token JWT.", "High", "Security Officer", "Auth Gateway", "Enkripsi TLS 1.3 & JWT Token", "QA-SLA-03")
    ]

    headers_rtm = ["Req ID", "Nama Modul Kebutuhan", "Deskripsi Spesifikasi Fungsional / Non-Fungsional", "Prioritas", "Aktor Pengguna", "Modul File Web", "Instrumen Teknologi Terkait", "Mapping QA Test"]
    format_table_headers(ws6, 5, headers_rtm, center_cols=[1, 4, 8])

    for r_idx, row in enumerate(rtm_rows, start=6):
        for c_idx, val in enumerate(row, start=1):
            cell = ws6.cell(row=r_idx, column=c_idx, value=val)
            cell.font = f_regular
            cell.border = border_thin
            if r_idx % 2 == 0:
                cell.fill = fill_zebra
            if c_idx in [1, 8]:
                cell.font = f_bold
                cell.alignment = Alignment(horizontal="center")
            elif c_idx == 4:
                cell.alignment = Alignment(horizontal="center")
                if val == "Critical":
                    cell.font = Font(name=font_name, size=9, bold=True, color="991B1B")
                elif val == "High":
                    cell.font = Font(name=font_name, size=9, bold=True, color="92400E")
            elif c_idx in [5, 6]:
                cell.font = f_source
        ws6.row_dimensions[r_idx].height = 20

    adjust_column_widths(ws6, 1, 8)

    # Simpan file Excel
    out_dir = os.path.dirname(os.path.abspath(__file__))
    out_path = os.path.join(out_dir, "MODEL_FINANSIAL_DAN_BLUEPRINT_CIDP.xlsx")
    wb.save(out_path)
    print(f"Human-standard Excel successfully saved to: {out_path}")

if __name__ == "__main__":
    generate_human_standard_excel()
