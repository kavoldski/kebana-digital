<?php
/**
 * KEBANA Digital Management System - Sign Up
 * File: modules/auth/sign_up.php
 */

if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
}

require_once APP_ROOT . '/includes/dbconnect.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: /kebana-digital/dashboard');
    exit();
}

$cawangan_list = [];
$table_check = $conn->query("SHOW TABLES LIKE 'tbl_cawangan'");
if ($table_check && $table_check->num_rows > 0) {
    $cawangan_result = $conn->query("SELECT cawangan_id, cawangan_name FROM tbl_cawangan ORDER BY cawangan_name ASC");
    if ($cawangan_result) {
        while ($row = $cawangan_result->fetch_assoc()) {
            $cawangan_list[] = $row;
        }
    }
}
?>
<!doctype html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akaun - KEBANA DIGITAL</title>

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
        <div class="grid w-full overflow-hidden rounded-[1rem] border border-white/10 bg-white shadow-[0_32px_100px_rgba(2,6,23,0.45)] lg:grid-cols-[minmax(0,620px)_minmax(0,1fr)]">
            <section class="relative flex flex-col justify-between bg-white px-6 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12">
                <div>
                    <div class="mb-10">
                        <p class="text-[10px] font-black uppercase tracking-[0.45em] text-kebana-blue/60">Kebana Digital</p>
                        <h1 class="mt-4 text-4xl font-black uppercase tracking-[-0.06em] text-kebana-blue sm:text-5xl">Daftar Akaun</h1>
                        <p class="mt-4 max-w-xl text-sm font-medium leading-6 text-slate-500">
                            Cipta akaun baharu untuk mengurus ahli, acara, kewangan, dan dokumen dalam ruang kerja yang seragam.
                        </p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="mb-8 border border-red-200 bg-red-50 px-4 py-4 text-[11px] font-black uppercase tracking-[0.18em] text-red-700">
                            Ralat: <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/kebana-digital/register" method="POST" class="space-y-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="username" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">ID Pengguna</label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                    placeholder="contoh: admin.cawangan"
                                >
                            </div>

                            <div>
                                <label for="email" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Alamat Emel</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                    placeholder="nama@contoh.com"
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

                            <div>
                                <label for="confirm_password" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Sahkan Kata Laluan</label>
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                    placeholder="Ulang password"
                                >
                            </div>

                            <div>
                                <label for="role" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Peranan Sistem</label>
                                <select
                                    id="role"
                                    name="role"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                >
                                    <option value="">Pilih Peranan</option>
                                    <optgroup label="KEBANA PUSAT">
                                        <option value="888">Super Admin</option>
                                        <option value="1">Presiden</option>
                                        <option value="2">Timbalan Presiden 1</option>
                                        <option value="3">Timbalan Presiden 2</option>
                                        <option value="4">Setiausaha Pusat</option>
                                        <option value="5">Penolong Setiausaha Pusat</option>
                                        <option value="6">Bendahari Kehormat</option>
                                        <option value="7">Penolong Bendahari Kehormat</option>
                                    </optgroup>
                                    <optgroup label="KEBANA CAWANGAN">
                                        <option value="11">Pengerusi Cawangan</option>
                                        <option value="22">Timb. Pengerusi Cawangan</option>
                                        <option value="33">Setiausaha Cawangan</option>
                                        <option value="44">Pen. Setiausaha Cawangan</option>
                                        <option value="55">Bendahari Cawangan</option>
                                        <option value="66">Pen. Bendahari Cawangan</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label for="cawangan_id" class="mb-3 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Cawangan</label>
                                <select
                                    id="cawangan_id"
                                    name="cawangan_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-kebana-blue focus:bg-white focus:ring-4 focus:ring-kebana-blue/10"
                                >
                                    <option value="">Tiada / Pusat</option>
                                    <?php foreach ($cawangan_list as $cawangan): ?>
                                        <option value="<?php echo (int) $cawangan['cawangan_id']; ?>">
                                            <?php echo htmlspecialchars($cawangan['cawangan_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 px-5 py-5">
                            <label class="flex cursor-pointer items-start gap-4">
                                <input type="checkbox" name="terms" required class="mt-1 h-4 w-4 rounded border-slate-300 text-kebana-blue focus:ring-kebana-blue">
                                <span class="text-[10px] font-black uppercase leading-relaxed tracking-[0.22em] text-slate-500">
                                    Saya bersetuju dengan terma dan syarat serta dasar privasi KEBANA DIGITAL.
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-kebana-blue px-6 py-4 text-xs font-black uppercase tracking-[0.35em] text-white shadow-[0_16px_40px_rgba(0,51,102,0.28)] transition hover:bg-kebana-accent">
                            Daftar Sekarang
                        </button>
                    </form>
                </div>

                <div class="mt-10 border-t border-slate-100 pt-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">Sudah mempunyai akaun?</p>
                    <a href="/kebana-digital/login" class="mt-5 inline-flex rounded-full border border-kebana-blue px-6 py-3 text-[10px] font-black uppercase tracking-[0.3em] text-kebana-blue transition hover:bg-kebana-blue hover:text-white">
                        Log Masuk
                    </a>
                    <p class="mt-8 text-[9px] font-black uppercase tracking-[0.3em] text-slate-300">
                        &copy; <?php echo date('Y'); ?> KEBANA DIGITAL | Versi 1.1.0
                    </p>
                </div>
            </section>

            <aside class="relative hidden min-h-[860px] overflow-hidden bg-slate-900 lg:block">
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
