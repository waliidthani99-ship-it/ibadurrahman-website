<?php
$adminTitle = $adminTitle ?? "Dashboard";
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION["lang"]) ? $_SESSION["lang"] : (isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : "en"); ?>" dir="<?php echo ((isset($_SESSION["lang"]) && $_SESSION["lang"]=="ar") || (isset($_COOKIE["lang"]) && $_COOKIE["lang"]=="ar")) ? "rtl" : "ltr"; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo sanitize($adminTitle); ?> - Madrasatul Ibadu-rrahman</title>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
[dir="rtl"] body{font-family:"Amiri","Noto Naskh Arabic",sans-serif;text-align:right}
[dir="ltr"] body{font-family:"Poppins",system-ui,sans-serif}
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="md:hidden flex items-center justify-between bg-darkGreen text-white px-4 py-3 sticky top-0 z-30">
  <span class="font-semibold">Madrasatul Ibadu-rrahman</span>
  <button id="menuToggle" aria-label="Menu" class="text-xl px-2">
    <i class="fas fa-bars"></i>
  </button>
</div>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"></div>
<div class="flex min-h-screen">

