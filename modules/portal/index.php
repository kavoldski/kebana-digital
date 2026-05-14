<?php
/**
 * KEBANA Digital Management System - Public Portal
 * File: modules/portal/index.php
 */

use App\Helpers\AnnouncementHelper;

$announcements = AnnouncementHelper::getAllAnnouncements('Active');
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ms" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KEBANA Portal Rasmi - Kesatuan Kebajikan Anak-Anak Sarawak</title>
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
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                        'blur-in': 'blurIn 1s ease-out forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        blurIn: {
                            '0%': { filter: 'blur(10px)', opacity: '0' },
                            '100%': { filter: 'blur(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .hero-gradient {
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.8)), url('/kebana-digital/public/assets/img/portal-hero.png');
            background-size: cover;
            background-position: center;
        }
        .text-glow {
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px -15px rgba(43, 48, 139, 0.15);
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6 h-20 lg:h-24 flex items-center justify-between">
            <div class="flex items-center space-x-3 lg:space-x-4">
                <div class="p-1.5 lg:p-2 bg-white rounded-xl shadow-sm">
                    <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 lg:h-10 w-auto">
                </div>
                <span class="text-xl lg:text-2xl font-black tracking-tighter uppercase text-kebana-blue">KEBANA<span class="text-kebana-gold">.</span></span>
            </div>
            
            <div class="hidden lg:flex items-center space-x-12 text-xs font-bold uppercase tracking-widest text-slate-600">
                <a href="#home" class="hover:text-kebana-blue transition-all relative group">
                    Utama
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-kebana-gold transition-all group-hover:w-full"></span>
                </a>
                <a href="#announcements" class="hover:text-kebana-blue transition-all relative group">
                    Hebahan
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-kebana-gold transition-all group-hover:w-full"></span>
                </a>
                <a href="#about" class="hover:text-kebana-blue transition-all relative group">
                    Mengenai Kami
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-kebana-gold transition-all group-hover:w-full"></span>
                </a>
            </div>

            <div class="flex items-center space-x-2 lg:space-x-4">
                <?php if ($isLoggedIn): ?>
                    <a href="/kebana-digital/dashboard" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-[9px] lg:text-[10px] font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        DASHBOARD
                    </a>
                <?php else: ?>
                    <a href="/kebana-digital/login" class="hidden sm:block px-4 lg:px-6 py-3 text-kebana-blue text-[10px] lg:text-xs font-bold uppercase tracking-widest hover:text-kebana-accent transition-all">
                        LOG MASUK
                    </a>
                    <a href="/kebana-digital/register" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-[9px] lg:text-[10px] font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        DAFTAR AHLI
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient relative min-h-screen flex flex-col overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-kebana-dark via-transparent to-transparent opacity-70 lg:opacity-60"></div>
        
        <div class="flex-1 flex flex-col justify-center max-w-7xl mx-auto px-6 relative z-10 w-full pt-32 pb-20">
            <div class="max-w-4xl space-y-8 lg:space-y-12">
                <div class="inline-flex items-center space-x-3 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full animate-blur-in">
                    <span class="w-2 h-2 bg-kebana-gold rounded-full animate-pulse"></span>
                    <span class="text-white text-[8px] lg:text-[10px] font-bold uppercase tracking-[0.3em]">Portal Rasmi KEBANA Digital</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white tracking-tighter leading-[0.9] lg:leading-[0.85] animate-fade-in-up">
                    MEMPERKASA <br/>
                    <span class="text-kebana-gold text-glow">ANAK SARAWAK</span><br/>
                    DI ERA DIGITAL.
                </h1>
                
                <p class="text-lg lg:text-xl text-slate-300 font-medium leading-relaxed max-w-2xl animate-fade-in-up [animation-delay:200ms] opacity-0" style="animation-fill-mode: forwards;">
                    Platform pengurusan bersepadu Kesatuan Kebajikan Anak-Anak Sarawak (KEBANA) untuk kebajikan, aktiviti, dan perpaduan komuniti yang lebih dinamik.
                </p>
                
                <div class="flex flex-wrap items-center gap-6 lg:gap-10 pt-4 animate-fade-in-up [animation-delay:400ms] opacity-0" style="animation-fill-mode: forwards;">
                    <a href="#announcements" class="w-full sm:w-auto text-center px-12 py-5 bg-kebana-gold text-kebana-blue text-[10px] lg:text-xs font-black uppercase tracking-[0.2em] hover:scale-105 hover:shadow-[0_0_30px_rgba(255,215,0,0.4)] transition-all rounded-sm">
                        LIHAT HEBAHAN
                    </a>
                    <a href="#about" class="w-full sm:w-auto justify-center group flex items-center space-x-4 text-white text-[10px] lg:text-xs font-black uppercase tracking-widest">
                        <span class="w-8 lg:w-12 h-[1px] bg-white/30 group-hover:w-16 group-hover:bg-kebana-gold transition-all"></span>
                        <span>MENGENAI KAMI</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats/Features -->
        <div class="relative z-20 px-6 pb-12 lg:pb-20">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <div class="bg-white/5 backdrop-blur-2xl border border-white/10 p-8 lg:p-10 rounded-[2rem] animate-fade-in-up [animation-delay:600ms] opacity-0 shadow-2xl" style="animation-fill-mode: forwards;">
                    <div class="w-14 h-14 bg-kebana-gold/10 flex items-center justify-center rounded-2xl mb-6">
                        <i class="fa-solid fa-shield-halved text-kebana-gold text-2xl"></i>
                    </div>
                    <h4 class="text-white font-bold uppercase tracking-widest text-xs mb-3">Kebajikan Terjamin</h4>
                    <p class="text-slate-400 text-xs leading-relaxed">Sistem pengurusan bantuan yang telus dan efisien untuk kebajikan semua ahli.</p>
                </div>
                <div class="bg-white/5 backdrop-blur-2xl border border-white/10 p-8 lg:p-10 rounded-[2rem] animate-fade-in-up [animation-delay:800ms] opacity-0 shadow-2xl" style="animation-fill-mode: forwards;">
                    <div class="w-14 h-14 bg-kebana-gold/10 flex items-center justify-center rounded-2xl mb-6">
                        <i class="fa-solid fa-bolt text-kebana-gold text-2xl"></i>
                    </div>
                    <h4 class="text-white font-bold uppercase tracking-widest text-xs mb-3">Akses Pantas</h4>
                    <p class="text-slate-400 text-xs leading-relaxed">Maklumat dan hebahan terkini dihantar terus ke peranti anda dengan segera.</p>
                </div>
                <div class="bg-white/5 backdrop-blur-2xl border border-white/10 p-8 lg:p-10 rounded-[2rem] animate-fade-in-up [animation-delay:1000ms] opacity-0 shadow-2xl" style="animation-fill-mode: forwards;">
                    <div class="w-14 h-14 bg-kebana-gold/10 flex items-center justify-center rounded-2xl mb-6">
                        <i class="fa-solid fa-fingerprint text-kebana-gold text-2xl"></i>
                    </div>
                    <h4 class="text-white font-bold uppercase tracking-widest text-xs mb-3">Identiti Digital</h4>
                    <p class="text-slate-400 text-xs leading-relaxed">Keahlian digital bersepadu untuk kemudahan segala urusan rasmi ahli.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="py-40 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-24 gap-12">
                <div class="space-y-4">
                    <span class="flex items-center space-x-3 text-[10px] font-black text-kebana-blue uppercase tracking-[0.4em]">
                        <span class="w-8 h-[1px] bg-kebana-gold"></span>
                        <span>Hebahan Terkini</span>
                    </span>
                    <h2 class="text-5xl font-black text-slate-900 tracking-tighter uppercase leading-none">Informasi & <br/><span class="text-kebana-blue">Notis Penting</span></h2>
                </div>
                <p class="text-xs font-medium text-slate-500 max-w-sm leading-relaxed border-l-2 border-kebana-gold pl-6">
                    Dapatkan maklumat terkini mengenai aktiviti dan pengumuman rasmi daripada Setiausaha Pusat KEBANA.
                </p>
            </div>

            <?php if (empty($announcements)): ?>
                <div class="py-32 text-center bg-slate-50 border border-dashed border-slate-200 rounded-3xl">
                    <div class="w-20 h-20 bg-white shadow-sm flex items-center justify-center rounded-2xl mx-auto mb-8">
                        <i class="fa-solid fa-bullhorn text-3xl text-slate-200"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tiada hebahan aktif buat masa ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="card-hover group bg-white p-12 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col h-full relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-kebana-blue/5 rounded-bl-[5rem] -mr-10 -mt-10 transition-all group-hover:bg-kebana-gold/10"></div>
                            
                            <div class="mb-10 flex justify-between items-center relative z-10">
                                <span class="px-4 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest rounded-full">
                                    <i class="fa-regular fa-calendar-days mr-2 text-kebana-blue"></i> <?php echo date('d M Y', strtotime($ann['created_at'])); ?>
                                </span>
                                <button class="text-slate-200 group-hover:text-kebana-gold transition-colors">
                                    <i class="fa-solid fa-bookmark text-xl"></i>
                                </button>
                            </div>

                            <h3 class="text-2xl font-black text-slate-900 tracking-tight uppercase mb-8 leading-tight group-hover:text-kebana-blue transition-colors">
                                <?php echo htmlspecialchars($ann['title']); ?>
                            </h3>
                            
                            <p class="text-sm font-medium text-slate-500 leading-relaxed mb-12 flex-1 line-clamp-5">
                                <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                            </p>
                            
                            <div class="pt-10 border-t border-slate-50 mt-auto flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Diterbitkan Oleh</span>
                                    <p class="text-[11px] font-black text-kebana-blue uppercase tracking-wider"><?php echo htmlspecialchars($ann['creator_name']); ?></p>
                                </div>
                                <div class="w-10 h-10 bg-kebana-blue text-white flex items-center justify-center rounded-full shadow-lg shadow-kebana-blue/20 transform translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all">
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-40 bg-slate-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
                <div class="relative group">
                    <div class="absolute -inset-6 bg-kebana-blue/5 rounded-[3rem] rotate-3 group-hover:rotate-0 transition-all"></div>
                    <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl">
                        <img src="/kebana-digital/public/assets/img/portal-about.png" alt="Kebana Community" class="w-full h-auto transform group-hover:scale-105 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-kebana-blue/60 to-transparent"></div>
                        <div class="absolute bottom-12 left-12">
                            <p class="text-white text-3xl font-black uppercase tracking-tighter italic">Bersatu Kita Teguh</p>
                            <p class="text-kebana-gold text-xs font-bold uppercase tracking-[0.4em] mt-2">Keluarga Besar KEBANA</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-12">
                    <div class="space-y-6">
                        <span class="inline-flex items-center space-x-3 px-4 py-2 bg-kebana-blue/5 rounded-full">
                            <span class="text-kebana-blue text-[10px] font-black uppercase tracking-widest">Visi & Misi Kami</span>
                        </span>
                        <h2 class="text-5xl font-black text-slate-900 tracking-tighter uppercase leading-[1.1]">Menyatukan Kekuatan <br/><span class="text-kebana-blue text-glow">Anak Sarawak.</span></h2>
                        <p class="text-base text-slate-500 font-medium leading-relaxed">
                            Kesatuan Kebajikan Anak-Anak Sarawak (KEBANA) bertekad untuk menjadi tunjang utama dalam memelihara kebajikan dan memperkasa komuniti Sarawak melalui inovasi digital dan semangat perpaduan yang teguh.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-10">
                        <div class="flex items-start space-x-8 p-8 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all">
                            <div class="w-16 h-16 bg-kebana-blue/5 text-kebana-blue flex items-center justify-center rounded-2xl flex-shrink-0">
                                <i class="fa-solid fa-hand-holding-heart text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-3">Kebajikan Utama</h4>
                                <p class="text-[13px] text-slate-500 font-medium leading-relaxed">Sentiasa bersedia memberikan bantuan dan sokongan moral kepada ahli yang memerlukan dalam pelbagai aspek kehidupan.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-8 p-8 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all">
                            <div class="w-16 h-16 bg-kebana-blue/5 text-kebana-blue flex items-center justify-center rounded-2xl flex-shrink-0">
                                <i class="fa-solid fa-people-group text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-3">Perpaduan Komuniti</h4>
                                <p class="text-[13px] text-slate-500 font-medium leading-relaxed">Menganjurkan pelbagai aktiviti sosio-budaya untuk mengeratkan silaturahim antara ahli di seluruh Malaysia.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#0A0F1E] pt-32 pb-12 text-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
        <div class="absolute top-0 right-0 w-1/3 h-full bg-kebana-blue/5 rounded-l-full blur-3xl -mr-20"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-20 mb-24">
                <div class="space-y-8">
                    <div class="flex items-center space-x-4">
                        <div class="p-2 bg-white rounded-xl shadow-lg">
                            <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 w-auto">
                        </div>
                        <span class="text-2xl font-black tracking-tighter uppercase">KEBANA<span class="text-kebana-gold">.</span></span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium uppercase leading-loose tracking-widest max-w-xs">
                        Kesatuan Kebajikan Anak-Anak Sarawak. <br/>
                        Berdaftar di bawah Pendaftar Pertubuhan Malaysia.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-11 h-11 border border-white/5 bg-white/5 flex items-center justify-center rounded-xl hover:bg-kebana-blue hover:border-kebana-blue transition-all duration-300">
                            <i class="fa-brands fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-11 h-11 border border-white/5 bg-white/5 flex items-center justify-center rounded-xl hover:bg-kebana-blue hover:border-kebana-blue transition-all duration-300">
                            <i class="fa-brands fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-11 h-11 border border-white/5 bg-white/5 flex items-center justify-center rounded-xl hover:bg-kebana-blue hover:border-kebana-blue transition-all duration-300">
                            <i class="fa-brands fa-x-twitter text-sm"></i>
                        </a>
                    </div>
                </div>
                
                <div class="space-y-8">
                    <h5 class="text-[11px] font-black text-white uppercase tracking-[0.4em] flex items-center">
                        <span class="w-6 h-[1px] bg-kebana-gold mr-3"></span>
                        Navigasi
                    </h5>
                    <ul class="space-y-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                        <li><a href="#home" class="hover:text-kebana-gold transition-colors duration-300">Utama</a></li>
                        <li><a href="#announcements" class="hover:text-kebana-gold transition-colors duration-300">Hebahan</a></li>
                        <li><a href="#about" class="hover:text-kebana-gold transition-colors duration-300">Mengenai Kami</a></li>
                        <li><a href="/kebana-digital/login" class="hover:text-kebana-gold transition-colors duration-300 text-slate-300">Log Masuk Ahli</a></li>
                    </ul>
                </div>

                <div class="space-y-8">
                    <h5 class="text-[11px] font-black text-white uppercase tracking-[0.4em] flex items-center">
                        <span class="w-6 h-[1px] bg-kebana-gold mr-3"></span>
                        Polisi
                    </h5>
                    <ul class="space-y-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                        <li><a href="#" class="hover:text-kebana-gold transition-colors duration-300">Dasar Privasi</a></li>
                        <li><a href="#" class="hover:text-kebana-gold transition-colors duration-300">Terma & Syarat</a></li>
                        <li><a href="#" class="hover:text-kebana-gold transition-colors duration-300">Bantuan Teknikal</a></li>
                    </ul>
                </div>

                <div class="space-y-8">
                    <h5 class="text-[11px] font-black text-white uppercase tracking-[0.4em] flex items-center">
                        <span class="w-6 h-[1px] bg-kebana-gold mr-3"></span>
                        Lokasi
                    </h5>
                    <ul class="space-y-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                        <li class="flex items-start">
                            <i class="fa-solid fa-envelope mr-4 text-kebana-gold text-base mt-0.5"></i> 
                            admin@kebana.org.my
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-phone mr-4 text-kebana-gold text-base mt-0.5"></i> 
                            +603-8888 7777
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-location-dot mr-4 text-kebana-gold text-base mt-0.5"></i> 
                            Kuching, Sarawak
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-bold text-slate-600 uppercase tracking-widest">
                <p>&copy; <?php echo date('Y'); ?> KEBANA Digital. Hak Cipta Terpelihara.</p>
                <div class="flex items-center space-x-3">
                    <span class="text-slate-500">Designed with Excellence</span>
                    <div class="w-1.5 h-1.5 bg-kebana-gold rounded-full shadow-[0_0_10px_rgba(255,215,0,0.5)]"></div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
