<?php
// Get config instance
$config = \App\Configs\Config::getInstance();
$page_title = 'Login';

// Include header
require_once BASE_PATH . '/app/views/layout/header.php';
?>

<div class="flex h-screen flex-col items-center justify-center">
  <!-- Container for login form -->
  <div class="max-h-auto mx-auto max-w-xl p-8 bg-white shadow-md rounded-lg border border-gray-200">
    <!-- Login title and description -->
    <div class="mb-8 space-y-3">
      <h1 class="text-xl font-semibold">Login</h1>
      <p class="text-gray-500">Enter your username and password to access your account.</p>
    </div>
    
    <!-- Display flash messages if any -->
    <?php if ($this->session->hasFlash('login_error')): ?>
      <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4">
        <p><?php echo $this->session->getFlash('login_error'); ?></p>
      </div>
    <?php endif; ?>
        
    <!-- Login form -->
    <form class="w-full" method="POST" action="<?php echo rtrim($config->get('site_url'), '/'); ?>/login">
      <!-- CSRF Protection -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
      
      <div class="mb-10 space-y-3">
        <div class="space-y-3">
          <!-- Username label and input field -->
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none" for="username">Username</label>
            <input 
                class="flex h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                id="username" 
                name="username" 
                placeholder="Enter your username" 
                required 
            />
          </div>
          <!-- Password label and input field -->
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none" for="password">Password</label>
            <input 
                class="flex h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                id="password" 
                type="password" 
                name="password" 
                placeholder="••••••••" 
                required 
            />
          </div>
        </div>
        <button 
            class="flex h-10 w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" 
            type="submit"
        >
          Login
        </button>
      </div>
    </form>
    
    <?php if ($config->get('app_env') !== 'production'): ?>
    <!-- Dev environment login hint -->
    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
      <p class="text-xs text-yellow-700">
        <strong>Development Environment</strong><br>
        Default admin login: Username: <strong>admin</strong>, Password: <strong>admin123</strong>
      </p>
    </div>
    <?php endif; ?>
  </div>

  <!-- Version info -->
  <div class="mt-8 text-center text-gray-500 text-xs">
    <p>SEO Rank Tracker v1.0.0</p>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/layout/footer.php'; ?>