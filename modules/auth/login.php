<?php
/**
 * KEBANA Digital Management System - Login
 * File: modules/auth/login.php
 *
 * Premium split-screen login with a branded visual hero.
 */

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: /kebana-digital/dashboard');
    exit();
}
?>
<!doctype html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Masuk - KEBANA DIGITAL</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        kebana: {
                            blue: '#003366',
                            yellow: '#FFCC00',
                            accent: '#004A99'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(255,204,0,0.18),_transparent_28%),linear-gradient(135deg,#020617_0%,#0f172a_55%,#111827_100%)] font-sans text-slate-900">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-6 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[1rem] border border-white/10 bg-white shadow-[0_32px_100px_rgba(2,6,23,0.45)] lg:grid-cols-[minmax(0,460px)_minmax(0,1fr)]">
            <section class="relative flex flex-col justify-between bg-white px-6 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12">
                <div>
                    <div class="mb-10">
                        <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-kebana-blue/5 p-2.5">
                            <img src="<?php echo LOGO_ICON; ?>" alt="KEBANA Logo" class="h-full w-full object-contain">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.45em] text-kebana-blue/60">Kebana Digital</p>
                        <h1 class="mt-4 text-4xl font-black uppercase tracking-[-0.06em] text-kebana-blue sm:text-5xl">Log Masuk</h1>
                        <p class="mt-4 max-w-sm text-sm font-medium leading-6 text-slate-500">
                            Akses pusat pengurusan ahli, acara, kewangan, dan dokumen dalam satu ruang kerja yang lebih kemas.
                        </p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="mb-8 border border-red-200 bg-red-50 px-4 py-4 text-[11px] font-black uppercase tracking-[0.18em] text-red-700">
                            Ralat: <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/kebana-digital/authenticate" method="POST" class="space-y-6">
                        <div>
                            <label for="username" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">ID Pengguna</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                placeholder="contoh: admin"
                            >
                        </div>

                        <div>
                            <label for="password" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Kata Laluan</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                placeholder="Password"
                            >
                        </div>

                        <div class="flex flex-col gap-4 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                            <label class="flex cursor-pointer items-center gap-3">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-kebana-blue focus:ring-kebana-blue">
                                <span>Ingat sesi saya</span>
                            </label>
                            <a href="#" class="text-kebana-blue transition hover:text-kebana-accent">Lupa kata laluan?</a>
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-kebana-blue px-6 py-4 text-xs font-black uppercase tracking-[0.35em] text-white shadow-[0_16px_40px_rgba(0,51,102,0.28)] transition hover:bg-kebana-accent">
                            Masuk Sekarang
                        </button>
                    </form>
                </div>

                <div class="mt-10 border-t border-slate-100 pt-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">Tiada akaun sistem?</p>
                    <a href="/kebana-digital/sign_up" class="mt-5 inline-flex rounded-full border border-kebana-blue px-6 py-3 text-[10px] font-black uppercase tracking-[0.3em] text-kebana-blue transition hover:bg-kebana-blue hover:text-white">
                        Daftar Akaun
                    </a>
                    <p class="mt-8 text-[9px] font-black uppercase tracking-[0.3em] text-slate-300">
                        &copy; <?php echo date('Y'); ?> KEBANA DIGITAL | Versi 1.1.0
                    </p>
                </div>
            </section>

            <aside class="relative hidden min-h-[760px] overflow-hidden bg-slate-900 lg:block">
                <img
                    src="/kebana-digital/public/assets/img/login-hero.svg"
                    alt="KEBANA Digital visual"
                    class="absolute inset-0 h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(2,6,23,0.08)_0%,rgba(2,6,23,0.64)_100%)]"></div>
            </aside>
        </div>
    </main>
</body>
</html>
