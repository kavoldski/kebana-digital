<?php
/**
 * KEBANA Digital Management System - Dasar Privasi (Privacy Policy)
 * File: modules/portal/privacy.php
 */

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ms" class="h-full bg-white scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dasar Privasi — KEBANA Digital</title>
    <meta name="description" content="Dasar privasi perlindungan data peribadi ahli bagi sistem pengurusan digital Persatuan Kenyah Badeng Sarawak (KEBANA).">
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
                <span class="text-kebana-blue text-[10px] font-black uppercase tracking-widest">Polisi Perlindungan Data</span>
            </div>
            <h1 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tighter uppercase leading-none">
                Dasar <br/>
                <span class="text-kebana-blue">Privasi Kami.</span>
            </h1>
            <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">
                Kemas Kini Terakhir: 8 Jun 2026
            </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="hidden lg:block w-80 shrink-0">
                <div class="sticky top-32 bg-white rounded-3xl border border-slate-200 p-6 space-y-2 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 px-3 mb-4">Navigasi Dasar</h3>
                    <nav class="space-y-1">
                        <a href="#pengenalan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">1. Pengenalan</a>
                        <a href="#maklumat-kumpul" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">2. Maklumat Dikutip</a>
                        <a href="#tujuan-kumpul" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">3. Tujuan Penggunaan</a>
                        <a href="#pendedahan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">4. Pendedahan Data</a>
                        <a href="#keselamatan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">5. Keselamatan Maklumat</a>
                        <a href="#hak-pengguna" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">6. Hak-hak Ahli</a>
                        <a href="#kuki" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">7. Dasar Kuki (Cookies)</a>
                        <a href="#perubahan" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">8. Perubahan Dasar</a>
                        <a href="#hubungi" class="sidebar-link block px-3 py-2 text-sm text-slate-600 hover:text-kebana-blue rounded-lg border-l-2 border-transparent transition-all">9. Hubungi Kami</a>
                    </nav>
                </div>
            </aside>

            <!-- Privacy Content -->
            <div class="flex-1 bg-white rounded-3xl border border-slate-200 p-8 lg:p-12 shadow-sm space-y-10">
                
                <!-- Section 1 -->
                <section id="pengenalan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">01.</span> Pengenalan
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        **Persatuan Kenyah Badeng Sarawak (KEBANA)** komited sepenuhnya dalam melindungi privasi dan data peribadi ahli-ahli kami serta pengguna platform KEBANA Digital. Dasar Privasi ini menerangkan cara kami mengumpul, menggunakan, mendedahkan, dan melindungi maklumat peribadi anda selaras dengan **Akta Perlindungan Data Peribadi 2010 (PDPA)** di Malaysia.
                    </p>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Dengan menggunakan Sistem kami atau membekalkan maklumat peribadi kepada kami, anda dianggap telah memberikan kebenaran kepada kami untuk memproses maklumat peribadi anda mengikut cara yang dinyatakan dalam Dasar Privasi ini.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 2 -->
                <section id="maklumat-kumpul" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">02.</span> Maklumat yang Kami Kumpul
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Bagi membolehkan kami menguruskan keahlian dan mengendalikan persatuan dengan berkesan, kami mengumpul maklumat berikut:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>**Maklumat Identiti Peribadi:** Nama penuh, nombor kad pengenalan (MyKad), jantina, tarikh lahir, dan butiran cawangan KEBANA anda.</li>
                        <li>**Maklumat Perhubungan:** Alamat rumah, alamat e-mel, dan nombor telefon.</li>
                        <li>**Maklumat Pendaftaran & Kebangsaan:** Butiran pekerjaan, status keahlian, dan gambar profil.</li>
                        <li>**Maklumat Kewangan:** Salinan resit pembayaran atau bukti pindahan wang bagi yuran keahlian, pendaftaran program, atau sumbangan derma.</li>
                        <li>**Data Penggunaan Sistem:** Rekod log audit sistem, alamat IP, jenis pelayar web, serta aktiviti penyertaan program yang dihadiri menggunakan QR code.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 3 -->
                <section id="tujuan-kumpul" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">03.</span> Tujuan Penggunaan Maklumat
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Maklumat peribadi anda dikumpul dan diproses hanya untuk tujuan rasmi persatuan KEBANA termasuk:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Mengesahkan identiti, memproses pendaftaran keahlian baharu, serta mengemas kini data ahli sedia ada.</li>
                        <li>Menguruskan kehadiran program, pendaftaran aktiviti, serta mengeluarkan notis makluman rasmi persatuan.</li>
                        <li>Memantau dan menguruskan rekod pembayaran yuran persatuan dan transaksi kewangan rasmi KEBANA.</li>
                        <li>Membolehkan sistem komunikasi dalaman (seperti sistem mesej atau hebahan terus) berfungsi dengan lancar.</li>
                        <li>Tujuan keselamatan sistem, keselamatan data, serta pemeliharaan integriti log audit persatuan.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 4 -->
                <section id="pendedahan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">04.</span> Pendedahan Maklumat Anda
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        KEBANA tidak akan menjual, menyewa, memajak, atau mendedahkan data peribadi anda kepada mana-mana pihak ketiga bagi tujuan pemasaran luaran. Walau bagaimanapun, data anda mungkin dikongsi dengan:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Pentadbir Sistem (SuperAdmin) dan ahli jawatankuasa persatuan yang diberi kuasa akses berdasarkan peranan mereka (role-based access) untuk urusan rasmi persatuan sahaja.</li>
                        <li>Pihak berkuasa kerajaan atau undang-undang sekiranya dikehendaki secara rasmi di bawah undang-undang persekutuan Malaysia.</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 5 -->
                <section id="keselamatan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">05.</span> Keselamatan Maklumat Peribadi
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Kami mengambil langkah-langkah fizikal, pengurusan, dan teknikal yang sewajarnya bagi memastikan data peribadi anda dilindungi dengan selamat daripada akses tanpa kebenaran, kehilangan, kecurian, atau pindaan tanpa izin. 
                    </p>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Akses kepada maklumat peribadi ahli disekat mengikut tahap kebenaran peranan masing-masing (seperti Bendahari Kehormat yang hanya mempunyai akses kewangan, manakala ahli biasa hanya boleh melihat data profil mereka sendiri). Walau bagaimanapun, sila ambil perhatian bahawa penghantaran data melalui internet tidak pernah dijamin 100% selamat sepenuhnya.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 6 -->
                <section id="hak-pengguna" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">06.</span> Hak-hak Ahli
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sebagai subjek data di bawah undang-undang Malaysia, anda mempunyai hak untuk:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-slate-655">
                        <li>Mengakses dan mendapatkan salinan maklumat peribadi anda yang kami simpan.</li>
                        <li>Memohon kemas kini atau pembetulan sebarang maklumat peribadi yang tidak tepat atau tidak lengkap melalui panel profil anda dalam portal atau menghubungi pentadbir.</li>
                        <li>Menarik balik persetujuan anda untuk pemprosesan data peribadi pada bila-bila masa (ini bagaimanapun mungkin menjejaskan status keahlian anda atau akses ke dalam Sistem KEBANA Digital).</li>
                    </ul>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 7 -->
                <section id="kuki" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">07.</span> Dasar Kuki (Cookies)
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sistem kami menggunakan kuki (*cookies*) untuk menyimpan tetapan sesi pengguna, membantu anda kekal log masuk, dan membolehkan platform mengingati keutamaan paparan anda. Anda boleh melaraskan tetapan pelayar web anda untuk menolak kuki, namun beberapa fungsi penting dalam Sistem mungkin tidak akan dapat berfungsi dengan sempurna jika kuki dinyahaktifkan.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 8 -->
                <section id="perubahan" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">08.</span> Perubahan Kepada Dasar Privasi ini
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Pihak KEBANA berhak untuk mengemas kini atau meminda Dasar Privasi ini pada bila-bila masa bagi mencerminkan perubahan dalam amalan operasi kami atau undang-undang perlindungan data negara. Sebarang perubahan akan dimaklumkan melalui halaman ini dengan mengemas kini tarikh kemas kini terakhir di bahagian atas. Anda digalakkan untuk menyemak halaman ini secara berkala.
                    </p>
                </section>

                <hr class="border-slate-100" />

                <!-- Section 9 -->
                <section id="hubungi" class="scroll-mt-32 space-y-4">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                        <span class="text-kebana-blue">09.</span> Hubungi Kami
                    </h2>
                    <p class="text-slate-655 leading-relaxed text-justify">
                        Sekiranya anda ingin melaksanakan mana-mana hak anda, atau mempunyai soalan, kebimbangan, atau maklum balas berkaitan Dasar Privasi ini, sila hubungi kami:
                    </p>
                    
                    <div class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-xl bg-kebana-blue/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope text-kebana-blue"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">E-mel Pentadbir</h4>
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
                <a href="<?php echo URL_ROOT; ?>/privacy" class="hover:text-kebana-blue transition-colors text-kebana-blue font-bold">Dasar Privasi</a>
                <a href="<?php echo URL_ROOT; ?>/terms" class="hover:text-kebana-blue transition-colors">Terma &amp; Syarat</a>
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
