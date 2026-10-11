<?php
$current = isset($current) ? $current : "";
$adminName = isset($_SESSION["admin_full_name"]) && $_SESSION["admin_full_name"] !== ""
    ? $_SESSION["admin_full_name"]
    : (isset($_SESSION["admin_username"]) ? $_SESSION["admin_username"] : "Administrator");

$counts = ["Pending" => 0, "Approved" => 0, "Rejected" => 0, "Active" => 0, "Total" => 0, "Legacy" => 0];
try {
    $pdoNav = getDbConnection();
    $counts["Total"] = (int)$pdoNav->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $counts["Legacy"] = (int)$pdoNav->query("SELECT COUNT(*) FROM students WHERE student_type = 'legacy'")->fetchColumn();
    foreach ($pdoNav->query("SELECT status, COUNT(*) AS c FROM students GROUP BY status") as $row) {
        if (isset($counts[$row["status"]])) $counts[$row["status"]] = (int)$row["c"];
    }
} catch (Exception $e) {
    // navigation still renders even if counts fail
}

$mainNav = [
    "dashboard"     => ["dashboard.php",     "fa-gauge-high",    "Dashboard",   $counts["Total"]],
    "registrations" => ["registrations.php", "fa-users",         "All Students", $counts["Total"]],
    "add_student"   => ["add_student.php",   "fa-user-plus",     "Add Student", false],
];
$statusNav = [
    "pending"  => ["registrations.php?status=Pending",  "fa-hourglass-half", "Pending",  $counts["Pending"]],
    "approved" => ["registrations.php?status=Approved", "fa-circle-check",   "Approved", $counts["Approved"]],
    "active"   => ["registrations.php?status=Active",   "fa-user-check",     "Active",   $counts["Active"]],
    "rejected" => ["registrations.php?status=Rejected", "fa-circle-xmark",   "Rejected", $counts["Rejected"]],
];
$typeNav = [
    "legacy" => ["registrations.php?type=legacy", "fa-clock-rotate-left", "Legacy", $counts["Legacy"]],
];
?>
<aside id="adminSidebar" class="fixed md:sticky top-0 left-0 z-50 md:z-auto h-screen w-64 shrink-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 bg-gradient-to-b from-darkGreen to-[#053d2e] text-white flex flex-col shadow-2xl">

  <!-- Brand -->
  <div class="flex items-center gap-3 px-5 py-[18px] border-b border-white/10">
    <img src="../assets/logo.png" alt="Logo" class="h-10 w-10 rounded-full bg-white/95 p-0.5 object-contain shadow">
    <div class="leading-tight min-w-0">
      <div class="font-bold text-[15px] truncate">Madrasatul Ibadu-rrahman</div>
      <div class="text-[11px] text-white/50 tracking-wide uppercase">Admin Panel</div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 overflow-y-auto px-3 py-4">
    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40">Main</p>
    <div class="space-y-1">
      <?php foreach ($mainNav as $key => $item): $active = ($current === $key); ?>
        <a href="<?php echo $item[0]; ?>" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo $active ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
          <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[15px] transition-colors <?php echo $active ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'; ?>">
            <i class="fas <?php echo $item[1]; ?>"></i>
          </span>
          <span class="flex-1"><?php echo sanitize($item[2]); ?></span>
          <?php if ($item[3] !== false): ?>
            <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-white/70"><?php echo (int)$item[3]; ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40">By Status</p>
    <div class="space-y-1">
      <?php foreach ($statusNav as $key => $item): $active = ($current === $key); ?>
        <a href="<?php echo $item[0]; ?>" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo $active ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
          <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[15px] transition-colors <?php echo $active ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'; ?>">
            <i class="fas <?php echo $item[1]; ?>"></i>
          </span>
          <span class="flex-1"><?php echo sanitize($item[2]); ?></span>
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-white/70"><?php echo (int)$item[3]; ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40">By Type</p>
    <div class="space-y-1">
      <?php foreach ($typeNav as $key => $item): $active = ($current === $key); ?>
        <a href="<?php echo $item[0]; ?>" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo $active ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
          <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[15px] transition-colors <?php echo $active ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'; ?>">
            <i class="fas <?php echo $item[1]; ?>"></i>
          </span>
          <span class="flex-1"><?php echo sanitize($item[2]); ?></span>
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-white/70"><?php echo (int)$item[3]; ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40">Website</p>
    <a href="../index.html" target="_blank" rel="noopener" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200">
      <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[15px] bg-white/5 group-hover:bg-white/10"><i class="fas fa-globe"></i></span>
      <span class="flex-1">View Site</span>
      <i class="fas fa-arrow-up-right-from-square text-[11px] text-white/40"></i>
    </a>
  </nav>

  <!-- Logout -->
  <div class="p-3 border-t border-white/10">
    <a href="logout.php" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-white/80 hover:bg-rose-500/20 hover:text-white transition-all duration-200">
      <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[15px] bg-white/5 group-hover:bg-rose-500/30"><i class="fas fa-right-from-bracket"></i></span>
      <span class="flex-1">Logout</span>
    </a>
  </div>
</aside>

<!-- Content column -->
<div class="flex-1 flex flex-col min-w-0 min-h-screen">

  <!-- Topbar -->
  <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="flex items-center justify-between gap-3 px-4 md:px-6 lg:px-8 py-3">
      <div class="flex items-center gap-3 min-w-0">
        <button id="menuToggle" aria-label="Menu" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
          <i class="fas fa-bars"></i>
        </button>
        <div class="min-w-0">
          <h1 class="text-lg md:text-xl font-bold text-gray-800 truncate"><?php echo sanitize($adminTitle); ?></h1>
          <?php if ($pageSubtitle !== ""): ?>
            <p class="text-xs md:text-sm text-gray-500 truncate"><?php echo sanitize($pageSubtitle); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex items-center gap-2 md:gap-3">
        <a href="../index.html" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition-colors">
          <i class="fas fa-globe text-xs"></i><span>View Site</span>
        </a>
        <div class="relative">
          <button id="adminMenuBtn" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl hover:bg-slate-100 transition-colors">
            <?php echo initial_avatar($adminName, $_SESSION["admin_id"] ?? 0, "w-9 h-9"); ?>
            <span class="hidden sm:block text-left leading-tight">
              <span class="block text-sm font-semibold text-gray-700"><?php echo sanitize($adminName); ?></span>
              <span class="block text-[11px] text-gray-400">Administrator</span>
            </span>
            <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
          </button>
          <div id="adminMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg ring-1 ring-black/5 py-1.5 z-50">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-sm font-semibold text-gray-700 truncate"><?php echo sanitize($adminName); ?></p>
              <p class="text-xs text-gray-400">Signed in</p>
            </div>
            <a href="../index.html" target="_blank" rel="noopener" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-slate-50">
              <i class="fas fa-globe w-4 text-center text-gray-400"></i>View Site
            </a>
            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
              <i class="fas fa-right-from-bracket w-4 text-center"></i>Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="flex-1 p-4 md:p-6 lg:p-8">
