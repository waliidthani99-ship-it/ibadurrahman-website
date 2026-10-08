<?php
$current = $current ?? "";
$navItems = [
  "dashboard"     => ["dashboard.php", "fa-gauge", "Dashboard"],
  "registrations" => ["registrations.php", "fa-clipboard-list", "Registrations"],
];
?>
<aside id="adminSidebar" class="fixed md:sticky top-0 left-0 z-50 md:z-auto h-screen w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 bg-darkGreen text-white flex flex-col">
  <div class="px-4 py-5 border-b border-white/10 font-bold text-lg">Madrasatul Ibadu-rrahman</div>
  <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
    <?php foreach ($navItems as $key => $item): ?>
      <a href="<?php echo $item[0]; ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?php echo $current === $key ? "bg-white/15 font-semibold" : "hover:bg-white/10"; ?>">
        <i class="fas <?php echo $item[1]; ?> w-5 text-center"></i><span><?php echo $item[2]; ?></span>
      </a>
    <?php endforeach; ?>
    <div class="pt-3 mt-3 border-t border-white/10">
      <a href="registrations.php?status=Pending" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10"><i class="fas fa-hourglass-half w-5 text-center"></i><span>Pending</span></a>
      <a href="registrations.php?status=Approved" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10"><i class="fas fa-circle-check w-5 text-center"></i><span>Approved</span></a>
      <a href="registrations.php?status=Rejected" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10"><i class="fas fa-circle-xmark w-5 text-center"></i><span>Rejected</span></a>
    </div>
  </nav>
  <div class="p-3 border-t border-white/10">
    <a href="logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10"><i class="fas fa-right-from-bracket w-5 text-center"></i><span>Logout</span></a>
  </div>
</aside>