<?php
$adminTitle = "Student Details";
$current = "registrations";
require_once __DIR__ . "/includes/auth.php";
require_admin_login();
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";
$pdo = getDbConnection();
$id = (int)(isset($_GET["id"]) ? $_GET["id"] : 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { die("Not found"); }
function e($v){ return htmlspecialchars(isset($v) ? $v : "", ENT_QUOTES, "UTF-8"); }
?>
<main class="flex-1 p-6">
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-bold">Student Details</h1>
  <a href="registrations.php" class="text-sm text-gray-600 hover:underline">&larr; Back to Registrations</a>
</div>

<div class="bg-white p-4 rounded shadow mb-4 flex flex-wrap items-center gap-3">
  <span class="text-sm text-gray-600">Status:</span>
  <form method="POST" action="actions.php" class="flex flex-wrap gap-2 items-center">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
    <input type="hidden" name="return" value="student">
    <button name="action" value="approve" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Approve</button>
    <button name="action" value="reject" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Reject</button>
    <button name="action" value="pending" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">Set Pending</button>
    <button name="action" value="delete" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700" onclick="return confirm('Delete this registration?');">Delete</button>
  </form>
</div>

<div class="grid md:grid-cols-2 gap-4">
<div class="bg-white p-4 rounded shadow">
  <h2 class="font-semibold mb-2">STUDENT INFORMATION</h2>
  <p><b>Registration ID:</b> <?php echo e($r["registration_id"]); ?></p>
  <p><b>Full Name:</b> <?php echo e($r["full_name"]); ?></p>
  <p><b>Date of Birth:</b> <?php echo e($r["date_of_birth"]); ?></p>
  <p><b>Gender:</b> <?php echo e($r["gender"]); ?></p>
  <p><b>Address:</b> <?php echo e($r["address"]); ?></p>
  <p><b>School:</b> <?php echo e($r["school"]); ?></p>
  <p><b>Madrasa:</b> <?php echo e($r["madrasa"]); ?></p>
  <p><b>School Leaving Time:</b> <?php echo e($r["school_leaving_time"]); ?></p>
  <p><b>Status:</b> <?php echo e($r["status"]); ?></p>
</div>
<div class="bg-white p-4 rounded shadow">
  <h2 class="font-semibold mb-2">PARENT/GUARDIAN INFORMATION</h2>
  <p><b>Parent Name:</b> <?php echo e($r["parent_name"]); ?></p>
  <p><b>Phone:</b> <?php echo e($r["parent_phone"]); ?></p>
  <p><b>Email:</b> <?php echo e($r["parent_email"]); ?></p>
</div>
</div>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>