<?php
/**
 * Conclusion Intermodal Dry Port (CIDP)
 * Multilingual System Engine (Indonesian & English)
 * File: config/i18n.php
 *
 * Provides translation dictionaries and localization helpers for DPMS Dashboard & Modules.
 */

if (!defined('CIDP_I18N_LOADED')) {
    define('CIDP_I18N_LOADED', true);
}

// Ensure active language is determined
if (!isset($current_lang)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'])) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('cidp_lang', $_GET['lang'], time() + (86400 * 30), "/");
    } elseif (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = $_COOKIE['cidp_lang'] ?? 'id';
    }
    $current_lang = $_SESSION['lang'] ?? 'id';
}

$GLOBALS['cidp_lang_dict'] = [
    'id' => [
        // Clusters
        'cluster_executive' => 'Executive & Map',
        'cluster_tms'       => 'TMS (Transport)',
        'cluster_yms'       => 'YMS (Yard)',
        'cluster_wms'       => 'WMS (Warehouse / CFS)',
        'cluster_finance'   => 'Compliance & Finance',

        // Menu Items
        'menu_beranda'   => 'Cockpit Eksekutif',
        'menu_twin'      => 'Digital Twin 2D (Live)',
        'menu_denah'     => 'Denah Master 35 Ha',
        'menu_simulator' => 'Simulator 3D Hub',
        'menu_gate'      => 'Gate & Timbangan VGM',
        'menu_trucking'  => 'Trucking & Shunting',
        'menu_intermodal'=> 'Rail Siding KA Pasoso',
        'menu_yard'      => 'Yard & Stacking Block',
        'menu_alat'      => 'Alat Berat & RTG GPS',
        'menu_kontainer' => 'Inventori Kontainer',
        'menu_reefer'    => 'Reefer Cold Chain',
        'menu_cfs'       => 'Container Freight Station',
        'menu_customs'   => 'Bea Cukai CEISA 4.0',
        'menu_billing'   => 'Billing & ERP Odoo',
        'menu_scanner'   => 'Scanner Optik AIDC',
        'menu_settings'  => 'Pengaturan Global',

        // Header & Global Shell
        'search_placeholder'   => 'Cari kontainer (Ctrl+K)...',
        'search_quick_title'   => 'Pintasan Akses Cepat',
        'search_esc'           => 'Esc untuk tutup',
        'fullscreen_title'     => 'Layar Penuh (F11)',
        'live_clock_title'     => 'Waktu Nyata Operasional Terminal CIDP (WIB)',
        'lang_choice_title'    => 'Pilihan Bahasa Sistem (ID / EN)',
        'notif_title'          => 'Notifikasi Sistem Yard',
        'notif_new_badge'      => '4 Baru',
        'notif_mark_read'      => 'Tandai Dibaca',
        'notif_tab_all'        => 'Semua',
        'notif_tab_logistics'  => 'Logistik',
        'notif_tab_customs'    => 'Bea Cukai',
        'notif_tab_finance'    => 'Finansial',
        'notif_view_all'       => 'Buka Log Notifikasi Lengkap',
        'quick_actions_title'  => 'Aksi Cepat Operasional',
        'profile_role_lead'    => 'Lead System Architect',
        'profile_eval_mode'    => 'Mode Simulasi Hak Akses',
        'profile_role_rs'      => 'Operator RS',
        'profile_role_driver'  => 'Supir Armada',
        'profile_role_shipper' => 'Klien Shipper',
        'profile_settings'     => 'Pengaturan Akun & Sistem',
        'profile_sim_panel'    => 'Panel Simulasi IoT & Sensor',
        'profile_site_plan'    => 'Site Plan Denah Terminal 35 Ha',
        'profile_logout'       => 'Keluar dari Sistem',
        'profile_db_status'    => 'DB Terhubung',

        // Logout Modal
        'logout_modal_title'   => 'Konfirmasi Keluar Sistem',
        'logout_modal_desc'    => 'Apakah Anda yakin ingin mengakhiri sesi kerja eksekutif di portal CIDP?',
        'logout_cancel'        => 'Batal',
        'logout_confirm'       => 'Ya, Keluar',

        // Sidebar Footer Card
        'sidebar_pattern_kawung'=> 'Kawung Maritim',
        'sidebar_pattern_parang'=> 'Parang Bahari',
        'sidebar_switch_btn'    => 'Ubah',
        'sidebar_consultant'    => 'Conclusion Consultant',
        'sidebar_platform'      => 'Platform Dry Port 35 Ha',
        'sidebar_online'        => 'ONLINE',

        // Cockpit Eksekutif (pages/beranda.php)
        'tower_title'           => 'Enterprise Executive Control Tower • CIDP 35 Hectares',
        'tower_subtitle'        => 'Conclusion Intermodal DPMS • Orkestrasi Multimoda Yard 35 Ha • Rel Siding Pasoso • CFS Gudang • CEISA 4.0',
        'badge_live_35ha'       => 'LIVE 35 HA',
        'badge_op_sync'         => 'DPMS OPERATIONAL SYNC',
        'badge_scada'           => 'SCADA 99.8%',
        'chip_siding_ready'     => 'Siding KA: Track S-01 Ready',
        'chip_gate_anpr'        => 'Gate ANPR',
        'chip_cfs_rampa'        => 'CFS Rampa',
        'chip_reefer_plug'      => 'Reefer Plug',
        'chip_gis_denah'        => 'GIS Denah',

        // KPI Cards
        'kpi_yard_stacking'     => 'YARD STACKING',
        'kpi_yard_sub'          => '/ 12.500 TEU',
        'kpi_yard_trend'        => '+4.2% (24j)',

        'kpi_gate_turnaround'   => 'GATE TURNAROUND',
        'kpi_gate_target'       => 'Target INSA ≤ 20m',
        'kpi_gate_trucks'       => 'Truk Aktif',
        'kpi_gate_vbs'          => 'VBS 98% On-Time',

        'kpi_rail_siding'       => 'SIDING KA INTERMODAL',
        'kpi_rail_sub'          => 'KA 2518 ke Priok',
        'kpi_rail_track'        => 'Track S-01 (600m)',
        'kpi_rail_eta'          => 'ETA 16:45 WIB',

        'kpi_reefer_chain'      => 'REEFER COLD CHAIN',
        'kpi_reefer_plugs'      => 'Plugs Terpasang',
        'kpi_reefer_lora'       => 'LoRa 868MHz',
        'kpi_reefer_haccp'      => 'HACCP 98.8% OK',

        'kpi_customs_ceisa'     => 'BEA CUKAI CEISA 4.0',
        'kpi_customs_cleared'   => 'Box Cleared',
        'kpi_customs_release'   => 'SPPB Auto Release',
        'kpi_customs_xray'      => 'Hi-Co X-Ray OK',

        'kpi_cfs_billing'       => 'CFS & BILLING',
        'kpi_cfs_jobs'          => 'Jobs Aktif',
        'kpi_cfs_ramps'         => 'Rampa D1–D5 Aktif',
        'kpi_cfs_omzet'         => 'Omzet 7 Hari',
        'kpi_cfs_paid'          => 'Paid',

        // Pipeline Conveyor Ribbon
        'pipeline_title'        => 'Multimodal Logistics Pipeline Conveyor Ribbon (6-Stage End-to-End Visual Workflow)',
        'pipeline_desc'         => 'Alur kargo terpadu 35 Hektar dari gerbang ANPR hingga pemberangkatan kereta api intermodal ke Pelabuhan Tanjung Priok.',
        'pipeline_active_badge' => 'Pipeline 100% Aktif',
        'step1_name'            => '1. Gate In ANPR',
        'step1_desc'            => 'OCR plat & timbangan VGM',
        'step2_name'            => '2. Buffer Yard',
        'step2_desc'            => 'Pemeriksaan dokumen & seal',
        'step3_name'            => '3. Stacking Blok',
        'step3_desc'            => 'RTG / Reach Stacker slot',
        'step4_name'            => '4. Kliring CEISA',
        'step4_desc'            => 'SPPB Bea Cukai otomatis',
        'step5_name'            => '5. CFS Stripping',
        'step5_desc'            => 'Cross-docking & barcode palet',
        'step6_name'            => '6. KA Priok Siding',
        'step6_desc'            => 'Pemberangkatan 60 TEU',

        // Operations Section Titles
        'ops_stream_title'      => 'Aliran Peristiwa Yard Real-Time',
        'ops_stream_desc'       => 'Pantau pergerakan fisik alat berat, kontainer, dan gerbang secara langsung',
        'ops_consignments'      => 'Konsinyasi Terkini',
        'ops_yard_dist'         => 'Distribusi Yard per Blok',
        'ops_equipment'         => 'Telemetri Alat Berat Aktif',
        'ops_dwell_time'        => 'Tren Dwell Time Mingguan',

        // Digital Twin 2D
        'twin_title'            => 'Digital Twin 2D Intermodal Dry Port (Real-Time Console)',
        'twin_desc'             => 'Konsol monitoring 3 pilar: Aliran Fisik, Aliran Informasi, dan Aliran Finansial secara real-time via Server-Sent Events.',
        'twin_flow_physical'    => 'Aliran 1: Fisik & Spasial (Material)',
        'twin_flow_info'        => 'Aliran 2: Informasi & Regulasi (Data/IoT)',
        'twin_flow_finance'     => 'Aliran 3: Finansial & Billing (ERP/Odoo)',
        'twin_sim_label'        => 'Simulator Sinyal Hardware (IoT / OCR / PLC / CEISA)',
        'twin_sim_full'         => '▶ Jalankan Full Siklus (1 - 6)',
        'twin_step_1'           => '[1] Gate In',
        'twin_step_2'           => '[2] Timbang',
        'twin_step_3'           => '[3] Menuju Blok',
        'twin_step_4'           => '[4] Lift-Off',
        'twin_step_5'           => '[5] Stacking',
        'twin_step_6'           => '[6] Bayar & Out',
    ],

    'en' => [
        // Clusters
        'cluster_executive' => 'Executive & Map',
        'cluster_tms'       => 'TMS (Transport)',
        'cluster_yms'       => 'YMS (Yard)',
        'cluster_wms'       => 'WMS (Warehouse / CFS)',
        'cluster_finance'   => 'Compliance & Finance',

        // Menu Items
        'menu_beranda'   => 'Executive Cockpit',
        'menu_twin'      => 'Digital Twin 2D (Live)',
        'menu_denah'     => '35 Ha Master Plan',
        'menu_simulator' => '3D Simulator Hub',
        'menu_gate'      => 'Gate & VGM Weighbridge',
        'menu_trucking'  => 'Trucking & Shunting',
        'menu_intermodal'=> 'Pasoso Rail Siding',
        'menu_yard'      => 'Yard & Stacking Blocks',
        'menu_alat'      => 'Heavy Equipment & RTG GPS',
        'menu_kontainer' => 'Container Inventory',
        'menu_reefer'    => 'Reefer Cold Chain Hub',
        'menu_cfs'       => 'Container Freight Station',
        'menu_customs'   => 'Customs CEISA 4.0',
        'menu_billing'   => 'Billing & ERP Odoo',
        'menu_scanner'   => 'Optical AIDC Scanner',
        'menu_settings'  => 'Global Settings',

        // Header & Global Shell
        'search_placeholder'   => 'Search container (Ctrl+K)...',
        'search_quick_title'   => 'Quick Access Shortcuts',
        'search_esc'           => 'Esc to close',
        'fullscreen_title'     => 'Fullscreen (F11)',
        'live_clock_title'     => 'Real-Time CIDP Terminal Operations (WIB)',
        'lang_choice_title'    => 'System Language Preference (ID / EN)',
        'notif_title'          => 'Yard System Notifications',
        'notif_new_badge'      => '4 New',
        'notif_mark_read'      => 'Mark as Read',
        'notif_tab_all'        => 'All',
        'notif_tab_logistics'  => 'Logistics',
        'notif_tab_customs'    => 'Customs',
        'notif_tab_finance'    => 'Finance',
        'notif_view_all'       => 'Open Full Notification Log',
        'quick_actions_title'  => 'Operational Quick Actions',
        'profile_role_lead'    => 'Lead System Architect',
        'profile_eval_mode'    => 'Access Rights Simulation Mode',
        'profile_role_rs'      => 'RS Operator',
        'profile_role_driver'  => 'Fleet Driver',
        'profile_role_shipper' => 'Shipper Client',
        'profile_settings'     => 'Account & System Settings',
        'profile_sim_panel'    => 'IoT & Sensor Simulation Panel',
        'profile_site_plan'    => '35 Ha Terminal Site Plan',
        'profile_logout'       => 'Sign Out from System',
        'profile_db_status'    => 'DB Connected',

        // Logout Modal
        'logout_modal_title'   => 'Confirm Sign Out',
        'logout_modal_desc'    => 'Are you sure you want to end your executive session in the CIDP portal?',
        'logout_cancel'        => 'Cancel',
        'logout_confirm'       => 'Yes, Sign Out',

        // Sidebar Footer Card
        'sidebar_pattern_kawung'=> 'Maritime Kawung',
        'sidebar_pattern_parang'=> 'Oceanic Parang',
        'sidebar_switch_btn'    => 'Switch',
        'sidebar_consultant'    => 'Conclusion Consultant',
        'sidebar_platform'      => '35 Ha Dry Port Platform',
        'sidebar_online'        => 'ONLINE',

        // Cockpit Eksekutif (pages/beranda.php)
        'tower_title'           => 'Enterprise Executive Control Tower • CIDP 35 Hectares',
        'tower_subtitle'        => 'Conclusion Intermodal DPMS • Multimodal Yard Orchestration 35 Ha • Pasoso Rail Siding • CFS Warehouse • CEISA 4.0',
        'badge_live_35ha'       => 'LIVE 35 HA',
        'badge_op_sync'         => 'DPMS OPERATIONAL SYNC',
        'badge_scada'           => 'SCADA 99.8%',
        'chip_siding_ready'     => 'Rail Siding: Track S-01 Ready',
        'chip_gate_anpr'        => 'ANPR Gate',
        'chip_cfs_rampa'        => 'CFS Ramp',
        'chip_reefer_plug'      => 'Reefer Plugs',
        'chip_gis_denah'        => 'GIS Master Map',

        // KPI Cards
        'kpi_yard_stacking'     => 'YARD STACKING',
        'kpi_yard_sub'          => '/ 12,500 TEU',
        'kpi_yard_trend'        => '+4.2% (24h)',

        'kpi_gate_turnaround'   => 'GATE TURNAROUND',
        'kpi_gate_target'       => 'INSA Target ≤ 20m',
        'kpi_gate_trucks'       => 'Active Trucks',
        'kpi_gate_vbs'          => 'VBS 98% On-Time',

        'kpi_rail_siding'       => 'INTERMODAL RAIL SIDING',
        'kpi_rail_sub'          => 'Train 2518 to Priok',
        'kpi_rail_track'        => 'Track S-01 (600m)',
        'kpi_rail_eta'          => 'ETA 16:45 WIB',

        'kpi_reefer_chain'      => 'REEFER COLD CHAIN',
        'kpi_reefer_plugs'      => 'Plugs Connected',
        'kpi_reefer_lora'       => 'LoRa 868MHz Precision',
        'kpi_reefer_haccp'      => 'HACCP 98.8% OK',

        'kpi_customs_ceisa'     => 'CUSTOMS CEISA 4.0',
        'kpi_customs_cleared'   => 'Boxes Cleared',
        'kpi_customs_release'   => 'SPPB Auto Release',
        'kpi_customs_xray'      => 'Hi-Co X-Ray OK',

        'kpi_cfs_billing'       => 'CFS & BILLING',
        'kpi_cfs_jobs'          => 'Active Jobs',
        'kpi_cfs_ramps'         => 'Ramps D1–D5 Active',
        'kpi_cfs_omzet'         => '7-Day Revenue',
        'kpi_cfs_paid'          => 'Paid',

        // Pipeline Conveyor Ribbon
        'pipeline_title'        => 'Multimodal Logistics Pipeline Conveyor Ribbon (6-Stage End-to-End Visual Workflow)',
        'pipeline_desc'         => 'Integrated 35-hectare cargo pipeline from ANPR gate to intermodal freight train departure towards Tanjung Priok Port.',
        'pipeline_active_badge' => 'Pipeline 100% Active',
        'step1_name'            => '1. Gate In ANPR',
        'step1_desc'            => 'Plate OCR & VGM Weighbridge',
        'step2_name'            => '2. Buffer Yard',
        'step2_desc'            => 'Document & seal verification',
        'step3_name'            => '3. Block Stacking',
        'step3_desc'            => 'RTG / Reach Stacker slotting',
        'step4_name'            => '4. CEISA Clearance',
        'step4_desc'            => 'Automated Customs SPPB release',
        'step5_name'            => '5. CFS Stripping',
        'step5_desc'            => 'Cross-docking & pallet barcode',
        'step6_name'            => '6. Priok Rail Siding',
        'step6_desc'            => '60 TEU train departure',

        // Operations Section Titles
        'ops_stream_title'      => 'Real-Time Yard Event Stream',
        'ops_stream_desc'       => 'Monitor live physical movements of equipment, containers, and terminal gates',
        'ops_consignments'      => 'Latest Consignments',
        'ops_yard_dist'         => 'Yard Distribution by Block',
        'ops_equipment'         => 'Active Equipment Telemetry',
        'ops_dwell_time'        => 'Weekly Dwell Time Trends',

        // Digital Twin 2D
        'twin_title'            => 'Intermodal Dry Port 2D Digital Twin (Real-Time Console)',
        'twin_desc'             => '3-Pillar real-time monitoring console: Physical Flow, Information Flow, and Financial Flow via Server-Sent Events.',
        'twin_flow_physical'    => 'Flow 1: Physical & Spatial (Material)',
        'twin_flow_info'        => 'Flow 2: Information & Regulatory (Data/IoT)',
        'twin_flow_finance'     => 'Flow 3: Financial & Billing (ERP/Odoo)',
        'twin_sim_label'        => 'Hardware Signal Simulator (IoT / OCR / PLC / CEISA)',
        'twin_sim_full'         => '▶ Run Full Cycle (1 - 6)',
        'twin_step_1'           => '[1] Gate In',
        'twin_step_2'           => '[2] Weighbridge',
        'twin_step_3'           => '[3] To Block',
        'twin_step_4'           => '[4] Lift-Off',
        'twin_step_5'           => '[5] Stacking',
        'twin_step_6'           => '[6] Settle & Out',
    ]
];

/**
 * Localization helper: retrieves translated text by key
 */
function __($key, $default = null) {
    global $current_lang, $cidp_lang_dict;
    $lang = $current_lang ?? 'id';
    if (!isset($cidp_lang_dict[$lang])) {
        $lang = 'id';
    }
    if (isset($cidp_lang_dict[$lang][$key])) {
        return $cidp_lang_dict[$lang][$key];
    }
    if (isset($cidp_lang_dict['id'][$key])) {
        return $cidp_lang_dict['id'][$key];
    }
    return $default ?? $key;
}

/**
 * Alias for __()
 */
function t($key, $default = null) {
    return __($key, $default);
}

/**
 * Returns localized menu clusters based on active language
 */
function get_localized_menu_clusters($lang = null): array {
    global $current_lang;
    $lang = $lang ?? $current_lang ?? 'id';

    return [
        __('cluster_executive') => [
            'beranda'   => ['icon' => 'fa-house', 'label' => __('menu_beranda')],
            'twin'      => ['icon' => 'fa-satellite-dish', 'label' => __('menu_twin')],
            'denah'     => ['icon' => 'fa-map-location-dot', 'label' => __('menu_denah')],
            'simulator' => ['icon' => 'fa-cube', 'label' => __('menu_simulator')]
        ],
        __('cluster_tms') => [
            'gate'       => ['icon' => 'fa-door-open', 'label' => __('menu_gate')],
            'trucking'   => ['icon' => 'fa-truck-front', 'label' => __('menu_trucking')],
            'intermodal' => ['icon' => 'fa-train-subway', 'label' => __('menu_intermodal')]
        ],
        __('cluster_yms') => [
            'yard'      => ['icon' => 'fa-cubes', 'label' => __('menu_yard')],
            'alat'      => ['icon' => 'fa-dolly', 'label' => __('menu_alat')],
            'kontainer' => ['icon' => 'fa-boxes-stacked', 'label' => __('menu_kontainer')],
            'reefer'    => ['icon' => 'fa-snowflake', 'label' => __('menu_reefer')]
        ],
        __('cluster_wms') => [
            'cfs' => ['icon' => 'fa-warehouse', 'label' => __('menu_cfs')]
        ],
        __('cluster_finance') => [
            'customs'  => ['icon' => 'fa-shield-halved', 'label' => __('menu_customs')],
            'billing'  => ['icon' => 'fa-file-invoice-dollar', 'label' => __('menu_billing')],
            'scanner'  => ['icon' => 'fa-barcode', 'label' => __('menu_scanner')],
            'settings' => ['icon' => 'fa-sliders', 'label' => __('menu_settings')]
        ]
    ];
}

/**
 * Localized date formatter
 */
function get_localized_date($lang = null): string {
    global $current_lang;
    $lang = $lang ?? $current_lang ?? 'id';

    if ($lang === 'en') {
        return date('F j, Y');
    }

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
}
