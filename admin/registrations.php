<?php
$adminTitle = "Registrations";
$current = "registrations";
require_once __DIR__ . "/includes/auth.php";
require_admin_login();
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";
$pdo = getDbConnection();
$status = trim(isset($_GET["status"]) ? $_GET["status"] : "");
$search = trim(isset($_GET["q"]) ? $_GET["q"] : "");
$where = "1=1";
$params = [];
if ($status && in_array($status, ["Pending","Approved","Rejected"])) { $where .= " AND status=?"; $params[] = $status; }
if ($search) {
  $where .= " AND (registration_id LIKE ? OR full_name LIKE ? OR parent_name LIKE ? OR parent_phone LIKE ?)";
  $s = "%$search%";
  $params = array_merge($params, [$s,$s,$s,$s]);
}
$stmt = $pdo->prepare("SELECT * FROM students WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
function status_badge($status) {
  $map = ["Pending"=>"bg-yellow-100 text-yellow-800", "Approved"=>"bg-green-100 text-green-800", "Rejected"=>"bg-red-100 text-red-800"];
  $cls = isset($map[$status]) ? $map[$status] : "bg-gray-100 text-gray-800";
  return '<span class="px-2 py-1 rounded text-xs font-semibold ' . $cls . '">' . sanitize($status) . '</span>';
}
?>
<main class="flex-1 p-6">
<h1 class="text-xl font-bold mb-4">Registrations</h1>
<form class="mb-4 flex gap-2 flex-wrap">
  <input name="q" value="<?php echo sanitize($search); ?>" placeholder="Search..." class="border p-2 rounded">
  <select name="status" class="border p-2 rounded">
    <option value="">All</option>
    <option <?php if($status=="Pending") echo "selected"; ?>>Pending</option>
    <option <?php if($status=="Approved") echo "selected"; ?>>Approved</option>
    <option <?php if($status=="Rejected") echo "selected"; ?>>Rejected</option>
  </select>
  <button class="bg-primary text-white px-4 py-2 rounded">Filter</button>
</form>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="min-w-full text-sm">
<thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Reg ID</th><th class="px-4 py-2 text-left">Student</th><th class="px-4 py-2 text-left">Parent</th><th class="px-4 py-2 text-left">Phone</th><th class="px-4 py-2 text-left">Date</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left">Actions</th></tr></thead>
<tbody>
<?php if(empty($rows)): ?><tr><td colspan="7" class="px-4 py-4 text-center text-gray-500">No records</td></tr><?php else: foreach($rows as $r): ?>
<tr class="border-t align-top">
  <td class="px-4 py-2 whitespace-nowrap"><?php echo sanitize($r["registration_id"]); ?></td>
  <td class="px-4 py-2"><?php echo sanitize($r["full_name"]); ?></td>
  <td class="px-4 py-2"><?php echo sanitize($r["parent_name"]); ?></td>
  <td class="px-4 py-2 whitespace-nowrap"><?php echo sanitize($r["parent_phone"]); ?></td>
  <td class="px-4 py-2 whitespace-nowrap"><?php echo date("Y-m-d", strtotime($r["created_at"])); ?></td>
  <td class="px-4 py-2"><?php echo status_badge($r["status"]); ?></td>
  <td class="px-4 py-2 whitespace-nowrap">
    <a href="student.php?id=<?php echo (int)$r["id"]; ?>" class="text-blue-600 hover:underline mr-2">View</a>
    <form method="POST" action="actions.php" class="inline">
      <?php csrf_field(); ?>
      <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
      <button name="action" value="approve" class="text-green-600 hover:underline mr-2">Approve</button>
      <button name="action" value="reject" class="text-red-600 hover:underline mr-2">Reject</button>
      <button name="action" value="delete" class="text-gray-500 hover:underline" onclick="return confirm('Delete this registration?');">Delete</button>
    </form>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>