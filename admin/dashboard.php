<?php
$adminTitle = "Dashboard";
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
$total = count_status($pdo);
$pending = count_status($pdo, "Pending");
$approved = count_status($pdo, "Approved");
$rejected = count_status($pdo, "Rejected");
$recent = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
function badge($status) {
  $map = ["Pending"=>"bg-yellow-100 text-yellow-800", "Approved"=>"bg-green-100 text-green-800", "Rejected"=>"bg-red-100 text-red-800"];
  $cls = isset($map[$status]) ? $map[$status] : "bg-gray-100 text-gray-800";
  return '<span class="px-2 py-1 rounded text-xs font-semibold ' . $cls . '">' . sanitize($status) . '</span>';
}
?>
<main class="flex-1 p-6">
<h1 class="text-xl font-bold mb-4">Dashboard</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white p-4 rounded shadow"><div class="text-gray-500 text-sm">Total</div><div class="text-2xl font-bold"><?php echo $total; ?></div></div>
  <div class="bg-white p-4 rounded shadow"><div class="text-gray-500 text-sm">Pending</div><div class="text-2xl font-bold text-yellow-600"><?php echo $pending; ?></div></div>
  <div class="bg-white p-4 rounded shadow"><div class="text-gray-500 text-sm">Approved</div><div class="text-2xl font-bold text-green-600"><?php echo $approved; ?></div></div>
  <div class="bg-white p-4 rounded shadow"><div class="text-gray-500 text-sm">Rejected</div><div class="text-2xl font-bold text-red-600"><?php echo $rejected; ?></div></div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
  <div class="px-4 py-3 border-b font-semibold">Recent Registrations</div>
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Reg ID</th><th class="px-4 py-2 text-left">Student</th><th class="px-4 py-2 text-left">Parent</th><th class="px-4 py-2 text-left">Date</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left"></th></tr></thead>
    <tbody>
    <?php if (empty($recent)): ?>
      <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No registrations yet</td></tr>
    <?php else: foreach ($recent as $r): ?>
      <tr class="border-t">
        <td class="px-4 py-2 whitespace-nowrap"><?php echo sanitize($r["registration_id"]); ?></td>
        <td class="px-4 py-2"><?php echo sanitize($r["full_name"]); ?></td>
        <td class="px-4 py-2"><?php echo sanitize($r["parent_name"]); ?></td>
        <td class="px-4 py-2 whitespace-nowrap"><?php echo date("Y-m-d", strtotime($r["created_at"])); ?></td>
        <td class="px-4 py-2"><?php echo badge($r["status"]); ?></td>
        <td class="px-4 py-2 whitespace-nowrap"><a href="student.php?id=<?php echo (int)$r["id"]; ?>" class="text-blue-600 hover:underline">View</a></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>