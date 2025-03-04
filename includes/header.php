<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ASM Reports' : 'ASM SEO Reports'; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-black text-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="index.php" class="text-xl font-bold">ASM Reports</a>
                <nav>
                    <ul class="flex space-x-4">
                        <li><a href="index.php" class="hover:text-blue-200">Clients</a></li>
                        <li><a href="import.php" class="hover:text-blue-200">Import Data</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="container mx-auto px-4 py-6">
    