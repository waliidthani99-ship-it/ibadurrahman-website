<?php
require_once __DIR__ . "/includes/auth.php";
require_admin_login();

$pdo = getDbConnection();
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

$currentMap = ["Pending" => "pending", "Approved" => "approved", "Rejected" => "rejected"];
$adminTitle = "Student Details";
$pageSubtitle = $r ? (($r["registration_number"] !== null && $r["registration_number"] !== "") ? $r["registration_number"] : $r["registration_id"]) : "";
$current = ($r && isset($currentMap[$r["status"]])) ? $currentMap[$r["status"]] : "registrations";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";

if (!$r) {
    echo '<div class="fade-up space-y-5">';
    flash_message();
    admin_empty_state("Registration not found", "The record you are looking for does not exist or may have been deleted.", "fa-user-slash");
    echo '</div>';
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

$regNumber = ($r["registration_number"] !== null && $r["registration_number"] !== "") ? $r["registration_number"] : null;
$val = function ($x) { return ($x !== null && trim((string)$x) !== "") ? $x : "—"; };
?>
<div class="fade-up space-y-6"><?php flash_message(); ?>

  <!-- Header row -->
  <div class="flex flex-wrap items-center justify-between gap-3">
    <a href="registrations.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors">
      <i class="fas fa-arrow-left text-xs"></i> Back to Registrations
    </a>
    <span class="text-xs text-gray-400">Last updated: <?php echo sanitize(date("d M Y, H:i", strtotime($r["updated_at"]))); ?></span>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">

    <!-- Left column: profile + actions -->
    <div class="space-y-6">
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="h-24 bg-gradient-to-r from-darkGreen to-emerald-600"></div>
        <div class="px-6 pb-6 -mt-12 text-center">
          <span class="inline-flex items-center justify-center w-24 h-24 rounded-2xl text-white text-3xl font-bold ring-4 ring-white shadow-lg <?php echo avatar_color($r["id"]); ?>">
            <?php echo sanitize(name_initials($r["full_name"])); ?>
          </span>
          <h1 class="text-xl font-bold text-gray-800 mt-4"><?php echo sanitize($r["full_name"]); ?></h1>
          <?php if ($regNumber !== null): ?>
            <p class="text-sm font-semibold text-primary font-mono mt-1"><?php echo sanitize($regNumber); ?></p>
          <?php else: ?>
            <p class="text-xs text-amber-600 font-medium mt-1"><i class="fas fa-triangle-exclamation"></i> No official number yet</p>
          <?php endif; ?>
          <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo sanitize($r["registration_id"]); ?></p>
          <div class="mt-3 flex items-center justify-center gap-2 flex-wrap">
            <?php echo status_badge($r["status"]); ?>
            <?php echo type_badge($r["student_type"]); ?>
          </div>
          <div class="mt-5 grid grid-cols-2 gap-3 text-left">
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Current Class</p>
              <p class="text-sm font-semibold text-gray-700 mt-0.5"><?php echo sanitize($val($r["current_class"])); ?></p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Admission Year</p>
              <p class="text-sm font-semibold text-gray-700 mt-0.5"><?php echo sanitize(format_admission_year($r["admission_year"])); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action panel -->
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-5">
        <div class="flex items-center justify-between mb-1">
          <h2 class="font-bold text-gray-800">Actions</h2>
          <a href="edit_student.php?id=<?php echo (int)$r["id"]; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-semibold hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-pen"></i> Edit
          </a>
        </div>
        <p class="text-xs text-gray-400 mb-4">Update the status of this student</p>
        <form method="POST" action="actions.php" class="space-y-2.5">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
          <input type="hidden" name="return" value="student">
          <button name="action" value="approve" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm">
            <i class="fas fa-circle-check"></i> Approve Application
          </button>
          <button name="action" value="reject" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 transition-colors shadow-sm">
            <i class="fas fa-circle-xmark"></i> Reject Application
          </button>
          <button name="action" value="pending" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm">
            <i class="fas fa-hourglass-half"></i> Mark as Pending
          </button>
          <div class="pt-1.5 mt-1.5 border-t border-slate-100">
            <button name="action" value="delete" onclick="return confirm('Delete this registration permanently?');" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-rose-600 text-sm font-semibold hover:bg-rose-600 hover:text-white transition-colors">
              <i class="fas fa-trash-can"></i> Delete Record
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right column: details -->
    <div class="lg:col-span-2 space-y-6">

      <!-- Student information -->
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
          <span class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center"><i class="fas fa-user-graduate"></i></span>
          <h2 class="font-bold text-gray-800">Student Information</h2>
        </div>
        <div class="p-6 grid sm:grid-cols-2 gap-x-8 gap-y-5">
          <?php
          $studentFields = [
              ["Registration Number", $regNumber !== null ? $regNumber : "Not assigned", "fa-id-card"],
              ["Registration ID", $r["registration_id"], "fa-hashtag"],
              ["Student Type", ucfirst($r["student_type"]), "fa-user-tag"],
              ["Current Class", $r["current_class"], "fa-graduation-cap"],
              ["Admission Year", format_admission_year($r["admission_year"]), "fa-calendar-days"],
              ["Date of Birth", $r["date_of_birth"] !== null && $r["date_of_birth"] !== "" ? date("d M Y", strtotime($r["date_of_birth"])) : null, "fa-cake-candles"],
              ["Gender", $r["gender"], "fa-venus-mars"],
              ["School", $r["school"], "fa-school"],
              ["Madrasa", $r["madrasa"], "fa-book-open-reader"],
              ["School Leaving Time", $r["school_leaving_time"], "fa-clock"],
          ];
          foreach ($studentFields as $f): ?>
            <div class="flex items-start gap-3">
              <span class="w-9 h-9 rounded-lg bg-slate-100 text-gray-500 flex items-center justify-center shrink-0 text-sm"><i class="fas <?php echo $f[2]; ?>"></i></span>
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wide text-gray-400"><?php echo $f[0]; ?></p>
                <p class="text-gray-800 font-medium break-words"><?php echo sanitize($val($f[1])); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="flex items-start gap-3 sm:col-span-2">
            <span class="w-9 h-9 rounded-lg bg-slate-100 text-gray-500 flex items-center justify-center shrink-0 text-sm"><i class="fas fa-location-dot"></i></span>
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Address</p>
              <p class="text-gray-800 font-medium break-words"><?php echo sanitize($val($r["address"])); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Parent / guardian -->
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
          <span class="w-9 h-9 rounded-xl bg-accent/10 text-accent flex items-center justify-center"><i class="fas fa-user-shield"></i></span>
          <h2 class="font-bold text-gray-800">Parent / Guardian Information</h2>
        </div>
        <div class="p-6 grid sm:grid-cols-2 gap-x-8 gap-y-5">
          <?php
          $parentFields = [
              ["Full Name", $r["parent_name"], "fa-user"],
              ["Phone Number", $r["parent_phone"], "fa-phone"],
              ["Email", $r["parent_email"], "fa-envelope"],
              ["Status", $r["status"], "fa-circle-info"],
          ];
          foreach ($parentFields as $f): ?>
            <div class="flex items-start gap-3">
              <span class="w-9 h-9 rounded-lg bg-slate-100 text-gray-500 flex items-center justify-center shrink-0 text-sm"><i class="fas <?php echo $f[2]; ?>"></i></span>
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wide text-gray-400"><?php echo $f[0]; ?></p>
                <?php if ($f[0] === "Phone" || $f[0] === "Phone Number"): ?>
                  <a href="tel:<?php echo sanitize($val($f[1])); ?>" class="text-primary font-medium hover:underline"><?php echo sanitize($val($f[1])); ?></a>
                <?php elseif ($f[0] === "Email" && $val($f[1]) !== "—"): ?>
                  <a href="mailto:<?php echo sanitize($f[1]); ?>" class="text-primary font-medium hover:underline break-all"><?php echo sanitize($f[1]); ?></a>
                <?php elseif ($f[0] === "Status"): ?>
                  <div class="mt-0.5"><?php echo status_badge($r["status"]); ?></div>
                <?php else: ?>
                  <p class="text-gray-800 font-medium break-words"><?php echo sanitize($val($f[1])); ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Timeline / meta -->
      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
          <span class="w-9 h-9 rounded-xl bg-slate-100 text-gray-500 flex items-center justify-center"><i class="fas fa-clock-rotate-left"></i></span>
          <h2 class="font-bold text-gray-800">Submission Timeline</h2>
        </div>
        <div class="p-6 grid sm:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-2"></span>
            <div>
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Submitted on</p>
              <p class="text-gray-800 font-medium"><?php echo sanitize(date("d M Y, H:i", strtotime($r["created_at"]))); ?></p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-primary mt-2"></span>
            <div>
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Registered on</p>
              <p class="text-gray-800 font-medium"><?php echo sanitize($r["registered_at"] !== null && $r["registered_at"] !== "" ? date("d M Y", strtotime($r["registered_at"])) : "—"); ?></p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-2"></span>
            <div>
              <p class="text-[11px] uppercase tracking-wide text-gray-400">Last updated</p>
              <p class="text-gray-800 font-medium"><?php echo sanitize(date("d M Y, H:i", strtotime($r["updated_at"]))); ?></p>
            </div>
          </div>
          <?php if (trim((string)$r["notes"]) !== ""): ?>
            <div class="sm:col-span-2">
              <p class="text-[11px] uppercase tracking-wide text-gray-400 mb-1">Notes</p>
              <p class="text-gray-700"><?php echo nl2br(sanitize($r["notes"])); ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
