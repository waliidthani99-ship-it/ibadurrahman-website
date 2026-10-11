<?php
require_once __DIR__ . "/includes/auth.php";
require_admin_login();

$pdo = getDbConnection();

$status  = isset($_GET["status"]) ? trim($_GET["status"]) : "";
$type    = isset($_GET["type"]) ? trim($_GET["type"]) : "";
$class   = isset($_GET["class"]) ? trim($_GET["class"]) : "";
$search  = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$page    = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;

if (!in_array($status, ["Pending", "Approved", "Rejected", "Active"], true)) $status = "";
if (!in_array($type, ["legacy", "new"], true)) $type = "";

$where = [];
$params = [];
if ($status !== "") { $where[] = "status = ?"; $params[] = $status; }
if ($type !== "")   { $where[] = "student_type = ?"; $params[] = $type; }
if ($class !== "")  { $where[] = "current_class = ?"; $params[] = $class; }
if ($search !== "") {
    $where[] = "(full_name LIKE ? OR registration_number LIKE ? OR registration_id LIKE ? OR parent_name LIKE ? OR parent_phone LIKE ?)";
    $like = "%" . $search . "%";
    array_push($params, $like, $like, $like, $like, $like);
}
$whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students" . $whereSql);
$countStmt->execute($params);
$count = (int)$countStmt->fetchColumn();

$perPage = 10;
$totalPages = max(1, (int)ceil($count / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM students" . $whereSql . " ORDER BY COALESCE(registered_at, created_at) DESC, id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classOptions = $pdo->query("SELECT DISTINCT current_class FROM students WHERE current_class IS NOT NULL AND current_class <> '' ORDER BY current_class")->fetchAll(PDO::FETCH_COLUMN);

$currentMap = ["" => "registrations", "Pending" => "pending", "Approved" => "approved", "Rejected" => "rejected", "Active" => "active"];
$current = $currentMap[$status];
if ($type === "legacy") $current = "legacy";
$adminTitle = $status !== "" ? $status . " Students" : "All Students";
$pageSubtitle = $count . " " . ($count === 1 ? "student" : "students") . " found";

$hasFilters = ($status !== "" || $type !== "" || $class !== "" || $search !== "");
$retHidden = function () use ($status, $type, $class, $search, $page) {
    echo '<input type="hidden" name="ret_status" value="' . sanitize($status) . '">';
    echo '<input type="hidden" name="ret_type" value="' . sanitize($type) . '">';
    echo '<input type="hidden" name="ret_class" value="' . sanitize($class) . '">';
    echo '<input type="hidden" name="ret_q" value="' . sanitize($search) . '">';
    echo '<input type="hidden" name="ret_page" value="' . (int)$page . '">';
};
$pageLink = function ($p) use ($status, $type, $class, $search) {
    $q = [];
    if ($status !== "") $q["status"] = $status;
    if ($type !== "") $q["type"] = $type;
    if ($class !== "") $q["class"] = $class;
    if ($search !== "") $q["q"] = $search;
    $q["page"] = $p;
    return "registrations.php?" . http_build_query($q);
};

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";
?>
<div class="fade-up space-y-5">

  <?php flash_message(); ?>

  <!-- Filter bar -->
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-4">
    <form method="GET" class="space-y-3">
      <div class="flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="relative flex-1">
          <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input type="text" name="q" value="<?php echo sanitize($search); ?>" placeholder="Search by name, registration number, parent or phone..."
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary focus:bg-white transition-all">
        </div>
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-3">
          <select name="status" class="py-2.5 pl-3 pr-9 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary focus:bg-white transition-all">
            <option value="">All statuses</option>
            <?php foreach (["Pending", "Approved", "Rejected", "Active"] as $s): ?>
              <option value="<?php echo $s; ?>" <?php if ($status === $s) echo "selected"; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
          </select>
          <select name="type" class="py-2.5 pl-3 pr-9 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary focus:bg-white transition-all">
            <option value="">All types</option>
            <option value="new"    <?php if ($type === "new")    echo "selected"; ?>>New</option>
            <option value="legacy" <?php if ($type === "legacy") echo "selected"; ?>>Legacy</option>
          </select>
          <select name="class" class="col-span-2 sm:col-auto py-2.5 pl-3 pr-9 rounded-xl border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary focus:bg-white transition-all">
            <option value="">All classes</option>
            <?php foreach ($classOptions as $co): ?>
              <option value="<?php echo sanitize($co); ?>" <?php if ($class === $co) echo "selected"; ?>><?php echo sanitize($co); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-darkGreen transition-colors shadow-sm">
            <i class="fas fa-filter text-xs"></i><span>Filter</span>
          </button>
          <?php if ($hasFilters): ?>
            <a href="registrations.php" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-slate-500 text-sm font-medium hover:bg-slate-100 transition-colors">
              <i class="fas fa-xmark"></i><span class="hidden sm:inline">Clear</span>
            </a>
          <?php endif; ?>
          <a href="add_student.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-accent text-white text-sm font-semibold hover:brightness-95 transition-all shadow-sm">
            <i class="fas fa-user-plus text-xs"></i><span class="hidden sm:inline">Add Student</span>
          </a>
        </div>
      </div>
    </form>
  </div>

  <?php if ($count === 0): ?>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100">
      <?php admin_empty_state("No students found", $hasFilters ? "Try adjusting your search or filters." : "New applications and added students will appear here.", "fa-clipboard-list"); ?>
    </div>
  <?php else: ?>

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-400 bg-slate-50/70">
              <th class="px-5 py-3.5 font-semibold">Student</th>
              <th class="px-5 py-3.5 font-semibold">Type</th>
              <th class="px-5 py-3.5 font-semibold">Class</th>
              <th class="px-5 py-3.5 font-semibold hidden lg:table-cell">Parent / Phone</th>
              <th class="px-5 py-3.5 font-semibold hidden xl:table-cell">Year</th>
              <th class="px-5 py-3.5 font-semibold hidden xl:table-cell">Registered</th>
              <th class="px-5 py-3.5 font-semibold">Status</th>
              <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($rows as $r): $regNo = ($r["registration_number"] !== null && $r["registration_number"] !== "") ? $r["registration_number"] : "—"; $dateVal = $r["registered_at"] !== null && $r["registered_at"] !== "" ? $r["registered_at"] : $r["created_at"]; ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <?php echo initial_avatar($r["full_name"], $r["id"]); ?>
                    <div class="min-w-0">
                      <p class="font-semibold text-gray-800 truncate max-w-[220px]"><?php echo sanitize($r["full_name"]); ?></p>
                      <p class="text-xs text-gray-400 font-mono"><?php echo sanitize($regNo); ?></p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5"><?php echo type_badge($r["student_type"]); ?></td>
                <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap"><?php echo sanitize(($r["current_class"] !== null && $r["current_class"] !== "") ? $r["current_class"] : "—"); ?></td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <p class="text-gray-700"><?php echo sanitize($r["parent_name"]); ?></p>
                  <p class="text-xs text-gray-400"><?php echo sanitize($r["parent_phone"]); ?></p>
                </td>
                <td class="px-5 py-3.5 hidden xl:table-cell text-gray-600 whitespace-nowrap"><?php echo sanitize(format_admission_year($r["admission_year"])); ?></td>
                <td class="px-5 py-3.5 hidden xl:table-cell text-gray-500 whitespace-nowrap"><?php echo sanitize(date("d M Y", strtotime($dateVal))); ?></td>
                <td class="px-5 py-3.5"><?php echo status_badge($r["status"]); ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="student.php?id=<?php echo (int)$r["id"]; ?>" title="View details" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-colors">
                      <i class="fas fa-eye text-xs"></i>
                    </a>
                    <form method="POST" action="actions.php" class="contents">
                      <?php csrf_field(); $retHidden(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
                      <?php if ($r["status"] !== "Approved"): ?>
                        <button name="action" value="approve" title="Approve" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors">
                          <i class="fas fa-check text-xs"></i>
                        </button>
                      <?php endif; ?>
                      <?php if ($r["status"] !== "Rejected"): ?>
                        <button name="action" value="reject" title="Reject" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors">
                          <i class="fas fa-xmark text-xs"></i>
                        </button>
                      <?php endif; ?>
                      <button name="action" value="delete" title="Delete" onclick="return confirm('Delete this student? This cannot be undone.');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-gray-500 hover:bg-rose-600 hover:text-white transition-colors">
                        <i class="fas fa-trash-can text-xs"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
      <?php foreach ($rows as $r): $regNo = ($r["registration_number"] !== null && $r["registration_number"] !== "") ? $r["registration_number"] : "—"; $dateVal = $r["registered_at"] !== null && $r["registered_at"] !== "" ? $r["registered_at"] : $r["created_at"]; ?>
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-4">
          <div class="flex items-start gap-3">
            <?php echo initial_avatar($r["full_name"], $r["id"]); ?>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-gray-800 truncate"><?php echo sanitize($r["full_name"]); ?></p>
              <p class="text-xs text-gray-400 font-mono"><?php echo sanitize($regNo); ?></p>
            </div>
            <?php echo status_badge($r["status"]); ?>
          </div>
          <div class="mt-3 flex items-center gap-2 flex-wrap">
            <?php echo type_badge($r["student_type"]); ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600"><i class="fas fa-graduation-cap"></i><?php echo sanitize(($r["current_class"] !== null && $r["current_class"] !== "") ? $r["current_class"] : "—"); ?></span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Parent</p>
              <p class="text-gray-700 truncate"><?php echo sanitize($r["parent_name"]); ?></p>
            </div>
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Phone</p>
              <p class="text-gray-700 truncate"><?php echo sanitize($r["parent_phone"]); ?></p>
            </div>
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Admission Year</p>
              <p class="text-gray-700"><?php echo sanitize(format_admission_year($r["admission_year"])); ?></p>
            </div>
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Registered</p>
              <p class="text-gray-700"><?php echo sanitize(date("d M Y", strtotime($dateVal))); ?></p>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <a href="student.php?id=<?php echo (int)$r["id"]; ?>" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-colors">
              <i class="fas fa-eye text-xs"></i>View
            </a>
            <form method="POST" action="actions.php" class="contents">
              <?php csrf_field(); $retHidden(); ?>
              <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
              <?php if ($r["status"] !== "Approved"): ?>
                <button name="action" value="approve" title="Approve" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors"><i class="fas fa-check"></i></button>
              <?php endif; ?>
              <?php if ($r["status"] !== "Rejected"): ?>
                <button name="action" value="reject" title="Reject" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors"><i class="fas fa-xmark"></i></button>
              <?php endif; ?>
              <button name="action" value="delete" title="Delete" onclick="return confirm('Delete this student?');" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-600 hover:bg-rose-600 hover:text-white transition-colors"><i class="fas fa-trash-can"></i></button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <?php
      $baseQuery = [];
      if ($status !== "") $baseQuery["status"] = $status;
      if ($type !== "") $baseQuery["type"] = $type;
      if ($class !== "") $baseQuery["class"] = $class;
      if ($search !== "") $baseQuery["q"] = $search;
      $linkFor = function ($p) use ($baseQuery) {
          $baseQuery["page"] = $p;
          return "registrations.php?" . http_build_query($baseQuery);
      };
      ?>
      <div class="flex items-center justify-between gap-3 pt-1">
        <p class="text-xs text-gray-400">Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
        <div class="flex items-center gap-1.5 flex-wrap">
          <a href="<?php echo $page <= 1 ? '#' : $linkFor($page - 1); ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium <?php echo $page <= 1 ? 'bg-slate-50 text-slate-300 pointer-events-none' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'; ?>">
            <i class="fas fa-chevron-left text-xs"></i>
          </a>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo $linkFor($i); ?>" class="inline-flex items-center justify-center min-w-[2.25rem] px-3 py-2 rounded-lg text-sm font-semibold <?php echo $i === $page ? 'bg-primary text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'; ?>"><?php echo $i; ?></a>
          <?php endfor; ?>
          <a href="<?php echo $page >= $totalPages ? '#' : $linkFor($page + 1); ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium <?php echo $page >= $totalPages ? 'bg-slate-50 text-slate-300 pointer-events-none' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'; ?>">
            <i class="fas fa-chevron-right text-xs"></i>
          </a>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
