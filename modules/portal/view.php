<?php
/**
 * KEBANA Digital Management System - Public Announcement Detail View
 * File: modules/portal/view.php
 */

use App\Helpers\AnnouncementHelper;

$ann_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$announcement = AnnouncementHelper::getAnnouncementById($ann_id);

if (!$announcement || $announcement['status'] !== 'Active') {
    // Graceful error display matching public portal layout
    ?>
    <!doctype html>
    <html lang="ms" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Hebahan Tidak Dijumpai — KEBANA</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Outfit', sans-serif; }</style>
    </head>
    <body class="min-h-full flex items-center justify-center p-6 text-center">
        <div class="max-w-md w-full bg-white p-12 rounded-[2.5rem] shadow-xl border border-slate-300">
            <div class="w-20 h-20 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-sm border border-red-200">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase mb-4">Hebahan Tidak Dijumpai</h1>
            <p class="text-base text-slate-655 font-bold mb-8 leading-relaxed">Hebahan yang anda cari tidak wujud atau telah dinyahaktifkan oleh pihak pentadbir.</p>
            <a href="<?php echo URL_ROOT; ?>/portal" class="inline-block px-8 py-3 bg-[#2B308B] text-white text-xs font-black uppercase tracking-widest hover:bg-[#3E4ABB] transition-all rounded-full shadow-lg shadow-[#2B308B]/20">
                Kembali ke Portal
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$images = AnnouncementHelper::getAnnouncementImages($ann_id);
$images = array_values(array_filter($images, function($img) {
    return file_exists(get_absolute_upload_path($img['image_path']));
}));
$isLoggedIn = isset($_SESSION['user_id']);

// Prepare dynamically generated meta tags for SEO
$clean_content = strip_tags(html_entity_decode($announcement['content']));
$meta_desc = mb_strimwidth($clean_content, 0, 155, "...");
$og_image = !empty($images) ? URL_ROOT . '/' . $images[0]['image_path'] : URL_ROOT . '/public/assets/img/kebana-logo-icon.png';
?>
<!doctype html>
<html lang="ms" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- =====================================================
         PRIMARY SEO TAGS
         ===================================================== -->
    <title><?php echo htmlspecialchars($announcement['title']); ?> — KEBANA</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="KEBANA, Persatuan Kenyah Badeng Sarawak, Kenyah Badeng, <?php echo htmlspecialchars($announcement['title']); ?>">
    <meta name="author" content="Persatuan Kenyah Badeng Sarawak (KEBANA)">
    <meta name="robots" content="index, follow">

    <!-- =====================================================
         OPEN GRAPH — Facebook, WhatsApp, Telegram sharing
         ===================================================== -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="KEBANA Digital">
    <meta property="og:title" content="<?php echo htmlspecialchars($announcement['title']); ?> — KEBANA">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:locale" content="ms_MY">

    <!-- =====================================================
         FAVICON & ICONS
         ===================================================== -->
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png">
    <link rel="apple-touch-icon" href="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png">

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
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .carousel-slide-item img {
            transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .carousel-slide-item.active img {
            transform: scale(1.04);
        }
        .carousel-thumb {
            border-color: transparent;
            opacity: 0.65;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .carousel-thumb.active {
            border-color: #2B308B;
            opacity: 1;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(43, 48, 139, 0.15);
        }
        .carousel-thumb:hover:not(.active) {
            opacity: 0.9;
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-900">

    <!-- Header / Nav -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-slate-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 lg:h-24 flex items-center justify-between">
            <div class="flex items-center space-x-3 lg:space-x-4">
                <div class="p-1.5 lg:p-2 bg-white rounded-xl shadow-sm border border-slate-300">
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-8 lg:h-10 w-auto">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl lg:text-2xl font-black tracking-tighter uppercase text-kebana-blue leading-none">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Popo Petobo Udip Badeng</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 lg:space-x-6">
                <a href="<?php echo URL_ROOT; ?>/portal" class="text-xs font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
                    <i class="fa-solid fa-arrow-left mr-3"></i> PORTAL
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo URL_ROOT; ?>/dashboard" class="px-5 lg:px-8 py-2.5 lg:py-3.5 bg-kebana-blue text-white text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl shadow-kebana-blue/20 rounded-full">
                        DASHBOARD
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content: Detailed Information View -->
    <main class="flex-1 bg-[#F8FAFC] min-h-screen pt-12 pb-32">
        <div class="max-w-4xl mx-auto px-6">
            
            <!-- Breadcrumbs / Back button -->
            <div class="mb-10">
                <a href="<?php echo URL_ROOT; ?>/portal" class="inline-flex items-center space-x-3 text-xs font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest transition-colors group">
                    <i class="fa-solid fa-arrow-left-long group-hover:-translate-x-1.5 transition-transform text-sm"></i>
                    <span>Kembali ke Dinding Maklumat</span>
                </a>
            </div>

            <article class="bg-white rounded-[3rem] border border-slate-300 shadow-xl overflow-hidden p-8 sm:p-16 space-y-12">
                
                <!-- Article Header / Metadata -->
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex items-center space-x-3 bg-slate-50 px-4 py-2.5 rounded-2xl border border-slate-300">
                            <i class="fa-regular fa-calendar text-kebana-blue text-sm"></i>
                            <span class="text-xs font-black text-slate-900 uppercase tracking-tight"><?php echo date('d F Y', strtotime($announcement['created_at'])); ?></span>
                        </div>

                        <div class="flex items-center space-x-3 bg-slate-50 px-4 py-2.5 rounded-2xl border border-slate-300">
                            <div class="w-8 h-8 bg-kebana-blue text-white rounded-full flex items-center justify-center text-xs font-black uppercase shadow-sm border border-slate-350">
                                <?php echo strtoupper(substr($announcement['creator_name'], 0, 1)); ?>
                            </div>
                            <span class="text-xs font-black text-kebana-blue uppercase tracking-wider"><?php echo htmlspecialchars($announcement['creator_name']); ?></span>
                        </div>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight uppercase leading-tight">
                        <?php echo htmlspecialchars($announcement['title']); ?>
                    </h1>
                </div>

                <!-- Carousel Section (Only render if there are images) -->
                <?php if (!empty($images)): ?>
                    <div class="space-y-5">
                        <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl bg-slate-950 aspect-video group animate-fade-in-up" id="announcementCarousel">
                            <!-- Slides wrapper -->
                            <div class="flex h-full w-full" id="carouselSlides">
                                <?php foreach ($images as $index => $img): ?>
                                    <div class="w-full h-full flex-shrink-0 relative overflow-hidden carousel-slide-item cursor-zoom-in" data-slide-index="<?php echo $index; ?>">
                                        <img src="<?php echo URL_ROOT . '/' . $img['image_path']; ?>" 
                                             alt="<?php echo htmlspecialchars($announcement['title']); ?> - Gambar <?php echo $index + 1; ?>" 
                                             class="w-full h-full object-cover select-none pointer-events-none">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Image count badge (top left) -->
                            <div class="absolute top-6 left-6 z-10 flex items-center space-x-2 bg-slate-950/70 backdrop-blur-md px-3.5 py-2 rounded-full shadow-sm border border-white/10">
                                <i class="fa-solid fa-images text-white text-xs"></i>
                                <span class="text-xs font-black text-white uppercase tracking-widest" id="carouselCounter">1 / <?php echo count($images); ?></span>
                            </div>

                            <!-- Direction controls (Only if more than 1 image) -->
                            <?php if (count($images) > 1): ?>
                                <button type="button" id="prevBtn" class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-2xl glass-nav hover:bg-[#2B308B] hover:text-white flex items-center justify-center text-slate-800 transition-all shadow-lg border border-slate-300 active:scale-95 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-chevron-left text-lg"></i>
                                </button>
                                <button type="button" id="nextBtn" class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-2xl glass-nav hover:bg-[#2B308B] hover:text-white flex items-center justify-center text-slate-800 transition-all shadow-lg border border-slate-300 active:scale-95 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-chevron-right text-lg"></i>
                                </button>

                                <!-- Dot indicators (Overlaid) -->
                                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center space-x-2 bg-black/40 backdrop-blur-md px-4 py-2.5 rounded-full border border-white/10" id="dotsContainer">
                                    <?php foreach ($images as $index => $img): ?>
                                        <button type="button" data-slide-index="<?php echo $index; ?>" 
                                                class="carousel-dot h-2.5 w-2.5 bg-white/60 hover:bg-white rounded-full transition-all duration-300"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Dynamic Thumbnails Strip (Only if more than 1 image) -->
                        <?php if (count($images) > 1): ?>
                            <div class="flex justify-center gap-3.5 px-2 overflow-x-auto py-2" id="thumbnailContainer">
                                <?php foreach ($images as $index => $img): ?>
                                    <button type="button" data-thumb-index="<?php echo $index; ?>" 
                                            class="carousel-thumb relative w-16 h-12 sm:w-20 sm:h-14 rounded-xl overflow-hidden flex-shrink-0 border-2 border-slate-300 focus:outline-none">
                                        <img src="<?php echo URL_ROOT . '/' . $img['image_path']; ?>" 
                                             alt="Thumbnail <?php echo $index + 1; ?>" 
                                             class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/10 hover:bg-transparent transition-all duration-200"></div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Article Description Text -->
                <div class="prose max-w-none pt-4 border-t border-slate-300">
                    <p class="text-lg sm:text-xl text-slate-655 font-medium leading-relaxed whitespace-pre-wrap text-justify selection:bg-kebana-gold selection:text-slate-900"><?php echo htmlspecialchars($announcement['content']); ?></p>
                </div>

            </article>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white py-16 border-t border-slate-300 mt-auto">
        <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row justify-between items-center gap-12">
            <div class="flex items-center space-x-4">
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-300">
                    <img src="<?php echo URL_ROOT; ?>/public/assets/img/kebana-logo-icon.png" alt="Logo" class="h-6 w-auto grayscale">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tighter uppercase text-slate-550">KEBANA<span class="text-kebana-gold">.</span></span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Digital Management System</span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-x-6 md:gap-x-8 lg:gap-x-10 gap-y-2 text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-[0.12em]">
                <a href="#" class="hover:text-kebana-blue transition-colors">Dasar Privasi</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Terma &amp; Syarat</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Bantuan</a>
                <a href="#" class="hover:text-kebana-blue transition-colors">Hubungi Kami</a>
            </div>

            <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center lg:text-right">
                &copy; <?php echo date('Y'); ?> KEBANA. Hak Cipta Terpelihara.
            </div>
        </div>
    </footer>

    <!-- Lightbox Modal -->
    <?php if (!empty($images)): ?>
    <div id="lightboxModal" class="fixed inset-0 z-[100] hidden bg-slate-950/95 backdrop-blur-xl flex flex-col items-center justify-center select-none opacity-0 transition-opacity duration-300">
        <!-- Close button -->
        <button type="button" id="closeLightbox" class="absolute top-6 right-6 w-14 h-14 rounded-2xl bg-white/5 hover:bg-white/10 hover:text-kebana-gold text-white flex items-center justify-center border border-white/10 active:scale-95 transition-all z-[110]">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <!-- Nav buttons (Only show if multiple images) -->
        <?php if (count($images) > 1): ?>
            <button type="button" id="lightboxPrev" class="absolute left-6 top-1/2 -translate-y-1/2 w-14 h-14 rounded-2xl bg-white/5 hover:bg-white/10 hover:text-kebana-gold text-white flex items-center justify-center border border-white/10 active:scale-95 transition-all z-[110]">
                <i class="fa-solid fa-chevron-left text-xl"></i>
            </button>
            <button type="button" id="lightboxNext" class="absolute right-6 top-1/2 -translate-y-1/2 w-14 h-14 rounded-2xl bg-white/5 hover:bg-white/10 hover:text-kebana-gold text-white flex items-center justify-center border border-white/10 active:scale-95 transition-all z-[110]">
                <i class="fa-solid fa-chevron-right text-xl"></i>
            </button>
        <?php endif; ?>

        <!-- Image container -->
        <div class="relative max-w-5xl w-full max-h-[75vh] px-6 flex items-center justify-center">
            <img id="lightboxImage" src="" alt="View Fullscreen" class="max-w-full max-h-[75vh] object-contain rounded-2xl shadow-2xl transition-all duration-300 transform scale-95 opacity-0">
        </div>

        <!-- Info/Counter -->
        <div class="mt-8 text-center space-y-2 z-[110]">
            <span id="lightboxCounter" class="inline-block px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white text-xs font-black uppercase tracking-widest">1 / 1</span>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const slidesContainer = document.getElementById('carouselSlides');
        const slides = document.querySelectorAll('.carousel-slide-item');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const dots = document.querySelectorAll('.carousel-dot');
        const thumbs = document.querySelectorAll('.carousel-thumb');
        const carouselCounter = document.getElementById('carouselCounter');
        
        // Lightbox Elements
        const lightboxModal = document.getElementById('lightboxModal');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxCounter = document.getElementById('lightboxCounter');
        const closeLightboxBtn = document.getElementById('closeLightbox');
        const lightboxPrevBtn = document.getElementById('lightboxPrev');
        const lightboxNextBtn = document.getElementById('lightboxNext');
        
        // Image list data
        const imagePaths = <?php echo json_encode(array_map(fn($img) => URL_ROOT . '/' . $img['image_path'], $images)); ?>;
        const totalSlides = imagePaths.length;
        
        let currentSlide = 0;
        let slideInterval;

        function updateCarousel() {
            if (!slidesContainer) return;
            
            // Apply transformation matrix to shift slide
            slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
            slidesContainer.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
            
            // Update active slide zoom state
            slides.forEach((slide, index) => {
                if (index === currentSlide) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            // Update counter
            if (carouselCounter) {
                carouselCounter.textContent = `${currentSlide + 1} / ${totalSlides}`;
            }
            
            // Update dots active status
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.add('w-8', 'bg-kebana-gold');
                    dot.classList.remove('w-2.5', 'bg-white/60');
                } else {
                    dot.classList.add('w-2.5', 'bg-white/60');
                    dot.classList.remove('w-8', 'bg-kebana-gold');
                }
            });

            // Update thumbnails active status
            thumbs.forEach((thumb, index) => {
                if (index === currentSlide) {
                    thumb.classList.add('active');
                    // Scroll thumbnail smoothly inside its container without shifting the page vertically
                    const container = document.getElementById('thumbnailContainer');
                    if (container) {
                        const scrollPos = thumb.offsetLeft - (container.clientWidth / 2) + (thumb.clientWidth / 2);
                        container.scrollTo({ left: scrollPos, behavior: 'smooth' });
                    }
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        function showNextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function showPrevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        function startAutoplay() {
            if (totalSlides <= 1) return;
            stopAutoplay();
            slideInterval = setInterval(showNextSlide, 6000);
        }

        function stopAutoplay() {
            if (slideInterval) {
                clearInterval(slideInterval);
            }
        }

        // Attach Button Listeners for Main Carousel
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                showNextSlide();
                startAutoplay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                showPrevSlide();
                startAutoplay();
            });
        }

        // Dot click handlers
        dots.forEach(dot => {
            dot.addEventListener('click', function(e) {
                e.stopPropagation();
                const targetIdx = parseInt(this.getAttribute('data-slide-index'));
                currentSlide = targetIdx;
                updateCarousel();
                startAutoplay();
            });
        });

        // Thumbnail click handlers
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function(e) {
                e.stopPropagation();
                const targetIdx = parseInt(this.getAttribute('data-thumb-index'));
                currentSlide = targetIdx;
                updateCarousel();
                startAutoplay();
            });
        });

        // Autoplay listeners on hover
        const carouselEl = document.getElementById('announcementCarousel');
        if (carouselEl) {
            carouselEl.addEventListener('mouseenter', stopAutoplay);
            carouselEl.addEventListener('mouseleave', startAutoplay);
            
            // Clicking slide opens lightbox
            slides.forEach(slide => {
                slide.addEventListener('click', function() {
                    openLightbox(parseInt(this.getAttribute('data-slide-index')));
                });
            });
        }

        // ==========================================
        // LIGHTBOX LOGIC
        // ==========================================
        function openLightbox(index) {
            currentSlide = index;
            updateCarousel(); // Sync main carousel
            
            // Set image source and counter
            lightboxImage.src = imagePaths[currentSlide];
            lightboxCounter.textContent = `${currentSlide + 1} / ${totalSlides}`;
            
            // Display modal
            lightboxModal.classList.remove('hidden');
            // Allow display to register before opacity transition
            setTimeout(() => {
                lightboxModal.classList.add('opacity-100');
                lightboxImage.classList.remove('scale-95', 'opacity-0');
                lightboxImage.classList.add('scale-100', 'opacity-100');
            }, 20);
            
            stopAutoplay();
        }

        function closeLightbox() {
            lightboxModal.classList.remove('opacity-100');
            lightboxImage.classList.remove('scale-100', 'opacity-100');
            lightboxImage.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                lightboxModal.classList.add('hidden');
                startAutoplay();
            }, 300);
        }

        function navigateLightbox(direction) {
            // Animate transition between lightbox images smoothly
            lightboxImage.classList.add('opacity-0', 'scale-95');
            
            setTimeout(() => {
                if (direction === 'next') {
                    currentSlide = (currentSlide + 1) % totalSlides;
                } else {
                    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                }
                updateCarousel(); // Sync back
                
                lightboxImage.src = imagePaths[currentSlide];
                lightboxCounter.textContent = `${currentSlide + 1} / ${totalSlides}`;
                
                lightboxImage.classList.remove('opacity-0', 'scale-95');
                lightboxImage.classList.add('opacity-100', 'scale-100');
            }, 150);
        }

        if (closeLightboxBtn) {
            closeLightboxBtn.addEventListener('click', closeLightbox);
        }
        
        if (lightboxNextBtn) {
            lightboxNextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                navigateLightbox('next');
            });
        }
        
        if (lightboxPrevBtn) {
            lightboxPrevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                navigateLightbox('prev');
            });
        }

        // Click outside image to close lightbox
        lightboxModal.addEventListener('click', function(e) {
            if (e.target === lightboxModal || e.target.id === 'lightboxImage' || e.target.closest('.relative') === null) {
                // Ensure clicking exit area closes
                closeLightbox();
            }
        });

        // Keydown keyboard listeners
        document.addEventListener('keydown', function(e) {
            if (!lightboxModal.classList.contains('hidden')) {
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowRight' && totalSlides > 1) {
                    navigateLightbox('next');
                } else if (e.key === 'ArrowLeft' && totalSlides > 1) {
                    navigateLightbox('prev');
                }
            } else {
                // Arrow keys navigate main carousel if visible
                if (e.key === 'ArrowRight' && totalSlides > 1) {
                    showNextSlide();
                    startAutoplay();
                } else if (e.key === 'ArrowLeft' && totalSlides > 1) {
                    showPrevSlide();
                    startAutoplay();
                }
            }
        });

        // Initialize state
        updateCarousel();
        startAutoplay();
    });
    </script>
    <?php endif; ?>

</body>
</html>
