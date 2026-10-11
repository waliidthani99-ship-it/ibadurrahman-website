<?php
require_once __DIR__ . "/includes/auth.php";
require_admin_login();

$pdo = getDbConnection();
$formErrors = [];
$data = [
    "full_name" => "", "date_of_birth" => "", "gender" => "", "current_class" => "",
    "admission_year" => "", "parent_name" => "", "parent_phone" => "", "parent_email" => "",
    "address" => "", "school" => "", "madrasa" => "", "school_leaving_time" => "", "notes" => "",
];

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
        $data["admission_year"] = $admissionYear !== null ? (string)$admissionYear : "";

        if ($data["full_name"] === "")          { $formErrors[] = "Student full name is required."; }
        if ($data["parent_name"] === "")        { $formErrors[] = "Parent/guardian name is required."; }
        if ($data["parent_phone"] === "")       { $formErrors[] = "Parent phone number is required."; }
        if ($data["address"] === "")            { $formErrors[] = "Address is required."; }
        if ($data["parent_email"] !== "" && !filter_var($data["parent_email"], FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = "Parent email address is not valid.";
        }
        $dobVal = ($data["date_of_birth"] !== "") ? $data["date_of_birth"] : null;

        // Duplicate check: same name and (matching DOB, or both unknown).
        if (!$formErrors) {
            $stmt = $pdo->prepare("SELECT id, registration_number FROM students WHERE full_name = ? AND " . ($dobVal === null ? "date_of_birth IS NULL" : "date_of_birth = ?") . " LIMIT 1");
            $stmt->execute($dobVal === null ? [$data["full_name"]] : [$data["full_name"], $dobVal]);
            $dup = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dup) {
                $formErrors[] = "A student with this name and date of birth already exists (ID #" . (int)$dup["id"] . ").";
            }
        }

        if (!$formErrors) {
            try {
                $pdo->beginTransaction();
                $regId = "LEG-" . date("YmdHis") . "-" . rand(1000, 9999);
                $ins = $pdo->prepare("INSERT INTO students
                    (registration_id, student_type, full_name, date_of_birth, current_class, admission_year, gender,
                     parent_name, parent_phone, parent_email, address, school, madrasa, school_leaving_time,
                     status, notes, registered_at, created_at, updated_at)
                    VALUES (?, 'legacy', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?, NOW(), NOW(), NOW())");
                $ins->execute([
                    $regId, $data["full_name"], $data["date_of_birth"] !== "" ? $data["date_of_birth"] : null,
                    $data["current_class"] !== "" ? $data["current_class"] : null, $admissionYear, $data["gender"] !== "" ? $data["gender"] : null,
                    $data["parent_name"], $data["parent_phone"], $data["parent_email"] !== "" ? $data["parent_email"] : null,
                    $data["address"], $data["school"] !== "" ? $data["school"] : null, $data["madrasa"] !== "" ? $data["madrasa"] : null,
                    $data["school_leaving_time"] !== "" ? $data["school_leaving_time"] : null,
                    $data["notes"] !== "" ? $data["notes"] : null,
                ]);
                $newId = (int)$pdo->lastInsertId();
                $number = assign_registration_number_if_missing($pdo, $newId);
                $pdo->commit();

                set_flash("success", "Student registered successfully. Registration number: " . $number);
                header("Location: student.php?id=" . $newId);
                exit;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $formErrors[] = "Could not save the student. Please try again.";
            }
        }
    }
}

$current = "add_student";
$adminTitle = "Add Existing Student";
$pageSubtitle = "Register a student who joined the madrasa previously";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/sidebar.php";

$formAction = "add_student.php";
$studentData = $data;
$submitLabel = "Save Student";
$showStatus = false;
?>
<div class="fade-up max-w-4xl">
  <a href="registrations.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-5">
    <i class="fas fa-arrow-left text-xs"></i> Back to students
  </a>
  <?php flash_message(); ?>
  <?php require __DIR__ . "/includes/student_form.php"; ?>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
