/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Multilingual Engine (Indonesian - English)
 * Real-time dynamic language switcher for corporate landing page
 */

const cidpTranslations = {
    id: {
        // TOP NAVBAR & UTILITIES
        nav_about: "Tentang Kami",
        nav_updates: "Berita & Rilis",
        nav_perf: "Kinerja 35 Ha",
        nav_facilities: "Fasilitas & NOC",
        nav_network: "Mitra Ekosistem",
        nav_cta: "Masuk Portal DPMS",
        nav_dashboard: "Buka Dashboard",
        nav_3d: "3D Digital Twin",
        search_btn_title: "Pencarian Cepat",

        // MEGA MENUS
        mega_1_title: "Ekosistem Intermodal",
        mega_1_sub1: "Yard Management (YMS)",
        mega_1_sub1_desc: "Blok A-E, 4-tier stacking & RTG",
        mega_1_sub2: "Rail Siding Pasoso (TMS)",
        mega_1_sub2_desc: "60 TEU rangkaian kereta logistik",
        mega_1_sub3: "CFS & Konsolidasi (WMS)",
        mega_1_sub3_desc: "Cross-docking & barcode palet GS1",
        mega_1_sub4: "Reefer Cold Chain Hub",
        mega_1_sub4_desc: "120 soket telemetri IoT real-time presisi",

        mega_2_title: "Keberlanjutan & Green Port",
        mega_2_sub1: "Reduksi Emisi -76% CO₂",
        mega_2_sub1_desc: "Modal shift dari truk ke kereta api",
        mega_2_sub2: "Elektrifikasi Alat Terminal",
        mega_2_sub2_desc: "E-RTG & efisiensi solar Kalmar",

        mega_3_title: "Kinerja & Laporan",
        mega_3_sub1: "Indikator Dwell Time 1.8 Hari",
        mega_3_sub1_desc: "Efisiensi operasional 2026",
        mega_3_sub2: "SOP & Regulasi CEISA 4.0",
        mega_3_sub2_desc: "Dokumen kepabeanan & kepatuhan K3",

        // MOBILE MENU
        mobile_lang_label: "Pilih Bahasa:",
        mobile_link_about: "Tentang CIDP 35 Ha",
        mobile_link_pillars: "Pilar YMS • TMS • WMS",
        mobile_link_perf: "Kinerja & Reduksi Emisi",
        mobile_link_facilities: "Fasilitas NOC & Gatehouse",
        mobile_link_network: "Jejak Modul & Kemitraan",
        mobile_link_3d: "Simulasi 3D Digital Twin",

        // HERO CAROUSEL
        hero_1_badge: "Hub Intermodal 35 Hektare",
        hero_1_title: "Solusi Logistik <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-200\">Terintegrasi Intermodal</span>",
        hero_1_desc: "Menghubungkan simpul pelabuhan laut, jalur ganda kereta api logistik, dan armada angkutan darat dengan kapasitas throughput 45.000 TEU per tahun demi efisiensi rantai pasok nasional.",
        hero_1_btn1: "Jelajahi Portal DPMS",
        hero_1_btn2: "Pelajari Profil Terminal",

        hero_2_badge: "Konektivitas Rel Pasoso",
        hero_2_title: "Akses Kereta Api <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-cyan-300 to-blue-200\">Langsung ke Tanjung Priok</span>",
        hero_2_desc: "Rangkaian 60 TEU kereta kargo intermodal memotong dwell time pelabuhan dari 3,8 hari menjadi 1,8 hari tanpa hambatan kemacetan jalan arteri.",
        hero_2_btn1: "Buka Modul TMS",
        hero_2_btn2: "Sistem Shunting Kereta",

        hero_3_badge: "CFS Warehouse Modern",
        hero_3_title: "Konsolidasi Pintar & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-green-200\">Gudang CFS Berstandar WMS</span>",
        hero_3_desc: "Stuffing, stripping, dan cross-docking LCL/FCL otomatis dengan pembacaan barcode palet GS1 serta integrasi faktur perpajakan kepabeanan.",
        hero_3_btn1: "Buka Modul WMS",
        hero_3_btn2: "Pelajari Alur Konsolidasi",

        hero_4_badge: "Rantai Pendingin Presisi",
        hero_4_title: "Terminal Reefer & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-300 to-sky-200\">Pemantauan Suhu IoT Real-Time</span>",
        hero_4_desc: "120 soket plug points mandiri dengan sistem deteksi deviasi suhu real-time untuk kargo farmasi, daging, susu, dan buah bernilai tinggi.",
        hero_4_btn1: "Monitoring Reefer",
        hero_4_btn2: "Spesifikasi Terminal",

        hero_5_badge: "Otomasi Gerbang & CEISA 4.0",
        hero_5_title: "Gerbang Otomatis RFID & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-300 to-yellow-200\">Validasi Port Clearance Instan</span>",
        hero_5_desc: "Kamera OCR pelat truk dan nomor kontainer ganda, jembatan timbang 80 ton, dan integrasi Host-to-Host CEISA 4.0 DJBC.",
        hero_5_btn1: "Buka Modul Gate",
        hero_5_btn2: "Standar Keselamatan Gate",

        // HERO BOTTOM TABS
        tab_0: "Solusi Intermodal",
        tab_1: "Rail Siding Pasoso",
        tab_2: "CFS & Konsolidasi",
        tab_3: "Reefer Cold Chain",
        tab_4: "Smart Gate CEISA 4.0",

        // SECTION 3: LATEST UPDATES
        upd_title: "<span class=\"text-orange-600 font-black\">Pembaruan</span> Terkini &amp; Rilis Operasional",
        upd_desc: "Pengumuman kepabeanan, jadwal shunting kereta api, dan pembaruan telemetri operasional.",
        upd_all: "Lihat Semua Dokumen &amp; Rilis",
        upd_f_badge: "Rilis Utama",
        upd_f_cat: "KEPABEANAN & CEISA",
        upd_f_title: "CIDP Resmikan Integrasi API Host-to-Host CEISA 4.0 Bea Cukai untuk Otomasi SPPB Real-Time",
        upd_f_btn: "Buka Akses Konsol Sistem",
        upd_c1_cat: "MAINTENANCE & REPAIR",
        upd_c1_title: "Workshop M&R Rampungkan Kalibrasi Berkala 3 Unit Reach Stacker Kalmar DRG450",
        upd_c1_desc: "Uji beban dinamis dan pemeriksaan sertifikasi twistlock memastikan kesiapan alat berat dalam melayani penumpukan 4-tier berstandar keselamatan internasional.",
        upd_c2_cat: "INFRASTRUKTUR & IOT",
        upd_c2_title: "Peningkatan Data Center Tier-3 CIDP Mencapai Uptime 99.98% untuk Sistem Billing & IoT",
        upd_c2_desc: "Pemantauan telemetri terpadu 26 titik hardware IoT dan sistem redundansi cloud multi-zona menjamin kelancaran operasional gerbang dan penumpukan tanpa henti.",
        upd_c3_cat: "KEMITRAAN EKOSISTEM",
        upd_c3_title: "Pembukaan Loket Satu Atap Bea Cukai, Bank Mandiri, dan Kantor Perwakilan ALFI",
        upd_c3_desc: "Percepat proses pengurusan dokumen ekspor-impor dan pembayaran nota tagihan jasa pelabuhan kering hanya dalam hitungan menit di Gedung Utama.",

        // SECTION 4: TRIDHARMA DPMS
        pil_tag: "TRIDHARMA LOGISTIK CIDP",
        pil_title: "Pilar <span class=\"text-orange-600\">Ekosistem Digital</span> Terpadu",
        pil_desc: "Tiga sistem inti untuk melacak, menggerakkan, dan mengonsolidasi muatan kargo antarmoda secara presisi.",
        pil_all: "Buka Seluruh Modul Terpadu",
        pil_card1_base_tag: "01 • MANAJEMEN LAPANGAN",
        pil_card1_base_title: "Yard Management System",
        pil_card1_hover_tag: "01 • YMS ENGINE",
        pil_card1_hover_title: "Presisi Spasial & Zero Misplacement",
        pil_card1_hover_desc: "Otomasi penataan Blok A hingga E dengan batasan 4-tier stacking, kalkulasi dwell time otomatis, dan pelacakan GPS alat berat Kalmar Reach Stacker dalam visualisasi denah interaktif.",
        pil_card1_hover_stat: "100% Akurasi Slot",
        pil_card1_btn: "Buka Modul",

        pil_card2_base_tag: "02 • LOGISTIK REL PASOSO",
        pil_card2_base_title: "Transport & Shunting KA",
        pil_card2_hover_tag: "02 • TMS ENGINE",
        pil_card2_hover_title: "Jalur Kereta Kargo Langsung ke Priok",
        pil_card2_hover_desc: "Sinkronisasi jadwal rangkaian 60 TEU kereta kargo Pasoso, manifes digital gerbong datar, dan optimasi dwell time intermodal.",
        pil_card2_hover_stat: "1.8 Hari Dwell Time",
        pil_card2_btn: "Buka Modul",

        pil_card3_base_tag: "03 • PERGUDANGAN & LCL",
        pil_card3_base_title: "CFS & Warehousing",
        pil_card3_hover_tag: "03 • WMS ENGINE",
        pil_card3_hover_title: "Konsolidasi Kargo & Cross-Docking",
        pil_card3_hover_desc: "Pengelolaan kargo LCL/FCL cerdas, barcode palet GS1, pemindaian otomatis, dan integrasi faktur perpajakan kepabeanan.",
        pil_card3_hover_stat: "GS1 Barcode Terpadu",
        pil_card3_btn: "Buka Modul",
        pil_hover_hint: "Arahkan kursor untuk rincian",

        // SECTION 5: PERFORMANCE
        perf_tag: "KINERJA OPERASIONAL & HSSE",
        perf_title: "Hasil Kinerja Terminal <br><span class=\"text-amber-300\">Tahun Berjalan 2026</span>",
        perf_desc: "Efisiensi teruji melalui integrasi digital penuh dan koridor kereta kargo antarmoda terpadu.",
        perf_kpi1_title: "Kapasitas Throughput",
        perf_kpi1_unit: "TEU",
        perf_kpi1_sub: "Meningkat 28% dibandingkan tahun lalu",
        perf_kpi2_title: "Rata-Rata Dwell Time",
        perf_kpi2_unit: "Hari",
        perf_kpi2_sub: "Di bawah rata-rata konvensional 3.8 hari",
        perf_kpi3_title: "Reduksi Emisi Gas Buang (CO₂)",
        perf_kpi3_unit: "CO₂",
        perf_kpi3_sub: "Pengalihan ke moda kereta logistik hijau",
        perf_doc_title: "Dokumen Regulasi, SOP & Laporan Resmi",
        perf_doc1_title: "Buku Pedoman Operasional & Tutorial Lengkap DPMS 2026",
        perf_doc1_desc: "Panduan arsitektur sistem, integrasi RFID UHF, dan protokol CEISA 4.0",
        perf_doc2_title: "Kebijakan K3, Keberlanjutan Lingkungan & Dekarbonisasi Pelabuhan",
        perf_doc2_desc: "Standar audit emisi gas buang dan kepatuhan ISPS Code",
        perf_doc3_title: "Formulir Pendaftaran Armada Truk & Kartu Akses RFID Gate",
        perf_doc3_desc: "Registrasi online transporter dan pengemudi",
        perf_doc_btn: "Unduh",

        // SECTION 7: HIGHLIGHTS
        fac_tag: "INFRASTRUKTUR TERMINAL 35 HA",
        fac_title: "Sorotan <span class=\"text-orange-600\">Fasilitas Unggulan</span>",
        fac_desc: "Pilih fasilitas untuk memeriksa spesifikasi teknis dan visualisasi operasional.",
        fac_item1_title: "Data Center Tier-3 & NOC CIDP",
        fac_item1_sub: "Pusat Komando Jaringan & Telemetri",
        fac_item1_desc: "Pusat kontrol 26 perangkat hardware IoT, server redundansi DPMS, dan pengawasan CCTV AI perimeter berkesinambungan.",
        fac_item2_title: "Workshop Maintenance & Repair (M&R)",
        fac_item2_sub: "Bengkel Kalmar & Rekondisi Kontainer",
        fac_item2_desc: "Pemeliharaan alat berat Kalmar Reach Stacker, pengelasan struktur kontainer, dan pencucian kimia standar IICL.",
        fac_item3_title: "Gatehouse Otomatis 4 Lajur & Timbangan",
        fac_item3_sub: "Otomasi Gerbang & Verifikasi Muatan",
        fac_item3_desc: "Empat lajur otomatis dengan kamera OCR nomor kontainer & pelat truk, scanner RFID UHF, dan timbangan 80 ton.",
        fac_item4_title: "Gedung Kantor Utama & Bea Cukai",
        fac_item4_sub: "Pusat Pelayanan Terpadu & Perbankan",
        fac_item4_desc: "Pelayanan satu atap (One Stop Service) terpadu: perwakilan Hanggar Bea Cukai, perbankan, dan loket verifikasi dokumen.",

        // SECTION 8: NETWORK
        net_tag: "JEJAK JARINGAN KAMI",
        net_title: "Modul Sistem DPMS & Jaringan Mitra",
        net_mod_title: "8 Modul Fungsional Conclusion DPMS",
        net_m1_title: "Gate Automation",
        net_m1_desc: "RFID UHF, kamera OCR, timbangan gantry otomatis & cetak e-Ticket SPK.",
        net_m2_title: "Yard Stacking Engine",
        net_m2_desc: "Slot Blok A-E, 4-tier stacking, dan optimasi Port of Discharge.",
        net_m3_title: "Rail Siding Pasoso",
        net_m3_desc: "Rangkaian 60 TEU kereta kargo langsung terhubung Pelabuhan Tanjung Priok.",
        net_m4_title: "CFS Consolidation",
        net_m4_desc: "Stripping/stuffing kargo LCL dan pelacakan barcode palet berstandar GS1.",
        net_m5_title: "Reefer Telematics",
        net_m5_desc: "120 soket plug points mandiri dengan telemetri suhu real-time presisi.",
        net_m6_title: "Equipment Telemetry",
        net_m6_desc: "Telemetri BBM, jam kerja, dan GPS presisi Kalmar DRG450 Reach Stacker.",
        net_m7_title: "Bea Cukai CEISA 4.0",
        net_m7_desc: "Pertukaran data elektronik manifes, SPPB, PEB, PIB instan DJBC.",
        net_m8_title: "Billing ERP Engine",
        net_m8_desc: "Otomasi kalkulasi lift-on/off, penumpukan progresif & faktur e-Tax.",
        net_part_title: "Ekosistem Mitra Logistik Terintegrasi",
        net_p1_role: "Operator Kereta Intermodal",
        net_p2_role: "Pelabuhan Tanjung Priok",
        net_p3_role: "CEISA 4.0 H2H Clearance",
        net_p4_role: "Asosiasi Forwarder Indonesia",

        // SECTION 9: CTA
        cta_badge: "Solusi Dry Port Terdepan di Indonesia",
        cta_title: "Siap Meningkatkan Efisiensi Rantai Pasok Anda Bersama Conclusion DPMS?",
        cta_desc: "Akses konsol kendali operasional, pantau pergerakan kontainer real-time, atau jalankan simulasi 3D Digital Twin sekarang.",
        cta_btn1: "Masuk ke Konsol DPMS",
        cta_btn2: "Buka Simulasi 3D Real-Time",

        // SECTION 10: FOOTER
        foot_desc: "Konsorsium infrastruktur pelabuhan darat 35 Hektare berstandar kelas dunia yang menghubungkan multimoda kereta api logistik, terminal peti kemas digital, serta ekosistem kepabeanan CEISA 4.0 secara berkesinambungan.",
        foot_col1_title: "Standar & Kepatuhan Terakreditasi",
        foot_col2_title: "Solusi Sistem DPMS",
        foot_col3_title: "Tata Kelola & HSSE",
        foot_col4_title: "Pusat Operasi & Komando",
        foot_hq_label: "Terminal & Kantor Pusat:",
        foot_tel_label: "Layanan Informasi Korporat:",
        foot_noc_label: "Pusat Kendali Operasi NOC:",
        foot_status_title: "Kesiapan Operasional Penuh",
        foot_status_desc: "Seluruh subsistem gerbang RFID, reach stacker, rel, dan telemetri IoT beroperasi berkesinambungan dengan siklus multi-shift non-stop.",
        foot_pol1: "Syarat & Ketentuan Layanan",
        foot_pol2: "Kebijakan Privasi & Data",
        foot_pol3: "Whistleblowing System (WBS)",
        foot_pol4: "Kebijakan HSSE & K3L",
        foot_pol5: "Peta Ekosistem Antarmoda",
        foot_security_std: "Sistem Manajemen Keamanan Informasi • ISO/IEC 27001",
        foot_copy: "Hak Cipta &copy; 2026 PT Conclusion Intermodal Dry Port Tbk. Seluruh hak cipta dilindungi undang-undang.",

        // SEARCH MODAL
        search_modal_title: "Pencarian Cepat CIDP DPMS",
        search_placeholder: "Ketik kata kunci: YMS, Reefer, CEISA, SOP, Shunting, atau Tarif...",
        search_popular_label: "Paling Sering Dicari:",
        search_empty_hint: "Masukkan kata kunci di atas untuk mencari data fasilitas, modul, atau dokumen.",
        search_not_found: "Tidak ditemukan hasil yang cocok dengan",

        // LOGIN MODAL
        login_modal_title: "Masuk Portal DPMS",
        login_modal_subtitle: "Konsol Pengendali Logistik & Yard 35 Ha",
        login_email_label: "Alamat Surel / ID Pengguna",
        login_pass_label: "Kata Sandi",
        login_remember: "Ingat saya di perangkat ini",
        login_forgot: "Lupa kata sandi?",
        login_submit: "Masuk ke Konsol DPMS",
        login_demo_label: "PILIH AKUN SIMULASI CEPAT (1-KLIK):"
    },

    en: {
        // TOP NAVBAR & UTILITIES
        nav_about: "About Us",
        nav_updates: "News & Releases",
        nav_perf: "35-Ha Performance",
        nav_facilities: "Facilities & NOC",
        nav_network: "Ecosystem Partners",
        nav_cta: "Access DPMS Portal",
        nav_dashboard: "Open Dashboard",
        nav_3d: "3D Digital Twin",
        search_btn_title: "Quick Search",

        // MEGA MENUS
        mega_1_title: "Intermodal Ecosystem",
        mega_1_sub1: "Yard Management (YMS)",
        mega_1_sub1_desc: "Blocks A-E, 4-tier stacking & RTG",
        mega_1_sub2: "Pasoso Rail Siding (TMS)",
        mega_1_sub2_desc: "60-TEU dedicated freight rail services",
        mega_1_sub3: "CFS & Consolidation (WMS)",
        mega_1_sub3_desc: "Cross-docking & GS1 pallet barcodes",
        mega_1_sub4: "Reefer Cold Chain Hub",
        mega_1_sub4_desc: "120 precision real-time IoT sockets",

        mega_2_title: "Sustainability & Green Port",
        mega_2_sub1: "-76% CO₂ Emission Reductions",
        mega_2_sub1_desc: "Modal freight shift from road to rail",
        mega_2_sub2: "Terminal Equipment Electrification",
        mega_2_sub2_desc: "E-RTG & Kalmar diesel efficiency",

        mega_3_title: "Performance & Reports",
        mega_3_sub1: "1.8-Day Dwell Time Benchmark",
        mega_3_sub1_desc: "Operational throughput efficiency 2026",
        mega_3_sub2: "CEISA 4.0 SOP & Governance",
        mega_3_sub2_desc: "Customs clearance & HSSE compliance",

        // MOBILE MENU
        mobile_lang_label: "Select Language:",
        mobile_link_about: "About CIDP 35-Ha",
        mobile_link_pillars: "YMS • TMS • WMS Pillars",
        mobile_link_perf: "Performance & Carbon Reduction",
        mobile_link_facilities: "NOC Facilities & Gatehouse",
        mobile_link_network: "System Modules & Partners",
        mobile_link_3d: "3D Digital Twin Simulation",

        // HERO CAROUSEL
        hero_1_badge: "35-Hectare Intermodal Hub",
        hero_1_title: "Integrated <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-200\">Intermodal Logistics Solution</span>",
        hero_1_desc: "Seamlessly connecting seaport hubs, double-track freight railways, and trucking fleets with 45,000 TEU annual throughput capacity to maximize national supply chain resilience.",
        hero_1_btn1: "Explore DPMS Portal",
        hero_1_btn2: "Discover Terminal Profile",

        hero_2_badge: "Pasoso Rail Connectivity",
        hero_2_title: "Direct Freight Rail <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-cyan-300 to-blue-200\">To Tanjung Priok Seaport</span>",
        hero_2_desc: "Dedicated 60-TEU intermodal freight rail services reduce seaport dwell time from 3.8 days to 1.8 days without arterial road bottlenecks.",
        hero_2_btn1: "Open TMS Module",
        hero_2_btn2: "Train Shunting System",

        hero_3_badge: "Modern CFS Warehousing",
        hero_3_title: "Smart Consolidation & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-green-200\">WMS-Certified CFS Facility</span>",
        hero_3_desc: "Automated LCL/FCL stuffing, stripping, and cross-docking powered by GS1 pallet barcode scanning and customs tax invoice integration.",
        hero_3_btn1: "Open WMS Module",
        hero_3_btn2: "Explore CFS Workflows",

        hero_4_badge: "Precision Cold Chain",
        hero_4_title: "Reefer Terminal & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-300 to-sky-200\">Real-Time IoT Temperature Telematics</span>",
        hero_4_desc: "120 independent smart plug points featuring automated temperature deviation detection for pharmaceutical, dairy, and high-value perishable cargo.",
        hero_4_btn1: "Reefer Telematics",
        hero_4_btn2: "Terminal Specifications",

        hero_5_badge: "Gate Automation & CEISA 4.0",
        hero_5_title: "Automated RFID Gate & <br><span class=\"text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-300 to-yellow-200\">Instant Port Clearance Validation</span>",
        hero_5_desc: "Dual OCR cameras for container and license plate detection, 80-ton gantry weighbridges, and Host-to-Host CEISA 4.0 customs integration.",
        hero_5_btn1: "Open Gate Module",
        hero_5_btn2: "Gate Safety Standards",

        // HERO BOTTOM TABS
        tab_0: "Intermodal Solution",
        tab_1: "Pasoso Rail Siding",
        tab_2: "CFS & Consolidation",
        tab_3: "Reefer Cold Chain",
        tab_4: "Smart Gate CEISA 4.0",

        // SECTION 3: LATEST UPDATES
        upd_title: "<span class=\"text-orange-600 font-black\">Latest Updates</span> &amp; Operational Releases",
        upd_desc: "Customs regulatory announcements, train shunting schedules, and terminal telematics updates.",
        upd_all: "View All Documents &amp; Releases",
        upd_f_badge: "Featured Release",
        upd_f_cat: "CUSTOMS & CEISA",
        upd_f_title: "CIDP Launches Host-to-Host CEISA 4.0 API Integration for Real-Time Customs SPPB Automation",
        upd_f_btn: "Open System Console Access",
        upd_c1_cat: "MAINTENANCE & REPAIR",
        upd_c1_title: "M&R Workshop Completes Periodic Calibration of 3 Kalmar DRG450 Reach Stackers",
        upd_c1_desc: "Dynamic load testing and twistlock safety certifications ensure heavy equipment readiness for ISO 4-tier stacking operations.",
        upd_c2_cat: "INFRASTRUCTURE & IOT",
        upd_c2_title: "CIDP Tier-3 Data Center Upgrade Achieves 99.98% Uptime for Billing & IoT Ecosystem",
        upd_c2_desc: "Integrated telematics monitoring across 26 IoT hardware units and multi-zone cloud failover ensure non-stop gate and stacking operations.",
        upd_c3_cat: "ECOSYSTEM PARTNERSHIP",
        upd_c3_title: "Grand Opening of Integrated One-Stop Desk for Customs, Bank Mandiri, and ALFI",
        upd_c3_desc: "Accelerate export-import documentation workflows and port terminal service billing within minutes at the Main Administration Building.",

        // SECTION 4: TRIDHARMA DPMS
        pil_tag: "CIDP LOGISTICS TRIDHARMA",
        pil_title: "Pillars of <span class=\"text-orange-600\">Integrated Digital</span> Ecosystem",
        pil_desc: "Three foundational systems designed to track, dispatch, and consolidate intermodal container cargo with precision.",
        pil_all: "Explore All DPMS Modules",
        pil_card1_base_tag: "01 • YARD MANAGEMENT",
        pil_card1_base_title: "Yard Management System",
        pil_card1_hover_tag: "01 • YMS ENGINE",
        pil_card1_hover_title: "Spatial Precision & Zero Misplacement",
        pil_card1_hover_desc: "Automated stacking for Blocks A through E with 4-tier limits, automated dwell time billing, and live GPS tracking of Kalmar reach stackers on an interactive yard map.",
        pil_card1_hover_stat: "100% Slot Accuracy",
        pil_card1_btn: "Open Module",

        pil_card2_base_tag: "02 • PASOSO RAIL LOGISTICS",
        pil_card2_base_title: "Transport & Rail Shunting",
        pil_card2_hover_tag: "02 • TMS ENGINE",
        pil_card2_hover_title: "Dedicated Freight Rail to Seaport",
        pil_card2_hover_desc: "Synchronized schedules for 60-TEU Pasoso container train services, digital flat-car manifests, and intermodal dwell time optimization.",
        pil_card2_hover_stat: "1.8-Day Dwell Time",
        pil_card2_btn: "Open Module",

        pil_card3_base_tag: "03 • WAREHOUSING & LCL",
        pil_card3_base_title: "CFS & Warehousing",
        pil_card3_hover_tag: "03 • WMS ENGINE",
        pil_card3_hover_title: "Cargo Consolidation & Cross-Docking",
        pil_card3_hover_desc: "Smart LCL/FCL cargo management, GS1 pallet barcodes, automated inbound scanning, and seamless customs tax invoice integration.",
        pil_card3_hover_stat: "Integrated GS1 Barcode",
        pil_card3_btn: "Open Module",
        pil_hover_hint: "Hover cursor for details",

        // SECTION 5: PERFORMANCE
        perf_tag: "OPERATIONAL & HSSE PERFORMANCE",
        perf_title: "Terminal Operational Results <br><span class=\"text-amber-300\">Year-to-Date 2026</span>",
        perf_desc: "Proven efficiency achieved through end-to-end digital integration and dedicated intermodal rail corridors.",
        perf_kpi1_title: "Throughput Capacity",
        perf_kpi1_unit: "TEU",
        perf_kpi1_sub: "Up 28% compared to prior year",
        perf_kpi2_title: "Average Dwell Time",
        perf_kpi2_unit: "Days",
        perf_kpi2_sub: "Well below conventional 3.8-day seaport average",
        perf_kpi3_title: "Carbon Emission Reduction (CO₂)",
        perf_kpi3_unit: "CO₂",
        perf_kpi3_sub: "Modal freight shift to green freight rail",
        perf_doc_title: "Regulatory Documents, SOPs & Official Reports",
        perf_doc1_title: "DPMS 2026 Comprehensive Operational & Tutorial Guide",
        perf_doc1_desc: "System architecture guide, UHF RFID integration, and CEISA 4.0 protocols",
        perf_doc2_title: "HSSE, Environmental Sustainability & Port Decarbonization Policy",
        perf_doc2_desc: "Green port emissions audit standards and ISPS Code compliance",
        perf_doc3_title: "Truck Fleet Registration Form & RFID Gate Access Card",
        perf_doc3_desc: "Online registration for transporters and container truck drivers",
        perf_doc_btn: "Download",

        // SECTION 7: HIGHLIGHTS
        fac_tag: "35-HECTARE TERMINAL INFRASTRUCTURE",
        fac_title: "Key Facility <span class=\"text-orange-600\">Highlights</span>",
        fac_desc: "Select a facility to inspect technical specifications and operational visuals.",
        fac_item1_title: "Tier-3 Data Center & CIDP NOC",
        fac_item1_sub: "Network & Telematics Command Center",
        fac_item1_desc: "Central command monitoring 26 IoT hardware units, DPMS high-availability servers, and continuous perimeter AI CCTV surveillance.",
        fac_item2_title: "Workshop Maintenance & Repair (M&R)",
        fac_item2_sub: "Kalmar Heavy Equipment & Container Care",
        fac_item2_desc: "Maintenance depot for Kalmar DRG450 Reach Stackers, container structural repairs, and IICL-standard chemical washing.",
        fac_item3_title: "4-Lane Automated Gatehouse & Weighbridge",
        fac_item3_sub: "Gate Automation & Payload Verification",
        fac_item3_desc: "Four automated lanes featuring container and plate OCR cameras, UHF RFID scanners, and an 80-ton gantry weighbridge.",
        fac_item4_title: "Main Headquarters & Customs Building",
        fac_item4_sub: "Integrated One-Stop Administrative Center",
        fac_item4_desc: "One Stop Service administrative hub housing Customs hangar delegates, banking branches, and shipping document desks.",

        // SECTION 8: NETWORK
        net_tag: "OUR NETWORK FOOTPRINT",
        net_title: "DPMS System Modules & Partner Network",
        net_mod_title: "8 Functional Modules of Conclusion DPMS",
        net_m1_title: "Gate Automation",
        net_m1_desc: "UHF RFID, OCR cameras, automated gantry weighbridge & SPK e-Ticket generation.",
        net_m2_title: "Yard Stacking Engine",
        net_m2_desc: "Block A-E slot allocation, 4-tier stacking, and Port of Discharge optimization.",
        net_m3_title: "Pasoso Rail Siding",
        net_m3_desc: "60-TEU dedicated freight rail services directly linked to Tanjung Priok Seaport.",
        net_m4_title: "CFS Consolidation",
        net_m4_desc: "LCL cargo stripping/stuffing and GS1-standard pallet barcode tracking.",
        net_m5_title: "Reefer Telematics",
        net_m5_desc: "120 independent reefer plug points with precision real-time temperature logs.",
        net_m6_title: "Equipment Telemetry",
        net_m6_desc: "Fuel monitoring, engine hours, and GPS positioning for Kalmar DRG450 Reach Stackers.",
        net_m7_title: "Customs CEISA 4.0",
        net_m7_desc: "Instant electronic exchange of manifests, SPPB, PEB, and PIB with DGCE.",
        net_m8_title: "Billing ERP Engine",
        net_m8_desc: "Automated lift-on/off billing, progressive storage calculations & e-Tax invoices.",
        net_part_title: "Integrated Logistics Partner Ecosystem",
        net_p1_role: "Intermodal Freight Rail Operator",
        net_p2_role: "Tanjung Priok Seaport Operator",
        net_p3_role: "CEISA 4.0 Host-to-Host Clearance",
        net_p4_role: "Indonesian Logistics Forwarders Association",

        // SECTION 9: CTA
        cta_badge: "Indonesia's Premier Dry Port Solution",
        cta_title: "Ready to Elevate Your Supply Chain Efficiency with Conclusion DPMS?",
        cta_desc: "Access the unified operations console, monitor container flows in real-time, or launch the 3D Digital Twin simulation now.",
        cta_btn1: "Access DPMS Console",
        cta_btn2: "Launch Real-Time 3D Simulation",

        // SECTION 10: FOOTER
        foot_desc: "A world-class 35-hectare dry port infrastructure consortium integrating multimodal freight rail, digital container terminals, and continuous CEISA 4.0 customs ecosystem compliance.",
        foot_col1_title: "Accredited Standards & Compliance",
        foot_col2_title: "DPMS System Solutions",
        foot_col3_title: "Governance & HSSE",
        foot_col4_title: "Operations & NOC Headquarters",
        foot_hq_label: "Terminal & Head Office:",
        foot_tel_label: "Corporate Information Services:",
        foot_noc_label: "NOC Operations Command Center:",
        foot_status_title: "Full Operational Readiness",
        foot_status_desc: "All RFID gate, reach stacker, rail siding, and IoT telematics subsystems operate continuously on a 24-hour multi-shift schedule.",
        foot_pol1: "Terms of Service",
        foot_pol2: "Privacy & Data Policy",
        foot_pol3: "Whistleblowing System (WBS)",
        foot_pol4: "HSSE & Safety Policy",
        foot_pol5: "Intermodal Ecosystem Map",
        foot_security_std: "Information Security Management System • ISO/IEC 27001",
        foot_copy: "Copyright &copy; 2026 PT Conclusion Intermodal Dry Port Tbk. All rights reserved.",

        // SEARCH MODAL
        search_modal_title: "CIDP DPMS Quick Search",
        search_placeholder: "Enter keyword: YMS, Reefer, CEISA, SOP, Shunting, or Tariff...",
        search_popular_label: "Popular Searches:",
        search_empty_hint: "Enter keywords above to search terminal facilities, modules, or documents.",
        search_not_found: "No matching results found for",

        // LOGIN MODAL
        login_modal_title: "Access DPMS Portal",
        login_modal_subtitle: "35-Ha Terminal & Yard Control Console",
        login_email_label: "Email Address / User ID",
        login_pass_label: "Password",
        login_remember: "Remember me on this device",
        login_forgot: "Forgot password?",
        login_submit: "Log in to DPMS Console",
        login_demo_label: "SELECT QUICK DEMO ACCOUNT (1-CLICK):"
    }
};

// Facility Highlight Data (Bilingual)
const cidpFacilities = [
    {
        image: 'assets/img/facilities/datacenter_noc.jpg',
        badge: { id: 'PUSAT KENDALI NOC', en: 'NOC COMMAND CENTER' },
        caption: { id: 'Data Center Tier-3 & Network Operations Center (NOC)', en: 'Tier-3 Data Center & Network Operations Center (NOC)' },
        specs: {
            id: ['Tier-3 Uptime 99.98%', '26 Sensor IoT', 'Redundansi Cloud'],
            en: ['Tier-3 Uptime 99.98%', '26 IoT Sensors', 'Multi-Zone Cloud Redundancy']
        }
    },
    {
        image: 'assets/img/facilities/workshop_mr.jpg',
        badge: { id: 'BENGKEL ALAT & KONTAINER', en: 'EQUIPMENT & CONTAINER DEPOT' },
        caption: { id: 'Workshop Maintenance & Repair (M&R) Kalmar', en: 'Kalmar Maintenance & Repair (M&R) Workshop' },
        specs: {
            id: ['Standar IICL', 'Kalmar DRG450 Care', 'Uji Segel ISO'],
            en: ['IICL Standard', 'Kalmar DRG450 Fleet', 'ISO Seal Testing']
        }
    },
    {
        image: 'assets/img/facilities/gatehouse_in.jpg',
        badge: { id: 'GERBANG OTOMATIS & TIMBANGAN', en: 'AUTOMATED GATE & WEIGHBRIDGE' },
        caption: { id: 'Gatehouse Otomatis 4 Lajur OCR & Timbangan Gantry', en: '4-Lane Automated OCR Gatehouse & Gantry Weighbridge' },
        specs: {
            id: ['4 Lajur Cerdas', 'OCR & RFID UHF', 'Jembatan Timbang 80 Ton'],
            en: ['4 Smart Lanes', 'OCR & UHF RFID', '80-Ton Weighbridge']
        }
    },
    {
        image: 'assets/img/facilities/kantor_utama.jpg',
        badge: { id: 'LAYANAN SATU ATAP', en: 'ONE STOP SERVICE' },
        caption: { id: 'Gedung Administrasi & Hanggar Bea Cukai CIDP', en: 'Main Administration Building & Customs Hangar CIDP' },
        specs: {
            id: ['Hanggar Bea Cukai', 'One Stop Service', 'Validasi CEISA 4.0'],
            en: ['Customs Hangar', 'One Stop Service', 'CEISA 4.0 Validation']
        }
    }
];

let activeHighlightIndex = 0;
let currentLanguage = 'id';

/**
 * Switch global application language (ID / EN)
 * @param {string} lang - 'id' or 'en'
 */
function setLanguage(lang) {
    if (!cidpTranslations[lang]) return;
    currentLanguage = lang;
    try {
        localStorage.setItem('cidp_lang', lang);
    } catch(e) {}
    document.documentElement.lang = lang;

    // 1. Update Desktop Language Buttons
    const btnID = document.getElementById('langBtnID');
    const btnEN = document.getElementById('langBtnEN');
    if (btnID && btnEN) {
        if (lang === 'id') {
            btnID.className = 'font-bold text-orange-600 transition-colors';
            btnEN.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal';
        } else {
            btnID.className = 'text-slate-500 hover:text-slate-900 transition-colors font-normal';
            btnEN.className = 'font-bold text-orange-600 transition-colors';
        }
    }

    // 2. Update Mobile Language Buttons
    const mBtnID = document.getElementById('mobileLangBtnID');
    const mBtnEN = document.getElementById('mobileLangBtnEN');
    if (mBtnID && mBtnEN) {
        if (lang === 'id') {
            mBtnID.className = 'font-bold text-orange-600';
            mBtnEN.className = 'text-slate-500 hover:text-slate-900 font-normal';
        } else {
            mBtnID.className = 'text-slate-500 hover:text-slate-900 font-normal';
            mBtnEN.className = 'font-bold text-orange-600';
        }
    }

    // 3. Update DOM text elements
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (cidpTranslations[lang][key] !== undefined) {
            el.textContent = cidpTranslations[lang][key];
        }
    });

    // 4. Update DOM HTML elements (containing formatting tags)
    document.querySelectorAll('[data-i18n-html]').forEach(el => {
        const key = el.getAttribute('data-i18n-html');
        if (cidpTranslations[lang][key] !== undefined) {
            el.innerHTML = cidpTranslations[lang][key];
        }
    });

    // 5. Update Input Placeholders
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        if (cidpTranslations[lang][key] !== undefined) {
            el.placeholder = cidpTranslations[lang][key];
        }
    });

    // 6. Refresh active facility highlight stage
    updateHighlightDisplay(activeHighlightIndex);

    // 7. Dispatch custom event for custom components
    window.dispatchEvent(new CustomEvent('cidp-language-switched', { detail: { lang } }));
}

/**
 * Updates the facility display stage with active language
 */
function updateHighlightDisplay(index) {
    if (!cidpFacilities[index]) return;
    activeHighlightIndex = index;
    const fac = cidpFacilities[index];
    const badge = document.getElementById('highlightBadge');
    const caption = document.getElementById('highlightCaption');
    const specsEl = document.getElementById('highlightSpecs');

    if (badge) badge.innerText = fac.badge[currentLanguage];
    if (caption) caption.innerText = fac.caption[currentLanguage];
    if (specsEl && fac.specs[currentLanguage]) {
        specsEl.innerHTML = fac.specs[currentLanguage]
            .map(s => `<span class="bg-white/15 px-2.5 py-0.5 rounded backdrop-blur-xs font-mono">${s}</span>`)
            .join('');
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    let preferredLang = 'id';
    try {
        const urlParam = new URLSearchParams(window.location.search).get('lang');
        if (urlParam && (urlParam === 'en' || urlParam === 'id')) {
            preferredLang = urlParam;
        } else {
            const saved = localStorage.getItem('cidp_lang');
            if (saved && (saved === 'en' || saved === 'id')) {
                preferredLang = saved;
            }
        }
    } catch(e) {}
    
    setLanguage(preferredLang);
});
