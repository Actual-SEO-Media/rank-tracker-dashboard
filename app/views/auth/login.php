<?php
$config = \App\Configs\Config::getInstance();
$page_title = 'Login';
require_once BASE_PATH . '/app/views/layout/header.php';
?>

<div class="flex h-screen flex-col items-center justify-center">
  <!-- Container for login form -->
  <div class="max-h-auto mx-auto max-w-xl">
    <!-- Login title and description -->
    <div class="mb-8 space-y-3">
      <p class="text-xl font-semibold">Login</p>
      <p class="text-gray-500">Enter your username and password to access your account.</p>
    </div>
    
    <!-- Display flash messages if any -->
    <?php if ($session->hasFlash('login_error')): ?>
      <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4">
        <p><?php echo $session->getFlash('login_error'); ?></p>
      </div>
    <?php endif; ?>
        
    <!-- Login form -->
    <form class="w-full" method="POST" action="<?php echo $config->get('base_url'); ?>/login">
      <!-- CSRF Protection -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
      
      <div class="mb-10 space-y-3">
        <div class="space-y-3">
          <!-- Username label and input field -->
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="username">Username</label>
            <input class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="username" placeholder="johndoe" name="username" required />
          </div>
          <!-- Password label and input field -->
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="password">Password</label>
            <input class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="password" type="password" placeholder="••••••••" name="password" required />
          </div>
        </div>
        <button class="ring-offset-background focus-visible:ring-ring flex h-10 w-full items-center justify-center whitespace-nowrap rounded-md bg-black px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-black/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50" type="submit">Login</button>
      </div>
    </form>
    
    <?php if ($config->get('app_env') !== 'production'): ?>
    <!-- Dev environment login hint -->
    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
      <p class="text-xs text-yellow-700">
        <strong>Development Environment</strong><br>
        Default login: username: <strong>admin</strong>, password: <strong>admin123</strong>
      </p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/layout/footer.php'; ?>