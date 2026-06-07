<?php
/**
 * KEBANA Digital Management System - Syarat-syarat Perkhidmatan (Terms of Service)
 * File: modules/portal/terms.php
 */

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ms" class="h-full bg-white scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Syarat-syarat Perkhidmatan — KEBANA Digital</title>
    <meta name="description" content="Syarat dan terma penggunaan sistem pengurusan digital Persatuan Kenyah Badeng Sarawak (KEBANA).">
    <meta name="robots" content="index, follow">

    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png">
    
    <!-- Preconnect to external resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    
    <!-- Styles & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        kebana: {
                            blue: '#2B308B',
                            gold: '#FFD700',
                            dark: '#0F172A',
                            accent: '#3E4ABB'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .sidebar-link.active {
            color: #2B308B;
            border-left-color: #2B308B;
            font-weight: 700;
            background-color: rgba(43, 48, 139, 0.05);
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 bg-[#F8FAFC]">

    <!-- Header / Nav -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-slate-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 lg:h-24 flex items-center justify-between">
            <a href="<?php echo URL_ROOT; ?>/portal" class="flex items-center space-x-3 lg:space-x-4 hover:opacity-90 transition-opacity">
                <div class="p-1.5 lg:p-2 bg-white rounded-xl shadow-sm border border-slate-300">
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 lg:h-10 w-auto">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl lg:text-2xl font-black tracking-tighter uppercase text-kebana-blue leading-none">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Pepo Petobo Udip Badeng</span>
                </div>
            </a>
            
            <div class="flex items-center space-x-3 lg:space-x-6">
                <a href="<?php echo URL_ROOT; ?>/portal" class="px-4 py-2 text-xs font-black uppercase tracking-wider text-slate-655 hover:text-kebana-blue transition-colors">
                    Portal Utama
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo URL_ROOT; ?>/dashboard" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        DASHBOARD
                    </a>
                <?php else: ?>
                    <a href="<?php echo URL_ROOT; ?>/login" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        LOG MASUK
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-12 lg:py-20">
        <!-- Hero Header -->
        <div class="mb-12 lg:mb-16 space-y-4">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-kebana-blue/5 rounded-full border border-kebana-blue/15">
                <span class="w-2 h-2 bg-kebana-blue rounded-full"></span>
                <span class="text-kebana-blue text-[10px] font-black uppercase tracking-widest">Dokumen Undang-undang</span>
            </div>
            <h1 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tighter uppercase leading-none">
                Syarat & Terma <br/>
                <span class="text-kebana-blue">Perkhidmatan.</span>
            </h1>
            <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">
                Kemas Kini Terakhir: 8 Jun 2026
            </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="hidden lg:block w-80 shrink-0">
                <div class="sticky top-32 bg-white rounded-3xl border border-slate-200 p-6 space-y-2 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 px-3 mb-4">Navigasi Terma</h3>
                    <nav class="space-y-1">
                        <a href="#pendahuluan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">1. Pendahuluan</a>
                        <a href="#kelayakan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">2. Kelayakan Penggunaan</a>
                        <a href="#akaun-keselamatan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">3. Akaun & Keselamatan</a>
                        <a href="#tanggungjawab" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">4. Tanggungjawab Pengguna</a>
                        <a href="#keahlian-kewangan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">5. Pendaftaran & Kewangan</a>
                        <a href="#harta-intelek" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">6. Hak Milik Intelektual</a>
                        <a href="#penamatan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">7. Penamatan Akses</a>
                        <a href="#liabiliti" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">8. Had Liabiliti</a>
                        <a href="#hubungi" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">9. Hubungi Kami</a>
                    </nav>
                </div>
            </aside>

            <!-- Terms Content -->
            <div class="flex-1 bg-white rounded-3xl border border-slate-200 p-8 lg:p-12 shadow-sm space-y-10">
                
                <!-- Section 1 -->
                <section id="pendahuluan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">01.</span> Pendahuluan
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Selamat datang ke **Sistem Pengurusan Digital KEBANA** (selepas ini dirujuk sebagai "Sistem" atau "Platform"), yang dimiliki dan dikendalikan oleh **Persatuan Kenyah Badeng Sarawak (KEBANA)**. Dengan mengakses, mendaftar, atau menggunakan mana-mana bahagian dalam Sistem ini, anda bersetuju untuk terikat secara sah dengan Syarat-syarat Perkhidmatan ini, serta Dasar Privasi kami.
                    </p>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sila baca terma-terma ini dengan teliti sebelum meneruskan pendaftaran akaun anda atau menggunakan perkhidmatan kami. Jika anda tidak bersetuju dengan mana-mana bahagian syarat ini, anda dinasihatkan untuk tidak menggunakan Sistem ini.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 2 -->
                <section id="kelayakan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">02.</span> Kelayakan Penggunaan
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Penggunaan Sistem ini dihadkan kepada:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Ahli-ahli Persatuan Kenyah Badeng Sarawak (KEBANA) yang sah dan berdaftar.</li>
                        <li>Individu yang memohon keahlian rasmi KEBANA melalui platform digital ini.</li>
                        <li>Pentadbir sistem, ahli jawatankuasa pusat, atau cawangan yang diberi kuasa khas untuk memantau, mengurus, dan mengendalikan data persatuan.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 3 -->
                <section id="akaun-keselamatan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">03.</span> Akaun & Keselamatan
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Apabila anda mendaftar dan mencipta akaun di platform KEBANA Digital, anda bersetuju untuk:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Memberikan maklumat peribadi yang tepat, terkini, dan lengkap semasa proses pendaftaran (seperti nama penuh mengikut MyKad, nombor kad pengenalan, emel, dan cawangan persatuan).</li>
                        <li>Menjaga keselamatan kata laluan anda dan memikul tanggungjawab penuh ke atas sebarang aktiviti yang berlaku di bawah akaun anda.</li>
                        <li>Menghubungi pihak pentadbir KEBANA dengan segera sekiranya anda mengesyaki sebarang pelanggaran keselamatan atau akses tanpa kebenaran kepada akaun anda.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 4 -->
                <section id="tanggungjawab" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">04.</span> Tanggungjawab Pengguna
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Semasa menggunakan platform ini, anda dilarang sama sekali daripada:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Memuat naik, menyiarkan, atau menghantar kandungan yang palsu, mengelirukan, memfitnah, lucah, atau melanggar hak orang lain.</li>
                        <li>Menggunakan platform untuk tujuan komersial yang tidak berkaitan dengan persatuan KEBANA tanpa kebenaran bertulis terlebih dahulu daripada Jawatankuasa Pusat KEBANA.</li>
                        <li>Mengganggu atau cuba menjejaskan keselamatan sistem, pangkalan data, pelayan, atau rangkaian yang dihubungkan dengan KEBANA Digital.</li>
                        <li>Menyamar sebagai mana-mana individu atau mewakili entiti lain secara salah.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 5 -->
                <section id="keahlian-kewangan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">05.</span> Pendaftaran Keahlian & Urusan Kewangan
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sistem ini menyediakan modul pendaftaran ahli baharu serta penyerahan yuran atau sumbangan.
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>**Kelulusan Keahlian:** Pendaftaran keahlian baharu tertakluk kepada pengesahan dan kelulusan oleh Setiausaha Cawangan atau Jawatankuasa Pusat KEBANA mengikut Perlembagaan Persatuan.</li>
                        <li>**Yuran & Sumbangan:** Sebarang pembayaran yuran keahlian atau sumbangan kewangan yang dibuat melalui sistem ini mestilah disertakan dengan bukti transaksi (resit pembayaran) yang sahih. Sebarang sumbangan yang telah diluluskan dan diterima adalah tidak boleh dikembalikan melainkan berlaku kesilapan teknikal yang disahkan oleh Bendahari Kehormat.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 6 -->
                <section id="harta-intelek" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">06.</span> Hak Milik Intelektual
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Segala reka bentuk, logo, kod sumber, pangkalan data, perisian, grafik, dan teks di dalam Sistem ini adalah hak milik intelek terpelihara **Persatuan Kenyah Badeng Sarawak (KEBANA)** atau pembekal teknologi rasmi kami. Anda tidak dibenarkan menyalin, mengedar, menerbitkan semula, atau mengubah suai mana-mana kandungan sistem tanpa kelulusan bertulis rasmi daripada pihak persatuan.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 7 -->
                <section id="penamatan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">07.</span> Penamatan & Penggantungan Akses
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Pihak pentadbir KEBANA berhak untuk menggantung atau menamatkan akaun anda serta akses kepada platform digital ini pada bila-bila masa, dengan atau tanpa notis, sekiranya:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Anda melanggar mana-mana terma yang terkandung dalam Syarat Perkhidmatan ini.</li>
                        <li>Status keahlian anda dalam Persatuan Kenyah Badeng Sarawak ditamatkan atau digantung mengikut peruntukan Perlembagaan KEBANA.</li>
                        <li>Terdapat permintaan rasmi daripada pihak berkuasa atau undang-undang negara Malaysia.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 8 -->
                <section id="liabiliti" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">08.</span> Had Liabiliti
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sistem KEBANA Digital disediakan atas dasar "sebagaimana adanya" (*as is*) dan "sebagaimana tersedia" (*as available*). Persatuan Kenyah Badeng Sarawak tidak memberikan jaminan bahawa Sistem akan sentiasa bebas daripada ralat, gangguan talian internet, atau serangan siber luar. 
                    </p>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        KEBANA dan barisan pentadbirnya tidak akan bertanggungjawab ke atas sebarang kerugian atau kerosakan langsung, tidak langsung, sampingan, atau khas yang timbul daripada penggunaan atau ketidakupayaan anda untuk menggunakan platform ini.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 9 -->
                <section id="hubungi" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">09.</span> Hubungi Kami
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sekiranya anda mempunyai sebarang soalan, kekeliruan, atau aduan mengenai Syarat-syarat Perkhidmatan ini, sila hubungi kami melalui saluran berikut:
                    </p>
                    
                    <div class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-xl bg-kebana-blue/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope text-kebana-blue"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">E-mel Sokongan</h4>
                                <a href="mailto:kebana.media@gmail.com" class="text-sm font-black text-kebana-blue hover:underline">kebana.media@gmail.com</a>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-xl bg-kebana-blue/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-phone text-kebana-blue"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Talian Hubungan</h4>
                                <a href="tel:+60183743375" class="text-sm font-black text-kebana-blue hover:underline">+6018-3743375</a>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white py-12 border-t border-slate-300">
        <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row justify-between items-center gap-8">
            <div class="flex items-center space-x-4">
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-300">
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-6 w-auto grayscale">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tighter uppercase text-slate-500">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Digital Management System</span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-[0.12em]">
                <a href="<?php echo URL_ROOT; ?>/privacy" class="hover:text-kebana-blue transition-colors">Dasar Privasi</a>
                <a href="<?php echo URL_ROOT; ?>/terms" class="hover:text-kebana-blue transition-colors text-kebana-blue font-bold">Terma &amp; Syarat</a>
                <a href="<?php echo URL_ROOT; ?>/manual" class="hover:text-kebana-blue transition-colors">Manual Pengguna</a>
            </div>

            <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center lg:text-right">
                &copy; <?php echo date('Y'); ?> KEBANA. Hak Cipta Terpelihara.
            </div>
        </div>
    </footer>

    <!-- Highlight active sidebar link on scroll -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.sidebar-link');

            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (pageYOffset >= sectionTop - 150) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href').includes(current)) {
                        link.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
