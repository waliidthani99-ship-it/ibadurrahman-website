<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../includes/init.php";
$pdo = getDbConnection();
$errors = [];
function v($k){ return trim($_POST[$k] ?? ""); }
$studentName = strtoupper(v("studentName"));
$dob = v("dob");
$classReq = v("requestedClass");
$parentName = strtoupper(v("parentName"));
$phone = v("phone");
$email = strtolower(v("email"));
$address = strtoupper(v("address"));
$gender = v("gender");
$school = strtoupper(v("school"));
$madrasa = strtoupper(v("madrasa"));
$schoolLeaving = v("schoolLeavingTime");
if (empty($studentName)) $errors[] = "studentName";
if (empty($dob)) $errors[] = "dob";
if (empty($classReq)) $errors[] = "requestedClass";
if (empty($parentName)) $errors[] = "parentName";
if (empty($phone)) $errors[] = "phone";
if (empty($address)) $errors[] = "address";
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "email";
if (!empty($errors)) {
  echo json_encode(["success"=>false,"errors"=>$errors]);
  exit;
}
$regId = "REG-" . date("YmdHis") . "-" . rand(1000,9999);
$dobVal = ($dob !== "") ? $dob : null;
$stmt = $pdo->prepare("INSERT INTO students (registration_id, student_type, full_name, date_of_birth, current_class, gender, parent_name, parent_phone, parent_email, address, school, madrasa, school_leaving_time, status, registered_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),NOW())");
$stmt->execute([$regId, "new", $studentName, $dobVal, $classReq, $gender, $parentName, $phone, $email, $address, $school, $madrasa, $schoolLeaving, "Pending"]);
echo json_encode(["success"=>true,"registration_id"=>$regId]);

