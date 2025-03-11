<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ASM SEO Reports' : 'ASM SEO Reports'; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
     <header class="bg-slate-900 relative z-10">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <a href="<?php echo SITE_URL; ?>" class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
              <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
            </svg>
            <span class="text-white font-bold text-xl tracking-tight">ASM Rank Tracker</span>
          </a>
        </div>
         <ul class="flex space-x-6">
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="/" class="text-gray-800 hover:text-gray-600">Clients</a></li>
                            <li><a href="/import" class="text-gray-800 hover:text-gray-600">Import</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/logout" class="text-gray-800 hover:text-gray-600">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/login" class="text-gray-800 hover:text-gray-600">Login</a></li>
                        <?php endif; ?>
        </ul>
        <!-- Mobile menu button -->
        <div class="flex md:hidden">
          <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-slate-800 focus:outline-none" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    <!-- Mobile menu, show/hide based on menu state -->
    <div class="hidden md:hidden" id="mobile-menu">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-slate-900">
        <a href="<?php echo SITE_URL; ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-slate-800">
          Clients
        </a>
        <a href="<?php echo SITE_URL; ?>/import" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-slate-800">
          Import Data
        </a>
      </div>
    </div>
  </header>
    
    <main class="container mx-auto px-4 py-6">
        <?php if (isset($page_title)): ?>
            <h1 class="text-2xl font-bold mb-6"><?php echo $page_title; ?></h1>
        <?php endif; ?>