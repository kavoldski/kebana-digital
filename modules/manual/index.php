<?php
/**
 * KEBANA Digital Management System - Interactive User Manual
 * File: modules/manual/index.php
 * 
 * Standalone, premium documentation page with client-side PDF export.
 */

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Manual — KEBANA Digital Management System</title>
    
    <!-- SEO Optimization -->
    <meta name="description" content="Official User Manual for KEBANA Digital Management System (KDMS). Step-by-step guides, system roles, and advanced features including MyKad OCR and AI Search.">
    <meta name="author" content="Persatuan Kenyah Badeng Sarawak (KEBANA)">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- KEBANA Icon -->
    <link rel="icon" type="image/png" href="<?php echo LOGO_ICON; ?>">

    <!-- Google Fonts: Outfit for premium modern feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- html2pdf.js for client-side PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
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
        /* PDF specific overrides and printing modes */
        .pdf-cover {
            display: none;
        }
        
        .is-printing .pdf-cover {
            display: block !important;
        }
        
        .is-printing .no-pdf {
            display: none !important;
        }

        .is-printing .pdf-page-break {
            page-break-before: always !important;
            break-before: page !important;
        }

        .is-printing {
            background-color: white !important;
            color: #1e293b !important;
        }
        
        /* Smooth fade-in animation for active elements */
        .toc-active {
            border-left-color: #2B308B !important;
            color: #2B308B !important;
            font-weight: 700;
            background-color: rgba(43, 48, 139, 0.04);
        }

        /* Custom scrollbar for beautiful docs feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans">

    <!-- Top Sticky Header (Hidden in PDF export) -->
    <header class="no-pdf sticky top-0 z-40 w-full flex-none bg-white/80 backdrop-blur border-b border-slate-200">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Info -->
                <div class="flex items-center gap-3">
                    <img src="<?php echo LOGO_ICON; ?>" alt="KEBANA Logo" class="h-10 w-10 object-contain">
                    <div>
                        <span class="text-sm font-black uppercase tracking-tight text-kebana-blue">KEBANA Digital</span>
                        <span class="ml-2 px-2 py-0.5 text-[10px] font-black bg-slate-100 text-slate-500 rounded border border-slate-200">v1.0 — June 2026</span>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo URL_ROOT; ?>/portal" class="text-xs font-black text-slate-500 hover:text-kebana-blue uppercase tracking-widest transition-colors">
                        <i class="fa-solid fa-house mr-1.5"></i> Back to Portal
                    </a>
                    <button onclick="downloadPDF()" class="bg-kebana-blue text-white px-5 py-2.5 text-xs font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-sm"></i> Download PDF
                    </button>
                    <!-- Mobile TOC Toggle -->
                    <button onclick="toggleMobileTOC()" class="lg:hidden p-2 text-slate-500 hover:text-kebana-blue focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12 flex gap-8">
        
        <!-- Sidebar Navigation (Hidden in PDF export) -->
        <aside id="sidebar-toc" class="no-pdf hidden lg:block lg:w-72 lg:flex-none sticky top-24 self-start max-h-[80vh] overflow-y-auto border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Table of Contents</h2>
            <nav class="space-y-1">
                <!-- Chapter 1 -->
                <div>
                    <a href="#ch1" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        1. Introduction
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch1-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">1.1 About the System</a>
                        <a href="#ch1-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">1.2 System Requirements</a>
                        <a href="#ch1-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">1.3 User Role Mapping</a>
                    </div>
                </div>

                <!-- Chapter 2 -->
                <div class="pt-2">
                    <a href="#ch2" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        2. Getting Started
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch2-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">2.1 Log In</a>
                        <a href="#ch2-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">2.2 Registration</a>
                        <a href="#ch2-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">2.3 Dashboard Navigation</a>
                        <a href="#ch2-4" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">2.4 Logging Out</a>
                    </div>
                </div>

                <!-- Chapter 3 -->
                <div class="pt-2">
                    <a href="#ch3" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        3. Member Management
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch3-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">3.1 Member Directory</a>
                        <a href="#ch3-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">3.2 Add Member</a>
                        <a href="#ch3-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">3.3 IC OCR Scanning</a>
                        <a href="#ch3-4" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">3.4 Mobile OCR Pairing</a>
                        <a href="#ch3-5" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">3.5 Edit & Reports</a>
                    </div>
                </div>

                <!-- Chapter 4 -->
                <div class="pt-2">
                    <a href="#ch4" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        4. Events & Activities
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch4-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">4.1 Event Catalog</a>
                        <a href="#ch4-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">4.2 Event Proposal Creation</a>
                        <a href="#ch4-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">4.3 Approval Workflow</a>
                        <a href="#ch4-4" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">4.4 Attendance & Gantt</a>
                    </div>
                </div>

                <!-- Chapter 5 -->
                <div class="pt-2">
                    <a href="#ch5" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        5. Finance & Budgets
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch5-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">5.1 Finance Dashboard</a>
                        <a href="#ch5-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">5.2 Transaction Logging</a>
                        <a href="#ch5-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">5.3 Event Budgets</a>
                        <a href="#ch5-4" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">5.4 Financial Reports</a>
                    </div>
                </div>

                <!-- Chapter 6 -->
                <div class="pt-2">
                    <a href="#ch6" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        6. Document Archive
                    </a>
                    <div class="ml-4 pl-2 border-l border-slate-100 space-y-1 mt-1">
                        <a href="#ch6-1" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">6.1 File Repository</a>
                        <a href="#ch6-2" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">6.2 AI Semantic Search</a>
                        <a href="#ch6-3" class="toc-link block py-1 text-[11px] text-slate-500 hover:text-kebana-blue transition-colors">6.3 Meeting Minutes & Proposals</a>
                    </div>
                </div>

                <!-- Chapter 7 -->
                <div class="pt-2">
                    <a href="#ch7" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        7. Announcements
                    </a>
                </div>

                <!-- Chapter 8 -->
                <div class="pt-2">
                    <a href="#ch8" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        8. Chat & Notifications
                    </a>
                </div>

                <!-- Chapter 9 -->
                <div class="pt-2">
                    <a href="#ch9" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        9. System Administration
                    </a>
                </div>

                <!-- Chapter 10 -->
                <div class="pt-2">
                    <a href="#ch10" class="toc-link block px-3 py-2 text-xs font-bold text-slate-600 hover:text-kebana-blue hover:bg-slate-50 border-l-2 border-transparent transition-all">
                        10. Troubleshooting & FAQ
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 min-w-0 bg-white border border-slate-200 p-6 sm:p-12 shadow-sm" id="manual-content-wrapper">
            
            <!-- Cover Page: Visible in PDF only -->
            <div class="pdf-cover border-[16px] border-kebana-blue p-16 h-[277mm] flex flex-col justify-between relative overflow-hidden bg-slate-50">
                <!-- KEBANA Ribbon decoration -->
                <div class="absolute top-0 right-0 w-48 h-48 bg-kebana-gold transform rotate-45 translate-x-20 -translate-y-20"></div>
                
                <div class="flex items-center gap-6">
                    <img src="<?php echo LOGO_ICON; ?>" alt="KEBANA Logo" class="h-24 w-24 object-contain">
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Persatuan Kenyah Badeng Sarawak</h2>
                        <h1 class="text-4xl font-extrabold tracking-tight text-kebana-blue mt-1">KEBANA Digital</h1>
                    </div>
                </div>

                <div class="my-auto py-12">
                    <h3 class="text-lg font-black tracking-widest text-slate-550 uppercase mb-3">SYSTEM DOCUMENTATION</h3>
                    <h2 class="text-5xl font-black text-kebana-blue uppercase tracking-tight leading-tight italic">
                        User Operations<br>Manual
                    </h2>
                    <div class="w-24 h-2 bg-kebana-gold mt-8"></div>
                    <p class="text-sm text-slate-600 mt-6 leading-relaxed max-w-xl">
                        A comprehensive administrator and user operational guide for managing community records, approvals, finance, events, announcements, and AI-driven document search RAG utilities.
                    </p>
                </div>

                <div class="border-t-2 border-slate-200 pt-8 flex justify-between items-end">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Document Version</p>
                        <p class="text-sm font-black text-kebana-blue mt-1">v1.0 — June 2026</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date of Publication</p>
                        <p class="text-sm font-black text-kebana-blue mt-1">8 June 2026</p>
                    </div>
                </div>
            </div>

            <!-- Chapter 1 -->
            <section id="ch1" class="space-y-8">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 1</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">1. Introduction</h1>
                </div>

                <!-- 1.1 -->
                <div id="ch1-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">1.1</span> About KEBANA Digital Management System (KDMS)
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        The <strong>KEBANA Digital Management System (KDMS)</strong> is a tailored administrative portal built specifically for the <strong>Persatuan Kenyah Badeng Sarawak (KEBANA)</strong>. The system serves as the centralized digital backbone for the association, automating community record-keeping, branching governance, multi-tier program approval pipelines, financial ledgering, document archival, and real-time internal communications.
                    </p>
                    <div class="p-6 bg-slate-50 border-l-4 border-kebana-gold rounded-r-lg space-y-2">
                        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-slate-500"></i> Core Pillars of KDMS
                        </h3>
                        <p class="text-xs text-slate-600">
                            KDMS relies on modular separation of duties across Cawangan (Branch) and Pusat (HQ) levels, facilitating digital accountability and transparency while eliminating structural administrative overhead.
                        </p>
                    </div>
                </div>

                <!-- 1.2 -->
                <div id="ch1-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">1.2</span> Technical System Requirements
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        KDMS is built as a lightweight, performant PHP-driven web platform. It runs natively across any modern, standard browser without demanding localized installations.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 border border-slate-200 shadow-sm rounded-lg bg-white space-y-2">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Supported Browsers</h4>
                            <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                                <li>Google Chrome (v100+)</li>
                                <li>Mozilla Firefox (v98+)</li>
                                <li>Microsoft Edge (v100+)</li>
                                <li>Apple Safari (v15+)</li>
                            </ul>
                        </div>
                        <div class="p-5 border border-slate-200 shadow-sm rounded-lg bg-white space-y-2">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Hardware Guidelines</h4>
                            <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                                <li><strong>Desktop</strong>: Min 4GB RAM, stable internet.</li>
                                <li><strong>Mobile (OCR/Portal)</strong>: iOS 14+ or Android 9.0+, working camera for scanning MyKad.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 1.3 -->
                <div id="ch1-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">1.3</span> User Role Mapping & Access Control
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        KDMS enforces strict role-based access controls (RBAC) segregated across <strong>Pusat (Central HQ)</strong> and <strong>Cawangan (Branch)</strong> levels. The system contains 12 defined roles, mapping privileges across distinct modules:
                    </p>

                    <!-- Table of Roles -->
                    <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="p-3 font-black text-slate-500 uppercase tracking-wider">Level</th>
                                    <th class="p-3 font-black text-slate-500 uppercase tracking-wider">Role ID</th>
                                    <th class="p-3 font-black text-slate-500 uppercase tracking-wider">Role Title (Malay)</th>
                                    <th class="p-3 font-black text-slate-500 uppercase tracking-wider">Privilege Scope</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-650">
                                <tr>
                                    <td class="p-3 font-black text-kebana-blue">Pusat</td>
                                    <td class="p-3 font-bold">888</td>
                                    <td class="p-3 font-bold">Super Admin</td>
                                    <td class="p-3">Global oversight. User CRUD, system configuration, audit logs.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-black text-kebana-blue">Pusat</td>
                                    <td class="p-3 font-bold">1</td>
                                    <td class="p-3 font-bold">Presiden</td>
                                    <td class="p-3">Global Read. Final approval authority for events and Central finances.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-black text-kebana-blue">Pusat</td>
                                    <td class="p-3 font-bold">4</td>
                                    <td class="p-3 font-bold">Setiausaha Pusat</td>
                                    <td class="p-3">Global secretary. Document AI archives, central announcements, member review.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-black text-kebana-blue">Pusat</td>
                                    <td class="p-3 font-bold">6</td>
                                    <td class="p-3 font-bold">Bendahari Kehormat</td>
                                    <td class="p-3">Global treasurer. Budget configurations, central transactions, global logs.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 text-slate-500">Cawangan</td>
                                    <td class="p-3 font-bold">11</td>
                                    <td class="p-3">Pengerusi Cawangan</td>
                                    <td class="p-3">Branch leader. Reviews and signs off on local events before Pusat review.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 text-slate-500">Cawangan</td>
                                    <td class="p-3 font-bold">33</td>
                                    <td class="p-3">Setiausaha Cawangan</td>
                                    <td class="p-3">Branch secretary. Add local members, submit event proposals.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 text-slate-500">Cawangan</td>
                                    <td class="p-3 font-bold">55</td>
                                    <td class="p-3">Bendahari Cawangan</td>
                                    <td class="p-3">Branch treasurer. Record local expenses, manage branch ledger accounts.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Chapter 2 -->
            <section id="ch2" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 2</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">2. Getting Started</h1>
                </div>

                <!-- 2.1 -->
                <div id="ch2-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">2.1</span> Logging In
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        To access the administrative dashboard, visit the login URL directly: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-kebana-blue text-xs">/login</code>. Enter your registered email or username and password, then click "Log Masuk".
                    </p>
                    <div class="p-6 bg-slate-50 border-l-4 border-slate-350 space-y-2">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Keep Me Logged In</h4>
                        <p class="text-xs text-slate-600">
                            Check "Ingat Saya" (Remember Me) on the login page to persist your session for up to 30 days via a secure database cookie token.
                        </p>
                    </div>
                </div>

                <!-- 2.2 -->
                <div id="ch2-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">2.2</span> Account Registration & Approval
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        New committee members can request account registration by visiting <code class="bg-slate-100 px-1.5 py-0.5 rounded text-kebana-blue text-xs">/sign_up</code>. Accounts created here require manual vetting and verification by the Super Admin before they can log in.
                    </p>
                </div>

                <!-- 2.3 -->
                <div id="ch2-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">2.3</span> Dashboard Navigation
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Upon successful authentication, users are redirected to their custom dashboard dashboard at <code class="bg-slate-100 px-1.5 py-0.5 rounded text-kebana-blue text-xs">/dashboard</code>. The dashboard dynamically renders statistics based on user role.
                    </p>
                    
                    <!-- Screenshot Integration -->
                    <div class="border border-slate-200 p-2 bg-slate-50 rounded-lg shadow-sm">
                        <img src="<?php echo URL_ROOT; ?>/public/assets/img/manual/dashboard.png" alt="KDMS Dashboard Screen Illustration" class="w-full h-auto rounded border border-slate-200">
                        <p class="text-[10px] text-slate-500 text-center mt-2 uppercase font-bold">Figure 2.1: KDMS Admin Dashboard Interface</p>
                    </div>
                </div>

                <!-- 2.4 -->
                <div id="ch2-4" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">2.4</span> Logging Out
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        To securely end your administrative session, click the "Log Keluar" link located at the bottom of the left-hand navigation sidebar. This deletes your active session and redirects you to the login screen.
                    </p>
                </div>
            </section>

            <!-- Chapter 3 -->
            <section id="ch3" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 3</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">3. Member Management</h1>
                </div>

                <!-- 3.1 -->
                <div id="ch3-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">3.1</span> Member Directory
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Navigate to the "Ahli" menu to view the complete membership directory. Users can filter members by branch (Cawangan), search by name, or extract targeted branch records.
                    </p>
                </div>

                <!-- 3.2 -->
                <div id="ch3-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">3.2</span> Add New Member
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        To add a member, click "Tambah Ahli Baru" on the Member List screen. You can enter details manually or utilize the advanced MyKad OCR scanning engine to accelerate details capture.
                    </p>
                </div>

                <!-- 3.3 -->
                <div id="ch3-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">3.3</span> Registration Form OCR Scanning
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        KDMS incorporates client-side optical character recognition (OCR) using `Tesseract.js`.
                    </p>
                    <div class="p-6 bg-slate-50 border-l-4 border-kebana-blue rounded-r-lg space-y-2">
                        <h4 class="text-xs font-black text-kebana-blue uppercase tracking-widest">How it Works</h4>
                        <ol class="text-xs text-slate-600 list-decimal list-inside space-y-1">
                            <li>Drag and drop a clear picture of the member's registration form/IC into the upload zone, or click to upload.</li>
                            <li>The OCR engine initializes and analyzes the image in-browser (protecting data privacy).</li>
                            <li>Extracted fields like Name, Email, Phone Number, IC Number, and Date of Birth are automatically populated.</li>
                        </ol>
                    </div>
                    
                    <!-- Screenshot Integration -->
                    <div class="border border-slate-200 p-2 bg-slate-50 rounded-lg shadow-sm">
                        <img src="<?php echo URL_ROOT; ?>/public/assets/img/manual/ocr.png" alt="MyKad OCR Interface Illustration" class="w-full h-auto rounded border border-slate-200">
                        <p class="text-[10px] text-slate-500 text-center mt-2 uppercase font-bold">Figure 3.1: Client-side Registration Form/IC OCR scanning panel</p>
                    </div>
                </div>

                <!-- 3.4 -->
                <div id="ch3-4" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">3.4</span> Mobile OCR Scanning & QR Pairing
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        If a computer lacks a high-quality camera or scanner, administrators can click "Imbas Guna Telefon" (Scan via Phone). The system generates a QR code representing a secure mobile OCR pairing session.
                    </p>
                    <div class="bg-slate-50 border border-slate-250 p-6 rounded-lg space-y-3">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-mobile-screen-button text-kebana-blue"></i> Steps for Mobile OCR Pairing:
                        </h4>
                        <ol class="text-xs text-slate-600 list-decimal list-inside space-y-1.5">
                            <li>Scan the QR code with your mobile smartphone.</li>
                            <li>Your phone opens the public route <code class="bg-white border px-1 rounded">/mobile-ocr?token=...</code>.</li>
                            <li>Snap a high-resolution photo of the MyKad on your phone.</li>
                            <li>The image is sent to the server and pushed to your desktop registration screen in real-time.</li>
                        </ol>
                    </div>
                </div>

                <!-- 3.5 -->
                <div id="ch3-5" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">3.5</span> Editing Members & Generating Reports
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        You can update member profiles by clicking "Kemaskini" beside any member listing. Under "Laporan", administrators can generate PDF summaries of branch memberships for local reporting.
                    </p>
                </div>
            </section>

            <!-- Chapter 4 -->
            <section id="ch4" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 4</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">4. Events & Activities</h1>
                </div>

                <!-- 4.1 -->
                <div id="ch4-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">4.1</span> Event Catalog
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        The "Aktiviti" module displays all planned events, categorized by central and branch levels. It includes a Gantt chart layout that displays project schedules and event calendars.
                    </p>
                </div>

                <!-- 4.2 -->
                <div id="ch4-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">4.2</span> Creating an Event Proposal
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        To initiate an event, the Setiausaha Cawangan or Pusat creates a proposal. On the "Cipta Aktiviti" screen, enter the event name, date, description, estimated budget, and proposed branch organizer.
                    </p>
                </div>

                <!-- 4.3 -->
                <div id="ch4-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">4.3</span> The Multi-tier Approval Workflow
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Event proposals undergo a strict multi-tier review process to ensure budget alignment and executive consent:
                    </p>
                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-lg space-y-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4 text-xs font-bold text-center">
                            <div class="bg-amber-100 text-amber-800 border border-amber-305 px-4 py-2 w-full sm:w-1/3 uppercase rounded">
                                Draft / Dicipta
                            </div>
                            <div class="text-slate-400 sm:rotate-0 rotate-90">
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                            <div class="bg-indigo-100 text-indigo-800 border border-indigo-305 px-4 py-2 w-full sm:w-1/3 uppercase rounded">
                                Dihantar Cawangan
                            </div>
                            <div class="text-slate-400 sm:rotate-0 rotate-90">
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                            <div class="bg-green-100 text-green-800 border border-green-305 px-4 py-2 w-full sm:w-1/3 uppercase rounded">
                                Diluluskan Presiden
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Once created as a <strong>Draft</strong>, the Branch Chairman submits the proposal. It scales to the <strong>Setiausaha Pusat</strong> and <strong>Presiden</strong> dashboards, who review the budgets and either grant final Approval or reject it back to drafts with comments.
                        </p>
                    </div>
                </div>

                <!-- 4.4 -->
                <div id="ch4-4" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">4.4</span> Attendance Tracking & Gantt Schedule
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Approved events display a "Kehadiran" tab. Organizers can generate a QR code for check-in. Attendees can scan the code to load `/events/checkin` and register their presence. A visual Gantt chart displays all scheduled events and their timeline overlaps automatically.
                    </p>
                </div>
            </section>

            <!-- Chapter 5 -->
            <section id="ch5" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 5</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">5. Finance & Budgets</h1>
                </div>

                <!-- 5.1 -->
                <div id="ch5-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">5.1</span> Financial Dashboard
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Access the "Kewangan" module to view KEBANA's ledger summary. This dashboard displays total assets, current balance, monthly expenses, and branch funding configurations.
                    </p>
                </div>

                <!-- 5.2 -->
                <div id="ch5-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">5.2</span> Transaction Logging
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Treasurers log income and expenses on the "Transaksi" screen. Each entry requires specifying a transaction category, date, payment method, branch scope, amount, and receipt upload for audit traceability.
                    </p>
                </div>

                <!-- 5.3 -->
                <div id="ch5-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">5.3</span> Event Budget Management
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Every event features an independent sub-ledger under "Belanjawan Aktiviti". Central treasurers allocate funds to specific item lines, preventing cawangan representatives from exceeding approved allocations.
                    </p>
                </div>

                <!-- 5.4 -->
                <div id="ch5-4" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">5.4</span> Financial Statement Exporting
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Treasurers can generate complete, formatted financial ledgers as Excel spreadsheets or PDF statements. Navigate to `Kewangan -> Jana Laporan`, select start/end dates, choose a branch, and click "Jana PDF" to download the document.
                    </p>
                </div>
            </section>

            <!-- Chapter 6 -->
            <section id="ch6" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 6</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">6. Document Archive & AI Search</h1>
                </div>

                <!-- 6.1 -->
                <div id="ch6-1" class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">6.1</span> The File Archive
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Under "Dokumen", administrators upload institutional records (such as meeting minutes, financial audits, proposals, and annual reports). The archive supports PDF, Word, Excel, and image formats.
                    </p>
                </div>

                <!-- 6.2 -->
                <div id="ch6-2" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">6.2</span> AI Semantic Search & Retrieval-Augmented Generation (RAG)
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        KDMS incorporates a cutting-edge Retrieval-Augmented Generation (RAG) assistant utilizing the Google Gemini API to query archives in natural language:
                    </p>
                    <div class="p-6 bg-indigo-50 border-l-4 border-indigo-600 rounded-r-lg space-y-3">
                        <h4 class="text-xs font-black text-indigo-700 uppercase tracking-widest">AI Backend Architecture</h4>
                        <ul class="text-xs text-slate-700 list-disc list-inside space-y-1.5">
                            <li><strong>Chunk Indexing</strong>: Uploaded PDFs are parsed on the server and divided into semantic text chunks.</li>
                            <li><strong>Vector Embeddings</strong>: The system generates 768-dimensional embeddings using `gemini-embedding-001`.</li>
                            <li><strong>Semantic Queries</strong>: When a user asks a question, cosine similarity matches the query embedding with document chunks.</li>
                            <li><strong>Conversational Response</strong>: Closest chunks are compiled and sent to `gemini-2.5-flash` to synthesize a natural answer with citations.</li>
                        </ul>
                    </div>
                    
                    <!-- Screenshot Integration -->
                    <div class="border border-slate-200 p-2 bg-slate-50 rounded-lg shadow-sm">
                        <img src="<?php echo URL_ROOT; ?>/public/assets/img/manual/ai_search.png" alt="AI Search Interface Illustration" class="w-full h-auto rounded border border-slate-200">
                        <p class="text-[10px] text-slate-500 text-center mt-2 uppercase font-bold">Figure 6.1: Document AI Semantic Search Sidebar Panel</p>
                    </div>
                </div>

                <!-- 6.3 -->
                <div id="ch6-3" class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-kebana-blue">6.3</span> Querying the Archive
                    </h2>
                    <p class="text-sm leading-relaxed text-slate-650">
                        Click "AI SEARCH" on the Document screen. In the query box, enter questions like: <em>"What was the approved budget for the Youth Festival in 2026?"</em>. The system will retrieve the matching PDF chunk, display the answer, and provide a direct link to open the source document.
                    </p>
                </div>
            </section>

            <!-- Chapter 7 -->
            <section id="ch7" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 7</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">7. Announcements & Portal</h1>
                </div>

                <div class="space-y-4">
                    <p class="text-sm leading-relaxed text-slate-650">
                        The <strong>Hebahan (Announcements)</strong> module lets central executives disseminate critical updates to the public portal and logged-in administrators.
                    </p>
                    <div class="bg-slate-50 border border-slate-250 p-6 rounded-lg space-y-3">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Publishing Updates:</h4>
                        <ol class="text-xs text-slate-600 list-decimal list-inside space-y-1.5">
                            <li>Navigate to "Hebahan" and click "Cipta Hebahan Baru".</li>
                            <li>Provide a title, announcement content, and select the status (Active or Draft).</li>
                            <li>Upload multiple image assets if required. Active notices are published to the public portal.</li>
                            <li>The public landing page at <code class="bg-white border px-1 rounded">/portal</code> displays announcements.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Chapter 8 -->
            <section id="ch8" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 8</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">8. Chat & Notifications</h1>
                </div>

                <div class="space-y-4">
                    <p class="text-sm leading-relaxed text-slate-650">
                        KDMS incorporates communication channels to keep administrators connected:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 border border-slate-200 shadow-sm rounded-lg bg-white space-y-2">
                            <h4 class="text-sm font-bold text-kebana-blue flex items-center gap-2">
                                <i class="fa-solid fa-comments"></i> Real-time Chat Center
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Access "Sembang" (Chat) to launch the internal messaging terminal. You can initiate conversations with registered committee members across any branch and view unread counters.
                            </p>
                        </div>
                        <div class="p-6 border border-slate-200 shadow-sm rounded-lg bg-white space-y-2">
                            <h4 class="text-sm font-bold text-kebana-blue flex items-center gap-2">
                                <i class="fa-solid fa-bell"></i> Push Notification Badges
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                The dashboard header features a bell icon with a dynamic red badge. System events trigger notification entries (e.g., event approvals, chat messages, and database sync alerts).
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Chapter 9 -->
            <section id="ch9" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 9</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">9. System Administration</h1>
                </div>

                <div class="space-y-4">
                    <p class="text-sm leading-relaxed text-slate-650">
                        Super Administrators (Role 888) have administrative access to manage system configuration, users, branches, and monitor activity logs.
                    </p>
                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Administrative Features:</h4>
                        <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                            <li><strong>Pengurusan Pengguna (User Management)</strong>: CRUD user accounts, allocate system roles, and approve new sign-ups.</li>
                            <li><strong>Pengurusan Cawangan (Branch Management)</strong>: Add and modify physical KEBANA branch nodes.</li>
                            <li><strong>Log Audit (Audit Log)</strong>: View database changes, logins, uploads, and transactional adjustments. Logs include IP address, user timestamp, and transaction states.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Chapter 10 -->
            <section id="ch10" class="pdf-page-break space-y-8 pt-12 lg:pt-16">
                <div class="border-b-4 border-kebana-blue pb-4">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Chapter 10</span>
                    <h1 class="text-3xl font-extrabold text-kebana-blue uppercase tracking-tight mt-1">10. Troubleshooting & FAQ</h1>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-800">Q: Why does the MyKad OCR scanner fail to extract info?</h3>
                        <p class="text-sm text-slate-650 leading-relaxed">
                            A: Tesseract.js requires clear, direct, and well-lit images. Ensure the identity card is aligned flat, has no flash reflections, and text characters are readable. If scanning continues to fail, use the Mobile OCR Pairing route or enter details manually.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-800">Q: Why can't I access the Finance module or Branch Ledger?</h3>
                        <p class="text-sm text-slate-650 leading-relaxed">
                            A: Financial access is restricted by Role-based Access Controls (RBAC). Only the Super Admin, President, Timbalan President, Bendahari Kehormat, and Bendahari Cawangan possess financial access. Contact your administrator if you need your system role adjusted.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-800">Q: How do I re-index documents for the AI search assistant?</h3>
                        <p class="text-sm text-slate-650 leading-relaxed">
                            A: The AI Search assistant updates automatically when a document is uploaded. If an index becomes corrupt or out-of-sync, the Setiausaha or Super Admin can navigate to `Dokumen`, click "KEMASKINI INDEKS" in the admin section, and confirm. This rebuilds the semantic embedding table.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-800">Q: Can I access this manual without an internet connection?</h3>
                        <p class="text-sm text-slate-650 leading-relaxed">
                            A: Yes. Click the "Download PDF" button at the top-right. This triggers `html2pdf.js` to compile the manual into an offline PDF document complete with cover page, formatting, and page numbers.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Collapsible Mobile TOC Sidebar Drawer -->
    <div id="mobile-toc" class="no-pdf fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-end p-4">
        <div class="bg-white w-full max-w-xs h-full p-6 shadow-xl flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="flex items-center justify-between mb-8">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400">Navigation</span>
                    <button onclick="toggleMobileTOC()" class="text-slate-500 hover:text-red-500">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>
                <nav class="space-y-2 text-sm" onclick="toggleMobileTOC()">
                    <a href="#ch1" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">1. Introduction</a>
                    <a href="#ch2" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">2. Getting Started</a>
                    <a href="#ch3" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">3. Member Management</a>
                    <a href="#ch4" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">4. Events & Activities</a>
                    <a href="#ch5" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">5. Finance & Budgets</a>
                    <a href="#ch6" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">6. Document Archive</a>
                    <a href="#ch7" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">7. Announcements</a>
                    <a href="#ch8" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">8. Chat & Notifications</a>
                    <a href="#ch9" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">9. System Administration</a>
                    <a href="#ch10" class="block py-2 border-b border-slate-100 hover:text-kebana-blue font-bold text-slate-600">10. FAQ & Support</a>
                </nav>
            </div>
            
            <button onclick="downloadPDF(); toggleMobileTOC();" class="w-full bg-kebana-blue text-white py-3 text-xs font-black uppercase tracking-widest hover:bg-kebana-accent transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="no-pdf bg-slate-900 text-slate-400 py-12 mt-16 border-t-4 border-kebana-gold">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <img src="<?php echo LOGO_ICON; ?>" alt="KEBANA Logo" class="h-12 w-12 mx-auto object-contain">
            <p class="text-xs font-black uppercase tracking-widest text-slate-300">Persatuan Kenyah Badeng Sarawak (KEBANA)</p>
            <p class="text-[11px] text-slate-500">© 2026 KEBANA Digital Management System. All rights reserved.</p>
        </div>
    </footer>

    <!-- JS Logic -->
    <script>
        // Toggle mobile menu drawer
        function toggleMobileTOC() {
            const drawer = document.getElementById('mobile-toc');
            drawer.classList.toggle('hidden');
        }

        // Active heading TOC tracking
        const sections = document.querySelectorAll('section');
        const tocLinks = document.querySelectorAll('.toc-link');

        window.addEventListener('scroll', () => {
            let currentActiveSection = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (window.scrollY >= sectionTop - 120) {
                    currentActiveSection = section.getAttribute('id');
                }
            });

            tocLinks.forEach(link => {
                link.classList.remove('toc-active');
                if (link.getAttribute('href') === `#${currentActiveSection}`) {
                    link.classList.add('toc-active');
                }
            });
        });

        // Trigger PDF generation flow
        function downloadPDF() {
            const element = document.getElementById('manual-content-wrapper');
            document.body.classList.add('is-printing');

            const opt = {
                margin:       [12, 15, 12, 15],
                filename:     'KEBANA_Digital_User_Manual_v1.0.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['css', 'legacy'] }
            };

            // Generate and save, then cleanup classes
            html2pdf().set(opt).from(element).save().then(() => {
                document.body.classList.remove('is-printing');
            }).catch(err => {
                console.error("PDF generation failed: ", err);
                document.body.classList.remove('is-printing');
            });
        }
    </script>
</body>
</html>
