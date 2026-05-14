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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        kebana: {
                            blue: '#003366',
                            yellow: '#FFCC00',
                            dark: '#0F172A',
                            accent: '#004A99'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hero-pattern {
            background-color: #003366;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-opacity='0.1'%3E%3Cpath fill='%23ffffff' d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10-10-4.477-10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }
        .announcement-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-10 w-auto">
                <span class="text-2xl font-black tracking-tighter italic uppercase text-kebana-blue">KEBANA<span class="text-kebana-yellow">.</span></span>
            </div>
            
            <div class="hidden md:flex items-center space-x-10 text-[10px] font-black uppercase tracking-widest text-slate-500">
                <a href="#home" class="hover:text-kebana-blue transition-colors">Utama</a>
                <a href="#announcements" class="hover:text-kebana-blue transition-colors">Hebahan</a>
                <a href="#about" class="hover:text-kebana-blue transition-colors">Mengenai Kami</a>
            </div>

            <div class="flex items-center space-x-4">
                <?php if ($isLoggedIn): ?>
                    <a href="/kebana-digital/dashboard" class="px-6 py-3 bg-kebana-blue text-white text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20">
                        KE PAPARAN UTAMA
                    </a>
                <?php else: ?>
                    <a href="/kebana-digital/login" class="px-6 py-3 text-kebana-blue text-[10px] font-black uppercase tracking-widest hover:text-kebana-accent transition-all">
                        LOG MASUK
                    </a>
                    <a href="/kebana-digital/register" class="px-6 py-3 bg-kebana-blue text-white text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20">
                        DAFTAR AHLI
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-pattern relative py-32 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-kebana-blue/80 to-transparent"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center text-white">
            <div class="space-y-8 animate-fade-in">
                <span class="inline-block px-4 py-1.5 bg-kebana-yellow text-kebana-blue text-[10px] font-black uppercase tracking-[0.3em] rounded-full">Portal Rasmi KEBANA Digital</span>
                <h1 class="text-6xl font-black tracking-tighter uppercase leading-[0.9] italic">
                    Memperkasa <br/>
                    <span class="text-kebana-yellow">Anak-Anak Sarawak</span><br/>
                    Dalam Digital.
                </h1>
                <p class="text-lg text-slate-300 font-medium leading-relaxed max-w-xl">
                    Platform pengurusan bersepadu Kesatuan Kebajikan Anak-Anak Sarawak (KEBANA) untuk kebajikan, aktiviti, dan perpaduan komuniti yang lebih dinamik.
                </p>
                <div class="flex items-center space-x-6 pt-4">
                    <a href="#announcements" class="px-10 py-5 bg-kebana-yellow text-kebana-blue text-xs font-black uppercase tracking-widest hover:scale-105 transition-all shadow-2xl">
                        LIHAT HEBAHAN
                    </a>
                    <a href="#about" class="text-xs font-black uppercase tracking-widest border-b-2 border-white/20 hover:border-white pb-1 transition-all">
                        MENGENAI KAMI
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="py-32 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
                <div>
                    <span class="text-[10px] font-black text-kebana-blue uppercase tracking-[0.5em] block mb-4">Hebahan Terkini</span>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">Informasi & Notis Penting</h2>
                </div>
                <div class="h-0.5 flex-1 bg-slate-200 mb-4 hidden md:block"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest max-w-xs text-right">Dapatkan maklumat terkini mengenai aktiviti dan pengumuman rasmi daripada Setiausaha Pusat.</p>
            </div>

            <?php if (empty($announcements)): ?>
                <div class="py-20 text-center bg-white border border-slate-100 shadow-sm">
                    <i class="fa-solid fa-bullhorn text-6xl text-slate-100 mb-6 block"></i>
                    <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Tiada hebahan aktif buat masa ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="announcement-card bg-white p-10 border border-slate-100 shadow-sm hover:shadow-2xl hover:border-kebana-blue transition-all flex flex-col h-full group">
                            <div class="mb-8 flex justify-between items-start">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">
                                    <i class="fa-regular fa-calendar-days mr-2"></i> <?php echo date('d M Y', strtotime($ann['created_at'])); ?>
                                </span>
                                <i class="fa-solid fa-bookmark text-kebana-blue/10 group-hover:text-kebana-yellow transition-colors"></i>
                            </div>
                            <h3 class="text-xl font-black text-kebana-blue tracking-tight uppercase italic mb-6 leading-tight group-hover:text-kebana-accent">
                                <?php echo htmlspecialchars($ann['title']); ?>
                            </h3>
                            <p class="text-xs font-medium text-slate-500 leading-relaxed mb-10 flex-1 line-clamp-4">
                                <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                            </p>
                            <div class="pt-8 border-t border-slate-50 mt-auto">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Diterbitkan Oleh:</span>
                                <p class="text-[10px] font-black text-kebana-blue uppercase tracking-tighter mt-1"><?php echo htmlspecialchars($ann['creator_name']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section Placeholder -->
    <section id="about" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
            <div class="space-y-10">
                <div>
                    <span class="text-[10px] font-black text-kebana-blue uppercase tracking-[0.5em] block mb-4">Visi & Misi</span>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic leading-[1.1]">Menyatukan Kekuatan Anak Sarawak.</h2>
                </div>
                <div class="space-y-6">
                    <p class="text-sm font-medium text-slate-600 leading-relaxed">
                        Kesatuan Kebajikan Anak-Anak Sarawak (KEBANA) bertekad untuk menjadi tunjang utama dalam memelihara kebajikan dan memperkasa komuniti Sarawak melalui inovasi digital dan semangat perpaduan yang teguh.
                    </p>
                    <div class="grid grid-cols-1 gap-6 pt-6">
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-kebana-blue/5 text-kebana-blue flex items-center justify-center rounded-xl flex-shrink-0">
                                <i class="fa-solid fa-hand-holding-heart text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-kebana-blue uppercase tracking-widest mb-2">Kebajikan Utama</h4>
                                <p class="text-[11px] text-slate-500 font-medium leading-relaxed">Sentiasa bersedia memberikan bantuan dan sokongan moral kepada ahli yang memerlukan.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-kebana-blue/5 text-kebana-blue flex items-center justify-center rounded-xl flex-shrink-0">
                                <i class="fa-solid fa-people-group text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-kebana-blue uppercase tracking-widest mb-2">Perpaduan Komuniti</h4>
                                <p class="text-[11px] text-slate-500 font-medium leading-relaxed">Menganjurkan pelbagai aktiviti sosio-budaya untuk mengeratkan silaturahim antara ahli.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative group">
                <div class="absolute -inset-4 bg-kebana-yellow/10 rounded-2xl group-hover:bg-kebana-yellow/20 transition-all"></div>
                <div class="relative bg-kebana-blue h-[500px] flex items-center justify-center p-12 overflow-hidden shadow-2xl">
                    <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Kebana Large" class="w-64 h-auto opacity-20 filter grayscale invert">
                    <div class="absolute bottom-10 left-10 text-white space-y-2">
                        <p class="text-2xl font-black italic tracking-tighter uppercase">Bersatu Kita Teguh</p>
                        <p class="text-[10px] font-black text-kebana-yellow uppercase tracking-[0.5em]">Keluarga Besar KEBANA</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-kebana-dark py-20 text-white">
        <div class="max-w-7xl mx-auto px-6 border-b border-white/5 pb-20 mb-20 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16">
            <div class="space-y-8 col-span-1 lg:col-span-1">
                <div class="flex items-center space-x-3">
                    <img src="/kebana-digital/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 w-auto filter brightness-0 invert">
                    <span class="text-xl font-black tracking-tighter italic uppercase">KEBANA<span class="text-kebana-yellow">.</span></span>
                </div>
                <p class="text-[10px] text-slate-500 font-bold uppercase leading-loose tracking-widest">
                    Kesatuan Kebajikan Anak-Anak Sarawak. <br/>
                    Berdaftar di bawah Pendaftar Pertubuhan Malaysia.
                </p>
            </div>
            
            <div class="space-y-8">
                <h5 class="text-[10px] font-black text-white uppercase tracking-[0.5em]">Pautan Pantas</h5>
                <ul class="space-y-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    <li><a href="#" class="hover:text-kebana-yellow transition-colors">Dasar Privasi</a></li>
                    <li><a href="#" class="hover:text-kebana-yellow transition-colors">Terma & Syarat</a></li>
                    <li><a href="#" class="hover:text-kebana-yellow transition-colors">Bantuan Teknika</a></li>
                </ul>
            </div>

            <div class="space-y-8">
                <h5 class="text-[10px] font-black text-white uppercase tracking-[0.5em]">Hubungi Kami</h5>
                <ul class="space-y-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    <li class="flex items-center"><i class="fa-solid fa-envelope mr-3 text-kebana-yellow"></i> admin@kebana.org.my</li>
                    <li class="flex items-center"><i class="fa-solid fa-phone mr-3 text-kebana-yellow"></i> +603-8888 7777</li>
                    <li class="flex items-center"><i class="fa-solid fa-location-dot mr-3 text-kebana-yellow"></i> Kuching, Sarawak</li>
                </ul>
            </div>

            <div class="space-y-8">
                <h5 class="text-[10px] font-black text-white uppercase tracking-[0.5em]">Media Sosial</h5>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 border border-white/10 flex items-center justify-center hover:bg-kebana-blue transition-all"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 border border-white/10 flex items-center justify-center hover:bg-kebana-blue transition-all"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="w-10 h-10 border border-white/10 flex items-center justify-center hover:bg-kebana-blue transition-all"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8 text-[9px] font-black text-slate-600 uppercase tracking-widest">
            <p>&copy; <?php echo date('Y'); ?> KEBANA Digital Management System. All Rights Reserved.</p>
            <p>Designed with Excellence.</p>
        </div>
    </footer>

</body>
</html>
