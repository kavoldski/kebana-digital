<?php
/**
 * KEBANA Digital Management System - Gantt Chart View
 * File: modules/events/gantt.php
 */

use App\Core\Database;
use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$master_event = EventsHelper::getEventById($event_id);

if (!$master_event || $master_event['event_level'] !== 'MASTER') {
    echo '<div class="p-8 text-center text-red-500 font-bold">Acara tidak dijumpai atau bukan Master Event.</div>';
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$sub_events = EventsHelper::getSubEvents($event_id);

// Include master event itself in the list for visualization
$all_events = array_merge([$master_event], $sub_events);

// Find min and max dates
$min_timestamp = PHP_INT_MAX;
$max_timestamp = 0;

foreach ($all_events as &$evt) {
    $start = !empty($evt['event_date']) && $evt['event_date'] !== '0000-00-00' ? strtotime($evt['event_date']) : time();
    $end = !empty($evt['event_end_date']) && $evt['event_end_date'] !== '0000-00-00' ? strtotime($evt['event_end_date']) : $start;
    
    // Fallback if end < start
    if ($end < $start) $end = $start;
    
    $evt['parsed_start'] = $start;
    $evt['parsed_end'] = $end;
    
    if ($start < $min_timestamp) $min_timestamp = $start;
    if ($end > $max_timestamp) $max_timestamp = $end;
}
unset($evt);

if ($min_timestamp === PHP_INT_MAX) {
    $min_timestamp = time();
    $max_timestamp = time();
}

// Add padding of 5 days before min and 5 days after max for better visualization
$padding_seconds = 5 * 24 * 60 * 60;
$chart_start = $min_timestamp - $padding_seconds;
$chart_end = $max_timestamp + $padding_seconds;
$total_duration_seconds = $chart_end - $chart_start;

if ($total_duration_seconds == 0) {
    $total_duration_seconds = 86400; // avoid div by zero
}

// Generate month markers
$current_time = $chart_start;
$months = [];
while ($current_time <= $chart_end) {
    $month_start = strtotime(date('Y-m-01', $current_time));
    $next_month_start = strtotime('+1 month', $month_start);
    
    $month_start_bound = max($chart_start, $month_start);
    $month_end_bound = min($chart_end, $next_month_start);
    
    $duration = $month_end_bound - $month_start_bound;
    $width_pct = ($duration / $total_duration_seconds) * 100;
    
    if ($width_pct > 0) {
        $months[] = [
            'label' => date('M Y', $month_start),
            'width' => $width_pct
        ];
    }
    
    $current_time = $next_month_start;
}
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm border border-slate-300">
        <div>
            <h2 class="text-3xl font-black text-kebana-blue uppercase tracking-tight italic">Carta Gantt Projek</h2>
            <p class="text-xs font-black text-slate-600 uppercase tracking-widest mt-2">
                Pemerhatian Jadual: <?php echo htmlspecialchars($master_event['event_title']); ?>
            </p>
        </div>
        <a href="<?= URL_ROOT ?>/events" class="bg-slate-100 text-slate-700 border border-slate-300 px-10 py-4 text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all shadow-sm inline-flex items-center">
            <i class="fa-solid fa-arrow-left mr-4 text-lg"></i>
            KEMBALI KEPADA SENARAI
        </a>
    </div>

    <!-- Gantt Chart Container -->
    <div class="bg-white border border-slate-300 shadow-sm overflow-x-auto relative rounded-lg">
        <div class="min-w-[800px] p-8">
            
            <!-- Timeline Header -->
            <div class="flex border-b-2 border-slate-300 mb-4 ml-[20%]">
                <?php foreach ($months as $m): ?>
                <div class="text-xs font-black text-slate-600 uppercase tracking-widest px-2 py-3 border-l border-slate-300" style="width: <?php echo $m['width']; ?>%">
                    <?php echo $m['label']; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Chart Body -->
            <div class="relative space-y-4">
                <!-- Background Grid -->
                <div class="absolute top-0 bottom-0 left-[20%] right-0 flex pointer-events-none">
                    <?php foreach ($months as $m): ?>
                    <div class="border-l border-slate-300/60 h-full" style="width: <?php echo $m['width']; ?>%"></div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($all_events as $evt): 
                    $is_master = ($evt['event_level'] === 'MASTER');
                    
                    $start_offset = $evt['parsed_start'] - $chart_start;
                    $duration = $evt['parsed_end'] - $evt['parsed_start'];
                    
                    // Add 1 day minimum width for visual rendering of single-day events
                    if ($duration == 0) $duration = 86400; 

                    $left_pct = ($start_offset / $total_duration_seconds) * 100;
                    $width_pct = ($duration / $total_duration_seconds) * 100;

                    // Ensure minimum width visually
                    if ($width_pct < 0.5) $width_pct = 0.5;
                    
                    $status = strtoupper($evt['status'] ?? 'DRAFT');
                    $bar_color = 'bg-blue-400';
                    if ($is_master) $bar_color = 'bg-kebana-blue shadow-lg shadow-kebana-blue/30';
                    elseif ($status === 'APPROVED') $bar_color = 'bg-green-600 shadow-lg shadow-green-600/30';
                    elseif ($status === 'SUBMITTED') $bar_color = 'bg-amber-500 shadow-lg shadow-amber-500/30';
                    elseif ($status === 'REJECTED') $bar_color = 'bg-red-600 shadow-lg shadow-red-600/30';
                    elseif ($status === 'BRANCH APPROVED') $bar_color = 'bg-emerald-500 shadow-lg shadow-emerald-500/30';
                    else $bar_color = 'bg-slate-500 shadow-lg shadow-slate-500/30';
                ?>
                <div class="flex items-center relative z-10 group hover:bg-slate-50/50 transition-colors p-2 rounded">
                    <!-- Label Area -->
                    <div class="w-[20%] pr-4 truncate">
                        <p class="text-sm font-black uppercase tracking-tight <?php echo $is_master ? 'text-kebana-blue' : 'text-slate-800 pl-4'; ?>">
                            <?php if (!$is_master): ?><i class="fa-solid fa-level-up fa-rotate-90 text-slate-400 mr-2 text-xs"></i><?php endif; ?>
                            <?php echo htmlspecialchars($evt['event_title']); ?>
                        </p>
                        <p class="text-xs font-bold text-slate-600 uppercase mt-1 <?php echo !$is_master ? 'pl-8' : ''; ?>">
                            <?php echo htmlspecialchars($evt['cawangan_name'] ?? 'HQ'); ?> • <?php echo date('d M', $evt['parsed_start']); ?> <?php echo $evt['parsed_start'] != $evt['parsed_end'] ? ' - ' . date('d M Y', $evt['parsed_end']) : date('Y', $evt['parsed_start']); ?>
                        </p>
                    </div>
                    
                    <!-- Bar Area -->
                    <div class="w-[80%] relative h-10 bg-slate-50 rounded overflow-hidden shadow-inner border border-slate-300">
                        <div class="absolute h-full rounded transition-all duration-500 group-hover:brightness-110 flex items-center px-3 <?php echo $bar_color; ?>" 
                             style="left: <?php echo $left_pct; ?>%; width: <?php echo $width_pct; ?>%;">
                             <span class="text-xs font-black text-white uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity truncate drop-shadow-md">
                                 <?php echo $status; ?>
                             </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
