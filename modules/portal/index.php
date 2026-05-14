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
    <title>Persatuan Kenyah Badeng Sarawak (KEBANA)</title>
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

    <!-- Header / Nav -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6 h-20 lg:h-24 flex items-center justify-between">
            <div class="flex items-center space-x-3 lg:space-x-4">
                <div class="p-1.5 lg:p-2 bg-white rounded-xl shadow-sm">
                    <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 lg:h-10 w-auto">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl lg:text-2xl font-black tracking-tighter uppercase text-kebana-blue leading-none">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-[8px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pepo Petobo Udip Badeng</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 lg:space-x-6">
                <?php if ($isLoggedIn): ?>
                    <a href="/kebana-digital/dashboard" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-[9px] lg:text-[10px] font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        DASHBOARD
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content: Information Wall -->
    <main class="flex-1 bg-[#F8FAFC] min-h-screen pt-16 lg:pt-24 pb-32">
        <div class="max-w-7xl mx-auto px-6">
            
            <!-- Page Title -->
            <div class="mb-16 lg:mb-24 space-y-4 animate-fade-in-up">
                <div class="inline-flex items-center space-x-3 px-4 py-2 bg-kebana-blue/5 rounded-full">
                    <span class="w-2 h-2 bg-kebana-gold rounded-full animate-pulse"></span>
                    <span class="text-kebana-blue text-[10px] font-black uppercase tracking-[0.3em]">Hab Informasi Terkini</span>
                </div>
                <h1 class="text-5xl lg:text-7xl font-black text-slate-900 tracking-tighter uppercase leading-none">
                    Dinding <br/>
                    <span class="text-kebana-blue">Maklumat & Hebahan.</span>
                </h1>
                <p class="text-base lg:text-lg text-slate-500 font-medium leading-relaxed max-w-2xl">
                    Pusat informasi bersepadu KEBANA untuk segala hebahan rasmi, notis penting, dan perkembangan aktiviti terkini buat seluruh ahli.
                </p>
            </div>

            <!-- The Wall (Announcements) -->
            <?php if (empty($announcements)): ?>
                <div class="py-40 text-center bg-white border border-dashed border-slate-200 rounded-[3rem] shadow-sm animate-blur-in">
                    <div class="w-24 h-24 bg-slate-50 shadow-inner flex items-center justify-center rounded-3xl mx-auto mb-10 transform -rotate-6">
                        <i class="fa-solid fa-bullhorn text-4xl text-slate-200"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-300 uppercase tracking-widest">Dinding ini masih kosong.</h3>
                    <p class="text-slate-400 text-sm mt-4 font-medium">Sila semak semula sebentar lagi untuk hebahan terbaru.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10 animate-fade-in-up [animation-delay:200ms]">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="card-hover group bg-white p-10 lg:p-12 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col h-full relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-40 h-40 bg-kebana-blue/5 rounded-bl-[6rem] -mr-12 -mt-12 transition-all duration-500 group-hover:bg-kebana-gold/10"></div>
                            
                            <!-- Card Header -->
                            <div class="mb-10 flex justify-between items-center relative z-10">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-slate-50 text-kebana-blue flex items-center justify-center rounded-xl font-black text-xs">
                                        <i class="fa-regular fa-calendar"></i>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Tarikh Hebahan</span>
                                        <span class="text-[11px] font-black text-slate-900 uppercase tracking-tighter"><?php echo date('d M Y', strtotime($ann['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-full border border-slate-100 flex items-center justify-center text-slate-200 group-hover:text-kebana-gold group-hover:border-kebana-gold/30 transition-all">
                                    <i class="fa-solid fa-bookmark text-sm"></i>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="relative z-10 flex-1">
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight uppercase mb-8 leading-tight group-hover:text-kebana-blue transition-colors duration-300">
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </h3>
                                
                                <p class="text-sm font-medium text-slate-500 leading-relaxed mb-12 line-clamp-6">
                                    <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                                </p>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="pt-10 border-t border-slate-50 mt-auto flex items-center justify-between relative z-10">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Diterbitkan Oleh</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-kebana-blue text-white rounded-full flex items-center justify-center text-[10px] font-black">
                                            <?php echo strtoupper(substr($ann['creator_name'], 0, 1)); ?>
                                        </div>
                                        <p class="text-[11px] font-black text-kebana-blue uppercase tracking-wider"><?php echo htmlspecialchars($ann['creator_name']); ?></p>
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-slate-50 text-slate-300 group-hover:bg-kebana-blue group-hover:text-white flex items-center justify-center rounded-2xl shadow-sm transition-all duration-300 transform group-hover:translate-x-1">
                                    <i class="fa-solid fa-arrow-right-long"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white py-16 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row justify-between items-center gap-12">
            <div class="flex items-center space-x-4">
                <div class="p-2 bg-slate-50 rounded-xl">
                    <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-6 w-auto grayscale">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tighter uppercase text-slate-400">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">Digital Management System</span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-8 lg:gap-16 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                <a href="#" class="hover:text-kebana-blue transition-colors">Dasar Privasi</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Terma & Syarat</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Bantuan</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Hubungi Kami</a>
            </div>

            <div class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                &copy; <?php echo date('Y'); ?> KEBANA. Hak Cipta Terpelihara.
            </div>
        </div>
    </footer>

</body>
</html>
