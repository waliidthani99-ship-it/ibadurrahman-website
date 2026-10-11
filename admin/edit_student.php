<?php
require_once __DIR__ . "/includes/auth.php";
require_admin_login();

$pdo = getDbConnection();
$id = isset($_GET["id"]) ? (int)$_GET["id"] : (isset($_POST["id"]) ? (int)$_POST["id"] : 0);

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
    set_flash("error", "The student you tried to edit does not exist.");
    header("Location: registrations.php");
    exit;
}

$formErrors = [];
$data = $existing;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"])) {
        $formErrors[] = "Your session expired. Please review the form and submit again.";
    } else {
        $data["full_name"]           = strtoupper(trim($_POST["full_name"] ?? ""));
        $data["date_of_birth"]       = trim($_POST["date_of_birth"] ?? "");
        $data["gender"]              = trim($_POST["gender"] ?? "");
        $data["current_class"]       = trim($_POST["current_class"] ?? "");
        $data["parent_name"]         = strtoupper(trim($_POST["parent_name"] ?? ""));
        $data["parent_phone"]        = trim($_POST["parent_phone"] ?? "");
        $data["parent_email"]        = strtolower(trim($_POST["parent_email"] ?? ""));
        $data["address"]             = strtoupper(trim($_POST["address"] ?? ""));
        $data["school"]              = strtoupper(trim($_POST["school"] ?? ""));
        $data["madrasa"]             = strtoupper(trim($_POST["madrasa"] ?? ""));
        $data["school_leaving_time"] = trim($_POST["school_leaving_time"] ?? "");
        $data["notes"]               = trim($_POST["notes"] ?? "");
        $data["status"]              = trim($_POST["status"] ?? $existing["status"]);
        if (!in_array($data["status"], ["Active", "Approved", "Pending", "Rejected"], true)) {
            $data["status"] = $existing["status"];
        }

        $admissionUnknown = !empty($_POST["admission_year_unknown"]);
        $admissionRaw = trim($_POST["admission_year"] ?? "");
        $admissionYear = null;
        if (!$admissionUnknown && $admissionRaw !== "") {
            if (!ctype_digit($admissionRaw) || (int)$admissionRaw < 1900 || (int)$admissionRaw > (int)date("Y") + 1) {
                $formErrors[] = "Admission year looks invalid.";
            } else {
                $admissionYear = (int)$admissionRaw;
            }
        }
        $data["admission_year"] = $admissionYear;

        if ($data["full_name"] === "")   { $formErrors[] = "Student full name is required."; }
        if ($data["parent_name"] === "") { $formErrors[] = "Parent/guardian name is required."; }
        if ($data["parent_phone"] === ""){ $formErrors[] = "Parent phone number is required."; }
        if ($data["address"] === "")     { $formErrors[] = "Address is required."; }
        if ($data["parent_email"] !== "" && !filter_var($data["parent_email"], FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = "Parent email address is invalid.";
        }

        if (!$formErrors) {
            try {
                $pdo->beginTransaction();

                // The official registration number is never changed here; it is only
                // assigned if the student becomes approved/active without one yet.
                $number = $existing["registration_number"];
                $registeredAt = $existing["registered_at"];
                if (in_array($data["status"], ["Approved", "Active"], true)) {
                    if ($number === null || $number === "") {
                        $number = generate_registration_number($pdo, (int)date("Y"));
                    }
                    if ($registeredAt === null || $registeredAt === "") {
                        $registeredAt = $existing["created_at"] !== "" ? $existing["created_at"] : date("Y-m-d H:i:s");
                    }
                }

                $upd = $pdo->prepare("UPDATE students SET
                    full_name = ?, date_of_birth = ?, current_class = ?, admission_year = ?, gender = ?,
                    parent_name = ?, parent_phone = ?, parent_email = ?, address = ?, school = ?, madrasa = ?,
                    school_leaving_time = ?, status = ?, notes = ?, registration_number = ?, registered_at = ?, updated_at = NOW()
                    WHERE id = ?");
                $upd->execute([
                    $data["full_name"], $data["date_of_birth"] !== "" ? $data["date_of_birth"] : null,
                    $data["current_class"] !== "" ? $data["current_class"] : null, $admissionYear,
                    $data["gender"] !== "" ? $data["gender"] : null, $data["parent_name"], $data["parent_phone"],
                    $data["parent_email"] !== "" ? $data["parent_email"] : null, $data["address"],
                    $data["school"] !== "" ? $data["school"] : null, $data["madrasa"] !== "" ? $data["madrasa"] : null,
                    $data["school_leaving_time"] !== "" ? $data["school_leaving_time"] : null,
                    $data["status"], $data["notes"] !== "" ? $data["notes"] : null, $number, $registeredAt, $id,
                ]);
                $pdo->commit();

                set_flash("success", "Student details updated successfully.");
                header("Location: student.php?id=" . $id);
                exit;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $formErrors[] = "Could not update the student. Please try again.";
            }
        }
    }
}

$current = "registrations";
$adminTitle = "Edit Student";
$pageSubtitle = $existing["registration_number"] !== null && $existing["registration_number"] !== ""
    ? $existing["registration_number"]
    : $existing["registration_id"];
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";

$formAction = "edit_student.php?id=" . $id;
$studentData = $data;
$studentData["id"] = $id;
$submitLabel = "Save Changes";
$showStatus = true;
?>
<div class="fade-up max-w-4xl">
  <a href="student.php?id=<?php echo (int)$id; ?>" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-5">
    <i class="fas fa-arrow-left text-xs"></i> Back to student
  </a>
  <?php flash_message(); ?>
  <?php require __DIR__ . "/includes/student_form.php"; ?>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
