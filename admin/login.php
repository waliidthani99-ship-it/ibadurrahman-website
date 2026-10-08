<?php
require_once __DIR__ . "/../includes/init.php";
$errors = array();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf_token"]) || !isset($_SESSION["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $errors[] = "Invalid token";
    }
    $user = trim(isset($_POST["username"]) ? $_POST["username"] : (isset($_POST["user"]) ? $_POST["user"] : ""));
    $pass = isset($_POST["password"]) ? $_POST["password"] : (isset($_POST["pass"]) ? $_POST["pass"] : "");
    if ($user === "" || $pass === "") {
        $errors[] = "All fields required";
    }
    if (empty($errors)) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute(array($user));
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($pass, $admin["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = $admin["id"];
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Madrasatul Ibadu-rrahman</title>
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
    <h1 class="text-xl md:text-2xl font-bold text-gray-800 text-center">Admin Login</h1>
    <p class="text-sm text-gray-500 mt-1">Sign in to your administrator account</p>
  </div>
  <?php if (!empty($errors)): foreach ($errors as $e): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-3"><?php echo sanitize($e); ?></div>
  <?php endforeach; endif; ?>
  <?php if (isset($_SESSION['flash_success']) && $_SESSION['flash_success']): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-3"><?php echo sanitize($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
  <?php endif; ?>
  <form method="POST" novalidate>
    <?php csrf_field(); ?>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Username</label>
      <input name="username" value="<?php echo sanitize(isset($_POST['username']) ? $_POST['username'] : ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
      <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent" required>
    </div>
    <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out">LOGIN</button>
  </form>
  <div class="mt-6 text-center">
    <p class="text-sm text-gray-600">Don't have an account? <a href="register.php" class="text-green-700 font-semibold hover:text-green-800 transition-colors">Register</a></p>
  </div>
</div>
</body>
</html>

