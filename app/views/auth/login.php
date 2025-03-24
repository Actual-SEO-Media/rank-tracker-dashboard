<?php
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
          
      <!-- Login form -->
        <form class="w-full" method="POST" action="<?php echo BASE_URL . '/login'; ?>">
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
      <!-- Forgot password link -->
      <div class="text-center text-sm mb-6">
        <a href="#" class="text-blue-600 hover:underline">Forgot password?</a>
      </div>
      
      <!-- Divider with text -->
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="bg-white px-2 text-gray-500">Or continue with</span>
        </div>
      </div>
      
      <!-- Social login buttons -->
      <div class="flex flex-col space-y-3">
        <button class="ring-offset-background focus-visible:ring-ring flex h-10 w-full items-center justify-center whitespace-nowrap rounded-md border border-gray-300 px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
            <line x1="9" y1="9" x2="9.01" y2="9"></line>
            <line x1="15" y1="9" x2="15.01" y2="9"></line>
          </svg>
          Continue with Google
        </button>
      </div>
    </div>
  </div>

<?php require_once BASE_PATH . '/app/views/layout/footer.php'; ?>