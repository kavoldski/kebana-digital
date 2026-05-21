<?php
/**
 * KEBANA Digital Management System - Public Portal
 * File: modules/portal/index.php
 */

use App\Helpers\AnnouncementHelper;

$announcements = AnnouncementHelper::getAllAnnouncements('Active');
$isLoggedIn    = isset($_SESSION['user_id']);

// Efficiently load first image for every announcement (single query)
$ann_ids  = array_column($announcements, 'announcement_id');
$coverMap = [];
if (method_exists(AnnouncementHelper::class, 'getCoverImageMap')) {
    $coverMap = AnnouncementHelper::getCoverImageMap($ann_ids);
}
?>
<!doctype html>
<html lang="ms" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- =====================================================
         PRIMARY SEO TAGS
         ===================================================== -->
    <title>KEBANA — Portal Rasmi Persatuan Kenyah Badeng Sarawak</title>
    <meta name="description" content="Portal rasmi Persatuan Kenyah Badeng Sarawak (KEBANA). Semak hebahan terkini, notis penting, dan perkembangan aktiviti persatuan untuk seluruh ahli.">
    <meta name="keywords" content="KEBANA, Persatuan Kenyah Badeng Sarawak, Kenyah Badeng, persatuan Sarawak, komuniti Badeng, KEBANA Digital">
    <meta name="author" content="Persatuan Kenyah Badeng Sarawak (KEBANA)">
    <meta name="robots" content="index, follow">
    <meta name="language" content="ms">

    <!-- Canonical URL: prevents duplicate content penalties -->
    <link rel="canonical" href="https://kebana.digital/">

    <!-- hreflang: targeting Malay language / Malaysian audience -->
    <link rel="alternate" hreflang="ms" href="https://kebana.digital/">
    <link rel="alternate" hreflang="x-default" href="https://kebana.digital/">

    <!-- =====================================================
         OPEN GRAPH — Facebook, WhatsApp, Telegram sharing
         ===================================================== -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KEBANA Digital">
    <meta property="og:title" content="KEBANA — Portal Rasmi Persatuan Kenyah Badeng Sarawak">
    <meta property="og:description" content="Portal rasmi KEBANA. Semak hebahan terkini, notis penting, dan perkembangan aktiviti persatuan Kenyah Badeng Sarawak.">
    <meta property="og:url" content="https://kebana.digital/">
    <meta property="og:image" content="https://kebana.digital/public/assets/img/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="KEBANA — Persatuan Kenyah Badeng Sarawak">
    <meta property="og:locale" content="ms_MY">

    <!-- =====================================================
         TWITTER / X CARD
         ===================================================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="KEBANA — Portal Rasmi Persatuan Kenyah Badeng Sarawak">
    <meta name="twitter:description" content="Portal rasmi KEBANA. Semak hebahan terkini, notis penting, dan perkembangan aktiviti persatuan.">
    <meta name="twitter:image" content="https://kebana.digital/public/assets/img/og-image.png">
    <meta name="twitter:image:alt" content="KEBANA — Persatuan Kenyah Badeng Sarawak">

    <!-- =====================================================
         SCHEMA.ORG — Structured Data (JSON-LD)
         ===================================================== -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NGO",
      "name": "Persatuan Kenyah Badeng Sarawak (KEBANA)",
      "alternateName": "KEBANA",
      "url": "https://kebana.digital",
      "logo": {
        "@type": "ImageObject",
        "url": "https://kebana.digital/public/assets/img/kebana-logo-icon.png",
        "width": 512,
        "height": 512
      },
      "image": "https://kebana.digital/public/assets/img/og-image.png",
      "description": "Pertubuhan masyarakat Kenyah Badeng Sarawak yang berfokus kepada pemeliharaan budaya, perpaduan komuniti, dan pembangunan ahli.",
      "inLanguage": "ms",
      "foundingLocation": {
        "@type": "Place",
        "name": "Sarawak, Malaysia"
      },
      "areaServed": {
        "@type": "State",
        "name": "Sarawak",
        "containedInPlace": {
          "@type": "Country",
          "name": "Malaysia"
        }
      },
      "sameAs": [
        "https://kebana.digital"
      ]
    }
    </script>

    <!-- =====================================================
         FAVICON & ICONS
         ===================================================== -->
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png">
    <link rel="apple-touch-icon" href="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png">

    <!-- =====================================================
         PERFORMANCE: Preconnect to external resources
         ===================================================== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">

    <!-- =====================================================
         STYLES & SCRIPTS
         ===================================================== -->
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
                        'fade-in-up': 'fadeInUp 0.7s ease-out forwards',
                        'blur-in':    'blurIn 1s ease-out forwards',
                        'shimmer':    'shimmer 2s linear infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%':   { opacity: '0', transform: 'translateY(28px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        blurIn: {
                            '0%':   { filter: 'blur(10px)', opacity: '0' },
                            '100%': { filter: 'blur(0)', opacity: '1' },
                        },
                        shimmer: {
                            '0%':   { backgroundPosition: '-400px 0' },
                            '100%': { backgroundPosition: '400px 0' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        /* Card cover image zoom */
        .ann-card-cover {
            overflow: hidden;
        }
        .ann-card-cover img {
            transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .ann-card:hover .ann-card-cover img {
            transform: scale(1.07);
        }
        /* Gradient overlay on cover image */
        .ann-card-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                rgba(15,23,42,0) 40%,
                rgba(15,23,42,0.55) 100%);
            pointer-events: none;
        }
        /* Staggered card entrance */
        .ann-card {
            opacity: 0;
            animation: fadeInUp 0.7s ease-out forwards;
        }
        /* Card hover lift */
        .ann-card {
            transition: box-shadow 0.4s cubic-bezier(0.4,0,0.2,1),
                        transform 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .ann-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 48px -12px rgba(43,48,139,0.18);
        }
        /* Placeholder shimmer */
        .cover-placeholder {
            background: linear-gradient(90deg,
                #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 800px 100%;
            animation: shimmer 2s linear infinite;
        }
        /* Badge pulse dot */
        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite;
        }
        @keyframes pulse {
            0%,100% { opacity:1; }
            50%      { opacity:.4; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-900">

    <!-- Header / Nav -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-slate-200/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 lg:h-24 flex items-center justify-between">
            <div class="flex items-center space-x-3 lg:space-x-4">
                <div class="p-1.5 lg:p-2 bg-white rounded-xl shadow-sm">
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 lg:h-10 w-auto">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl lg:text-2xl font-black tracking-tighter uppercase text-kebana-blue leading-none">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-[8px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pepo Petobo Udip Badeng</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 lg:space-x-6">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo URL_ROOT; ?>/dashboard" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-[9px] lg:text-[10px] font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
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
            <div class="mb-16 lg:mb-20 space-y-4" style="opacity:0;animation:fadeInUp 0.8s ease-out 0.1s forwards">
                <div class="inline-flex items-center space-x-3 px-4 py-2 bg-kebana-blue/5 rounded-full border border-kebana-blue/10">
                    <span class="w-2 h-2 bg-kebana-gold rounded-full pulse-dot"></span>
                    <span class="text-kebana-blue text-[10px] font-black uppercase tracking-[0.3em]">Hab Informasi Terkini</span>
                </div>
                <h1 class="text-5xl lg:text-7xl font-black text-slate-900 tracking-tighter uppercase leading-none">
                    Dinding <br/>
                    <span class="text-kebana-blue">Maklumat &amp; Hebahan.</span>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                    <?php foreach ($announcements as $i => $ann):
                        $coverId   = $ann['announcement_id'];
                        $coverPath = $coverMap[$coverId] ?? null;
                        $delay     = 0.15 + ($i * 0.08);
                    ?>
                        <a href="<?php echo URL_ROOT; ?>/portal/view/<?php echo $ann['announcement_id']; ?>"
                           class="ann-card group bg-white rounded-[2rem] border border-slate-100 shadow-sm flex flex-col overflow-hidden relative hover:no-underline block"
                           style="animation-delay: <?php echo $delay; ?>s">

                            <!-- Cover Photo -->
                            <div class="ann-card-cover relative w-full h-52 bg-slate-100 flex-shrink-0">
                                <?php if ($coverPath): ?>
                                    <img src="<?php echo URL_ROOT . '/' . $coverPath; ?>"
                                         alt="<?php echo htmlspecialchars($ann['title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <!-- Elegant placeholder when no image -->
                                    <div class="cover-placeholder w-full h-full flex items-center justify-center">
                                        <div class="text-center opacity-30">
                                            <i class="fa-solid fa-bullhorn text-4xl text-slate-400 mb-2 block"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tiada Gambar</span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Date badge overlaid on cover -->
                                <div class="absolute top-4 left-4 z-10 flex items-center space-x-2 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-sm border border-white/50">
                                    <i class="fa-regular fa-calendar text-kebana-blue text-[9px]"></i>
                                    <span class="text-[9px] font-black text-slate-800 uppercase tracking-wider"><?php echo date('d M Y', strtotime($ann['created_at'])); ?></span>
                                </div>

                                <!-- Arrow icon badge (top-right) -->
                                <div class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/90 backdrop-blur-sm border border-white/50 flex items-center justify-center shadow-sm transition-all duration-300 group-hover:bg-kebana-blue group-hover:border-transparent">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-500 group-hover:text-white transition-colors duration-300"></i>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="flex flex-col flex-1 p-7 lg:p-8 space-y-4">
                                <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase leading-tight group-hover:text-kebana-blue transition-colors duration-300 line-clamp-2">
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </h3>
                                
                                <p class="text-sm font-medium text-slate-500 leading-relaxed line-clamp-3 flex-1">
                                    <?php echo htmlspecialchars($ann['content']); ?>
                                </p>

                                <!-- Card Footer -->
                                <div class="pt-5 border-t border-slate-50 flex items-center justify-between">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-7 h-7 bg-kebana-blue text-white rounded-full flex items-center justify-center text-[11px] font-black shadow-sm shadow-kebana-blue/20">
                                            <?php echo strtoupper(substr($ann['creator_name'] ?? 'K', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block">Diterbitkan oleh</span>
                                            <span class="text-[11px] font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($ann['creator_name'] ?? 'KEBANA'); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-[9px] font-black text-slate-300 uppercase tracking-wider group-hover:text-kebana-blue transition-colors duration-300">
                                        <span>Baca</span>
                                        <i class="fa-solid fa-arrow-right text-[8px] transition-transform duration-300 group-hover:translate-x-1"></i>
                                    </div>
                                </div>
                            </div>

                        </a>
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
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-6 w-auto grayscale">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tighter uppercase text-slate-400">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">Digital Management System</span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-8 lg:gap-16 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                <a href="#" class="hover:text-kebana-blue transition-colors">Dasar Privasi</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Terma &amp; Syarat</a>
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

