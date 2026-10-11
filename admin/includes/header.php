<?php
$adminTitle = isset($adminTitle) ? $adminTitle : "Dashboard";
$pageSubtitle = isset($pageSubtitle) ? $pageSubtitle : "";
$lang = isset($_SESSION["lang"]) ? $_SESSION["lang"] : (isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : "en");
$isRtl = ($lang === "ar");
require_once __DIR__ . "/helpers.php";
?>
<!DOCTYPE html>
<html lang="<?php echo sanitize($lang); ?>" dir="<?php echo $isRtl ? "rtl" : "ltr"; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo sanitize($adminTitle); ?> - Madrasatul Ibadu-rrahman</title>
<link rel="icon" href="../assets/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: "#047857",
        darkGreen: "#065F46",
        accent: "#D97706",
        gold: "#F59E0B",
        cream: "#FEF7ED"
      },
      fontFamily: {
        sans: ["Poppins", "system-ui", "sans-serif"],
        arabic: ["Amiri", "serif"]
      }
    }
  }
};
</script>
<style>
  body { font-family: Poppins, system-ui, sans-serif; }
  [dir="rtl"] body { font-family: "Amiri", "Noto Naskh Arabic", sans-serif; }
  ::-webkit-scrollbar { width: 8px; height: 8px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
  ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  [dir="rtl"] #adminSidebar { left: auto; right: 0; transform: translateX(100%); }
  [dir="rtl"] #adminSidebar.is-open { transform: translateX(0); }
  @media (min-width: 768px) {
    [dir="rtl"] #adminSidebar { transform: translateX(0); }
  }
  .fade-up { animation: fadeUp .4s ease both; }
  @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body class="bg-slate-100 text-gray-800 antialiased">
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden md:hidden"></div>
<div class="flex min-h-screen">
