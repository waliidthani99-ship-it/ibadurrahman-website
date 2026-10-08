<?php
require_once __DIR__ . "/../includes/init.php";
$errors = array();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf_token"]) || !isset($_SESSION["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $errors[] = "Invalid token";
    }
    $fullName = trim(isset($_POST["full_name"]) ? $_POST["full_name"] : "");
    $user = trim(isset($_POST["username"]) ? $_POST["username"] : "");
    $pass = isset($_POST["password"]) ? $_POST["password"] : "";
    $confirm = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";
    if ($fullName === "" || $user === "" || $pass === "" || $confirm === "") {
        $errors[] = "All fields required";
    }
    if ($pass !== "" && strlen($pass) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($pass !== $confirm) {
        $errors[] = "Passwords do not match";
    }
    if (empty($errors)) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute(array($user));
        if ($stmt->fetch()) {
            $errors[] = "Username already exists.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO admins (full_name, username, password_hash) VALUES (?, ?, ?)");
            $ins->execute(array($fullName, $user, $hash));
            $_SESSION["flash_success"] = "Account created successfully. You can now log in.";
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Register - Madrasatul Ibadu-rrahman</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: Poppins, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #FFFDF5; }
.btn-primary { background-color: #3A7A40; transition: all 0.3s ease; }
.btn-primary:hover { background-color: #2d5f30; }
</style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8">
<div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 md:p-8">
  <div class="flex flex-col items-center mb-6">
    <img src="../assets/logo.png" alt="Logo" class="h-20 mb-3">
    <h1 class="text-xl md:text-2xl font-bold text-gray-800 text-center">Admin Registration</h1>
    <p class="text-sm text-gray-500 mt-1">Create a new administrator account</p>
  </div>
  <?php if (!empty($errors)): foreach ($errors as $e): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-3"><?php echo sanitize($e); ?></div>
  <?php endforeach; endif; ?>
  <form method="POST" novalidate>
    <?php csrf_field(); ?>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
      <input name="full_name" value="<?php echo sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Username</label>
      <input name="username" value="<?php echo sanitize(isset($_POST['username']) ? $_POST['username'] : ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
      <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
      <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out">REGISTER</button>
  </form>
  <div class="mt-6 text-center">
    <p class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-green-700 font-semibold hover:text-green-800 transition-colors">Login</a></p>
  </div>
</div>
</body>
</html>