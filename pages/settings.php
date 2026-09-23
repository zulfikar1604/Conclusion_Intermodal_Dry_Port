<?php
// =============================================================================
// MODUL: PENGATURAN GLOBAL & TATA KELOLA SISTEM YMS
// File: pages/settings.php
// CIDP Yard Management System — PT Multi Terminal Indonesia / ITL Trisakti
// PIC : Zulfikar Jafarudin Fatah (Lead System Architect / Ketua Tim)
// =============================================================================

require_once __DIR__ . '/../connection.php';

// Ambil info user yang sedang login dari session
$cur_nama  = (!empty($_SESSION['nama']) && $_SESSION['nama'] !== 'Guest') ? $_SESSION['nama'] : 'Zulfikar Jafarudin Fatah';
$cur_email = $_SESSION['email'] ?? 'admin@cidp.ac.id';
$cur_role  = 'Lead System Architect (Ketua Tim)';

// Status koneksi basis data
$db_status = 'Terhubung (Live PDO Sync)';
$db_badge  = 'bg-emerald-50 text-emerald-700 border-emerald-200';
try {
    $pdo->query("SELECT 1");
} catch (Exception $e) {
    $db_status = 'Mode Demo Offline';
    $db_badge  = 'bg-amber-50 text-amber-700 border-amber-200';
}

$save_notice = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_save_settings'])) {
    $save_notice = true;
}
?>

<div class="space-y-6">

    <!-- Header Modul: Pengaturan Global -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80">
        <div>
            <div class="flex items-center space-x-2 mb-1.5">
                <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full bg-blue-50 text-[#0170b9] border border-blue-200">
                    <i class="fa-solid fa-gear mr-1"></i> Pengaturan Global
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?= $db_badge ?> border">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span> <?= $db_status ?>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">Pengaturan Global &amp; Tata Kelola Sistem YMS</h2>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">
                Pusat kendali arsitektur sistem, parameter operasional terminal 35 Ha, dan integrasi hak akses pimpinan proyek.
            </p>
        </div>
        <div class="flex items-center space-x-2.5 flex-shrink-0">
            <a href="dashboard.php?page=beranda" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl shadow-2xs transition inline-flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <button type="submit" form="formSettings" name="btn_save_settings" class="px-4 py-2 bg-[#004b87] hover:bg-[#002f5e] text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center space-x-1.5">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi Berhasil Simpan -->
    <?php if ($save_notice): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between text-xs text-emerald-800 animate-fadeIn">
        <div class="flex items-center space-x-2.5">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span><strong>Berhasil!</strong> Konfigurasi dan parameter sistem berhasil diperbarui.</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- 4 Kartu KPI Parameter Sistem Utama -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Status Arsitektur</span>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-0.5">YMS v2.0</p>
                <span class="text-[10px] text-blue-600 font-semibold">Orkestrasi Terpadu</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0170b9] flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-sitemap"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Mode Simulasi</span>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-0.5">Satu Pintu</p>
                <span class="text-[10px] text-emerald-600 font-semibold">Akses Konsultan Penuh</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-door-open"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Basis Data</span>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-0.5">MySQL PDO</p>
                <span class="text-[10px] text-cyan-600 font-semibold">cidp_yms (Live)</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-database"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Fasilitas Terminal</span>
                <p class="text-base sm:text-lg font-extrabold text-gray-900 mt-0.5">35 Hektar</p>
                <span class="text-[10px] text-amber-600 font-semibold">5 Blok (A - E)</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
        </div>

    </div>

    <!-- Grid 2 Kolom: Profil Pimpinan Proyek vs Form Parameter Terminal -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Profil & Portofolio Lead System Architect (5 Kolom) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Kartu Profil Zulfikar Jafarudin Fatah -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-5">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-[#002f5e] text-white flex items-center justify-center font-extrabold text-xl shadow-md flex-shrink-0">
                        ZF
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Penanggung Jawab Divisi &amp; Arsitektur:</span>
                        <h3 class="text-lg font-extrabold text-gray-900 leading-snug"><?= htmlspecialchars($cur_nama) ?></h3>
                        <span class="inline-block mt-1 text-[11px] font-bold text-[#0170b9] bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-100">
                            <?= $cur_role ?>
                        </span>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-3 text-xs">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                        <span class="text-gray-500">Alamat Pos-el (Email):</span>
                        <span class="font-mono font-bold text-gray-800"><?= htmlspecialchars($cur_email) ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                        <span class="text-gray-500">Tingkat Hak Akses:</span>
                        <span class="font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded">Superadmin (Akses Penuh)</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                        <span class="text-gray-500">Status Akun:</span>
                        <span class="font-bold text-blue-700 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> Aktif &bull; Konsultan Utama
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Sesi Login:</span>
                        <span class="font-mono text-gray-700"><?= date('d M Y, H:i') ?> WIB</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Tugas Pokok &amp; Tanggung Jawab:</h4>
                    <p class="text-xs text-gray-600 leading-relaxed bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                        Bertanggung jawab memimpin perancangan arsitektur sistem menyeluruh YMS, orkestrasi integrasi antarmoda pelabuhan kering, koordinasi tim pengembang, dan penyusunan tata kelola data sistem.
                    </p>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Ruang Lingkup Modul Langsung:</span>
                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">Beranda Eksekutif</span>
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">Pelacakan Kontainer</span>
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">Manajemen Trucking</span>
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">Alat Berat GPS</span>
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">Simulasi 3D</span>
                        <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-lg font-bold">Pengaturan Global</span>
                    </div>
                </div>
            </div>

            <!-- Kartu Identitas Akademis ITL Trisakti -->
            <div class="bg-gradient-to-br from-[#002f5e] to-[#004b87] rounded-2xl p-6 text-white shadow-xs space-y-4">
                <div class="flex items-center space-x-2 text-xs text-blue-200 font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-graduation-cap text-amber-400 text-sm"></i>
                    <span>Afiliasi Akademis Resmi</span>
                </div>
                <div>
                    <h4 class="text-base font-extrabold text-white">Institut Transportasi dan Logistik (ITL) Trisakti</h4>
                    <p class="text-xs text-blue-100 mt-1">
                        Program Studi S1 Logistik &bull; Fakultas Sistem Transportasi dan Logistik
                    </p>
                </div>
                <div class="space-y-1.5 text-xs text-blue-100 pt-3 border-t border-white/15">
                    <p><strong>Mata Kuliah:</strong> Teknologi dan Perangkat Lunak Logistik</p>
                    <p><strong>Dosen Pengampu:</strong> Dr. Tigor Franky, S.T., M.T.</p>
                    <p><strong>Topik Proyek:</strong> Inland Container Depot &amp; Dry Port Management</p>
                    <p><strong>Kelompok:</strong> Kelompok 3 &bull; Tahun Akademik 2026</p>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Form Konfigurasi Terminal & Preferensi Sistem (7 Kolom) -->
        <div class="lg:col-span-7 space-y-6">

            <form id="formSettings" method="POST" action="dashboard.php?page=settings" class="space-y-6">
                
                <!-- Panel 1: Parameter Operasional Terminal CIDP 35 Ha -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center space-x-2.5 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0170b9] flex items-center justify-center text-sm">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-gray-900">Parameter Fasilitas Terminal Pelabuhan Kering</h3>
                            <p class="text-[11px] text-gray-500">Konfigurasi fisik dan target kinerja operasional terminal 35 Ha</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Nama Fasilitas Terminal</label>
                            <input type="text" name="terminal_name" value="Conclusion Intermodal Dry Port (CIDP)" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Lokasi Kawasan Industri</label>
                            <input type="text" name="terminal_location" value="Cikarang, Jawa Barat (Jababeka V / MM2100)" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Luas Lahan Terminal (Hektar)</label>
                            <input type="number" step="0.1" name="terminal_area" value="35.0" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Target Maksimal Masa Inap (Dwell Time)</label>
                            <div class="flex">
                                <input type="number" step="0.1" name="target_dwell" value="3.0" class="w-full px-3 py-2 border border-gray-300 rounded-l-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                                <span class="bg-gray-100 border border-l-0 border-gray-300 px-3 py-2 text-gray-600 font-bold rounded-r-lg">Hari</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Titik Steker Listrik Reefer (Plugs)</label>
                            <input type="number" name="reefer_plugs" value="300" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Kapasitas Rel Kereta Api (Rail Siding)</label>
                            <input type="text" name="rail_capacity" value="30 - 60 TEUs / Rangkaian KA" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Konfigurasi Basis Data & Telemetri -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center space-x-2.5 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-gray-900">Integrasi Basis Data &amp; Telemetri IoT</h3>
                            <p class="text-[11px] text-gray-500">Konfigurasi endpoint basis data MySQL dan protokol transmisi sensor</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Host Basis Data</label>
                            <input type="text" value="localhost:3306" readonly class="w-full px-3 py-2 border border-gray-200 rounded-lg font-mono font-medium text-gray-600 bg-gray-100 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Nama Basis Data Aktif</label>
                            <input type="text" value="cidp_yms (MySQL)" readonly class="w-full px-3 py-2 border border-gray-200 rounded-lg font-mono font-medium text-gray-600 bg-gray-100 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Interval Pembaruan Otomatis (Auto-Refresh)</label>
                            <select name="refresh_interval" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-medium text-gray-800 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0170b9] outline-none">
                                <option value="3000">3.0 Detik (Sangat Cepat)</option>
                                <option value="5500" selected>5.5 Detik (Standar Rekomendasi)</option>
                                <option value="10000">10.0 Detik (Hemat Bandwidth)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Zona Waktu Operasional</label>
                            <input type="text" value="Asia/Jakarta (WIB • UTC+7)" readonly class="w-full px-3 py-2 border border-gray-200 rounded-lg font-medium text-gray-600 bg-gray-100 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Preferensi Sistem & Akses Simulasi -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center space-x-2.5 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-gray-900">Opsi &amp; Preferensi Sistem YMS</h3>
                            <p class="text-[11px] text-gray-500">Pengaturan fungsionalitas visual dan alur simulasi demonstrasi</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/60 cursor-pointer hover:bg-slate-100/70 transition">
                            <div class="pr-4">
                                <span class="font-bold text-gray-900 block">Mode Akses Demonstrasi Satu Pintu</span>
                                <span class="text-gray-500 text-[11px]">Membuka seluruh modul tanpa hambatan role-check untuk kelancaran evaluasi dosen/penguji</span>
                            </div>
                            <input type="checkbox" checked disabled class="w-4 h-4 text-[#0170b9] rounded border-gray-300">
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/60 cursor-pointer hover:bg-slate-100/70 transition">
                            <div class="pr-4">
                                <span class="font-bold text-gray-900 block">Sinkronisasi Otomatis Finansial ERP</span>
                                <span class="text-gray-500 text-[11px]">Setiap manuver alat angkat dan timbangan langsung mencatat entri buku besar akuntansi</span>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-[#0170b9] rounded border-gray-300">
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/60 cursor-pointer hover:bg-slate-100/70 transition">
                            <div class="pr-4">
                                <span class="font-bold text-gray-900 block">Peringatan Suara Alarm Gate &amp; Reefer</span>
                                <span class="text-gray-500 text-[11px]">Membunyikan nada peringatan saat terjadi kontainer kelebihan muatan VGM atau suhu reefer di luar batas</span>
                            </div>
                            <input type="checkbox" checked class="w-4 h-4 text-[#0170b9] rounded border-gray-300">
                        </label>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" name="btn_save_settings" class="px-6 py-2.5 bg-[#0170b9] hover:bg-[#004b87] text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center space-x-2">
                            <i class="fa-solid fa-check"></i>
                            <span>Terapkan Pengaturan Sistem</span>
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <!-- Panel Matriks Pembagian Tanggung Jawab Tim Pengembang -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">Struktur Matriks Tim Pengembang CIDP (Kelompok 3)</h3>
                    <p class="text-[11px] text-gray-500">Daftar penanggung jawab divisi pada proyek perancangan sistem terpadu YMS</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-gray-500">5 Anggota Tim Resmi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-gray-500 font-bold uppercase text-[10px] tracking-wider border-b border-gray-200/70">
                    <tr>
                        <th class="py-2.5 px-3">No</th>
                        <th class="py-2.5 px-3">Nama Anggota</th>
                        <th class="py-2.5 px-3">Peran &amp; Spesialisasi</th>
                        <th class="py-2.5 px-3">Modul Tanggung Jawab</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="bg-blue-50/30">
                        <td class="py-2.5 px-3 font-bold text-gray-700">1</td>
                        <td class="py-2.5 px-3 font-extrabold text-gray-900">Zulfikar Jafarudin Fatah</td>
                        <td class="py-2.5 px-3"><span class="font-bold text-[#0170b9]">Lead System Architect (Ketua Tim)</span></td>
                        <td class="py-2.5 px-3 text-gray-600">Arsitektur YMS, Beranda, Kontainer, Trucking, Alat Berat, Simulator 3D, Pengaturan Global</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Aktif</span></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-gray-700">2</td>
                        <td class="py-2.5 px-3 font-bold text-gray-900">Armansyah Muchtarrom</td>
                        <td class="py-2.5 px-3 text-cyan-800 font-semibold">Hardware &amp; Infrastructure Specialist</td>
                        <td class="py-2.5 px-3 text-gray-600">Otomasi Gate, Sensor Twistlock, Jembatan Timbang VGM, Monitor Reefer Cold Chain</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">Terdaftar</span></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-gray-700">3</td>
                        <td class="py-2.5 px-3 font-bold text-gray-900">Afriansayah Ayubi</td>
                        <td class="py-2.5 px-3 text-amber-800 font-semibold">Software &amp; ERP Process Specialist</td>
                        <td class="py-2.5 px-3 text-gray-600">Rekayasa Software, Alur Kerja Penanganan Kontainer, Modul Billing &amp; Faktur ERP</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">Terdaftar</span></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-gray-700">4</td>
                        <td class="py-2.5 px-3 font-bold text-gray-900">Juan Gamaliel</td>
                        <td class="py-2.5 px-3 text-emerald-800 font-semibold">Data Integration Specialist</td>
                        <td class="py-2.5 px-3 text-gray-600">Integrasi Aliran Data API, Denah Terminal 35 Ha, Stacking Rules, Intermodal Rail Siding</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">Terdaftar</span></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-gray-700">5</td>
                        <td class="py-2.5 px-3 font-bold text-gray-900">Naufal Andika Heditya</td>
                        <td class="py-2.5 px-3 text-indigo-800 font-semibold">Business Analyst &amp; QA Specialist</td>
                        <td class="py-2.5 px-3 text-gray-600">Analisis Kebutuhan Bisnis, Standardisasi GS1/SSCC Scanner, Kepabeanan CEISA 4.0 DJBC</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">Terdaftar</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
