<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../includes/init.php";
$errors = [];
function v($k){ return trim($_POST[$k] ?? ""); }

$studentName    = strtoupper(v("studentName"));
$dob            = v("dob");
$schoolName     = strtoupper(v("schoolName"));
$schoolClass    = strtoupper(v("schoolClass"));
$healthIssues   = v("healthIssues");
$parentName     = strtoupper(v("parentName"));
$phone          = v("phone");
$email          = strtolower(v("email"));
$address        = strtoupper(v("address"));
$additionalInfo = v("additionalInfo");
$declaration    = strtolower(v("declaration"));

if (empty($studentName)) $errors[] = "studentName";
if (empty($dob) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) $errors[] = "dob";
if (empty($schoolName)) $errors[] = "schoolName";
if (empty($schoolClass)) $errors[] = "schoolClass";
if (empty($parentName)) $errors[] = "parentName";
if (empty($phone)) $errors[] = "phone";
if (empty($address)) $errors[] = "address";
if ($declaration !== "1" && $declaration !== "on" && $declaration !== "true") $errors[] = "declaration";
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "email";

if (!empty($errors)) {
  echo json_encode(["success" => false, "errors" => $errors]);
  exit;
}

$regId = "REG-" . date("YmdHis") . "-" . rand(1000, 9999);

// Keep both optional text fields without needing a new database column.
$notesParts = [];
if ($healthIssues !== "") $notesParts[] = "Health issues/allergies: " . $healthIssues;
if ($additionalInfo !== "") $notesParts[] = "Additional information: " . $additionalInfo;
$notes = !empty($notesParts) ? implode("\n\n", $notesParts) : null;

$pdo = getDbConnection();
$stmt = $pdo->prepare("INSERT INTO students
    (registration_id, student_type, full_name, date_of_birth, current_class,
     parent_name, parent_phone, parent_email, address, school, status, notes,
     registered_at, created_at, updated_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),NOW())");
$stmt->execute([
    $regId,
    "new",
    $studentName,
    $dob,
    $schoolClass,
    $parentName,
    $phone,
    $email !== "" ? $email : null,
    $address,
    $schoolName,
    "Pending",
    $notes
]);

echo json_encode(["success" => true, "registration_id" => $regId]);
