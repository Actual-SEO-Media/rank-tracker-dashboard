<?php
$config = \App\Configs\Config::getInstance();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ASM SEO Reports' : 'ASM SEO Reports'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="<?php echo $config->get('base_url'); ?>/app/assets/css/styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-slate-900 relative z-10">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <a href="<?php echo $config->get('site_url'); ?>" class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
              <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
            </svg>
            <span class="text-white font-bold text-xl tracking-tight">ASM Rank Tracker</span>
          </a>
        </div>
         <ul class="flex space-x-6">
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="<?php echo $config->get('site_url'); ?>" class="text-white hover:text-gray-300">Clients</a></li>
                <li><a href="<?php echo $config->get('site_url'); ?>/import" class="text-white hover:text-gray-300">Import</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo $config->get('site_url'); ?>/logout" class="text-white hover:text-gray-300">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $config->get('site_url'); ?>/login" class="text-white hover:text-gray-300">Login</a></li>
            <?php endif; ?>
        </ul>