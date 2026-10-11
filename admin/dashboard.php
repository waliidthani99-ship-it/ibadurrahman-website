<?php
$adminTitle = "Dashboard";
$pageSubtitle = "Overview of student registrations";
$current = "dashboard";
require_once __DIR__ . "/includes/auth.php";
require_admin_login();
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";

$pdo = getDbConnection();
function count_status($pdo, $status = null) {
    if ($status === null) {
        return (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE status = ?");
    $stmt->execute([$status]);
    return (int)$stmt->fetchColumn();
}
$total    = count_status($pdo);
$pending  = count_status($pdo, "Pending");
$approved = count_status($pdo, "Approved");
$rejected = count_status($pdo, "Rejected");
$active   = count_status($pdo, "Active");
$legacy   = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE student_type = 'legacy'")->fetchColumn();
$recent = $pdo->query("SELECT * FROM students ORDER BY COALESCE(registered_at, created_at) DESC, id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

$pct = function ($n) use ($total) { return $total > 0 ? round(($n / $total) * 100) : 0; };

$cards = [
    ["Total Students", $total,    "fa-users",            "indigo",  "registrations.php",                "All students"],
    ["Pending",        $pending,  "fa-hourglass-half",   "amber",   "registrations.php?status=Pending",  "Awaiting review"],
    ["Approved",       $approved, "fa-circle-check",     "emerald", "registrations.php?status=Approved", "Accepted students"],
    ["Active",         $active,   "fa-user-check",       "sky",     "registrations.php?status=Active",   "Enrolled students"],
    ["Legacy",         $legacy,   "fa-clock-rotate-left","violet",  "registrations.php?type=legacy",     "Previously enrolled"],
];
?>
<div class="fade-up space-y-6"><?php flash_message(); ?>

  <!-- Stat cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 md:gap-6">
    <?php foreach ($cards as $c): ?>
      <?php $n = $c[1]; $color = $c[3]; ?>
      <a href="<?php echo $c[4]; ?>" class="group flex flex-col h-full min-w-0 rounded-2xl bg-white p-4 sm:p-5 shadow-sm ring-1 ring-slate-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h3 class="text-xs sm:text-sm font-medium text-slate-500 leading-snug break-words"><?php echo sanitize($c[0]); ?></h3>
            <p class="mt-2 text-2xl sm:text-3xl font-bold text-slate-800 leading-none tabular-nums"><?php echo $n; ?></p>
          </div>
          <span class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-<?php echo $color; ?>-50 text-<?php echo $color; ?>-600 text-base sm:text-xl shrink-0 group-hover:scale-105 transition-transform">
            <i class="fas <?php echo $c[2]; ?>"></i>
          </span>
        </div>
        <p class="mt-auto pt-3 text-[11px] sm:text-xs text-slate-400 flex items-center gap-1 min-w-0">
          <i class="fas fa-arrow-right text-[9px] opacity-0 group-hover:opacity-100 transition-opacity shrink-0"></i>
          <span class="truncate"><?php echo sanitize($c[5]); ?></span>
        </p>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">

    <!-- Recent registrations -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h2 class="font-bold text-gray-800">Recent Registrations</h2>
          <p class="text-xs text-gray-400">Latest applications submitted</p>
        </div>
        <a href="registrations.php" class="text-sm font-medium text-primary hover:text-darkGreen inline-flex items-center gap-1.5">
          View all <i class="fas fa-arrow-right text-xs"></i>
        </a>
      </div>

      <?php if (empty($recent)): ?>
        <?php admin_empty_state("No registrations yet", "New student applications will appear here once submitted.", "fa-clipboard-list"); ?>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs uppercase tracking-wide text-gray-400 bg-slate-50/70">
                <th class="px-5 py-3 font-semibold">Student</th>
                <th class="px-5 py-3 font-semibold hidden md:table-cell">Parent</th>
                <th class="px-5 py-3 font-semibold hidden lg:table-cell">Date</th>
                <th class="px-5 py-3 font-semibold">Status</th>
                <th class="px-5 py-3 font-semibold text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($recent as $r): ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                      <?php echo initial_avatar($r["full_name"], $r["id"]); ?>
                      <?php $regNo = ($r["registration_number"] !== null && $r["registration_number"] !== "") ? $r["registration_number"] : $r["registration_id"]; ?>
                      <div class="min-w-0">
                        <p class="font-semibold text-gray-800 truncate max-w-[180px]"><?php echo sanitize($r["full_name"]); ?></p>
                        <p class="text-xs text-gray-400 font-mono"><?php echo sanitize($regNo); ?></p>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3 hidden md:table-cell">
                    <p class="text-gray-700"><?php echo sanitize($r["parent_name"]); ?></p>
                    <p class="text-xs text-gray-400"><?php echo sanitize($r["parent_phone"]); ?></p>
                  </td>
                  <td class="px-5 py-3 hidden lg:table-cell text-gray-500 whitespace-nowrap"><?php echo sanitize(date("d M Y", strtotime($r["registered_at"] !== null && $r["registered_at"] !== "" ? $r["registered_at"] : $r["created_at"]))); ?></td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1.5"><?php echo status_badge($r["status"]); echo type_badge($r["student_type"]); ?></div>
                  </td>
                  <td class="px-5 py-3 text-right">
                    <a href="student.php?id=<?php echo (int)$r["id"]; ?>" title="View details" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-colors">
                      <i class="fas fa-eye text-xs"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Status breakdown + quick actions -->
    <div class="space-y-6">
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-5">
        <h2 class="font-bold text-gray-800 mb-4">Status Breakdown</h2>
        <?php
        $bars = [
            ["Approved", $approved, $pct($approved), "bg-emerald-500", "text-emerald-600"],
            ["Active",   $active,   $pct($active),   "bg-sky-500",     "text-sky-600"],
            ["Pending",  $pending,  $pct($pending),  "bg-amber-500",   "text-amber-600"],
            ["Rejected", $rejected, $pct($rejected), "bg-rose-500",    "text-rose-600"],
        ];
        foreach ($bars as $b): ?>
          <div class="mb-4 last:mb-0">
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-sm font-medium text-gray-600"><?php echo $b[0]; ?></span>
              <span class="text-sm font-semibold <?php echo $b[4]; ?>"><?php echo $b[1]; ?> <span class="text-gray-400 font-normal">(<?php echo $b[2]; ?>%)</span></span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
              <div class="h-full rounded-full <?php echo $b[3]; ?> transition-all duration-500" style="width: <?php echo $b[2]; ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
          <span class="text-sm text-gray-500">Total students</span>
          <span class="text-lg font-bold text-gray-800"><?php echo $total; ?></span>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-5">
        <h2 class="font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="space-y-2">
          <a href="add_student.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-accent/10 text-accent hover:bg-accent/20 transition-colors">
            <i class="fas fa-user-plus w-5 text-center"></i>
            <span class="text-sm font-medium flex-1">Add an existing student</span>
            <i class="fas fa-chevron-right text-xs"></i>
          </a>
          <a href="registrations.php?status=Pending" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
            <i class="fas fa-hourglass-half w-5 text-center"></i>
            <span class="text-sm font-medium flex-1">Review pending applications</span>
            <i class="fas fa-chevron-right text-xs"></i>
          </a>
          <a href="registrations.php?type=legacy" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 transition-colors">
            <i class="fas fa-clock-rotate-left w-5 text-center"></i>
            <span class="text-sm font-medium flex-1">View legacy students</span>
            <i class="fas fa-chevron-right text-xs"></i>
          </a>
          <a href="registrations.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
            <i class="fas fa-users w-5 text-center"></i>
            <span class="text-sm font-medium flex-1">All students</span>
            <i class="fas fa-chevron-right text-xs"></i>
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
