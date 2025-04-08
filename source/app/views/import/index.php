<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="mb-4">
  <a href="<?php echo $_ENV['SITE_URL']; ?>" class="text-slate-900 hover:text-slate-700 flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
    </svg>
    Back to Dashboard
  </a>
</div>

<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
  <h2 class="text-xl font-semibold text-slate-800 mb-6">Import Rank Tracker Data</h2>
  
  <?php if ($success): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6">
      <p><?php echo $success_message; ?></p>
    </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 mb-6">
      <p><?php echo $error; ?></p>
    </div>
  <?php endif; ?>
  
  <form action="<?php echo $_ENV['SITE_URL']; ?>/import" method="post" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label for="client_domain" class="block text-sm font-medium text-slate-700 mb-1">
        Client Domain*
      </label>
      <input type="text" id="client_domain" name="client_domain"
             value="<?php echo htmlspecialchars($domain); ?>"
             class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm
                    placeholder-slate-400
                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
             required>
      <p class="text-sm text-slate-500 mt-1">Example: actualseomedia.com</p>
    </div>
    
    <div>
      <label for="report_period" class="block text-sm font-medium text-slate-700 mb-1">
        Report Period* (YYYY-MM)
      </label>
      <input type="month" id="report_period" name="report_period"
             value="<?php echo date('Y-m'); ?>"
             class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm
                    placeholder-slate-400
                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
             required>
      <p class="text-sm text-slate-500 mt-1">The month this report covers</p>
    </div>
    <div>
  <label for="csv_file" class="block text-sm font-medium text-slate-700 mb-1">
    CSV File*
  </label>
  <div class="flex items-center justify-center w-full">
    <label for="csv_file" class="flex flex-col items-center justify-center w-full px-3 py-6 border-2 border-slate-300 border-dashed rounded-md cursor-pointer bg-slate-50 hover:bg-slate-100 transition" id="upload-box">
      <!-- Default state (shown when no file selected) -->
      <div id="default-content" class="flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="mt-2 text-sm text-slate-500">
          <span class="font-medium">Click to upload</span> or drag and drop
        </p>
        <p class="text-xs text-slate-500">
          CSV files only
        </p>
      </div>
      
      <!-- Success state (hidden by default, shown when file selected) -->
      <div id="success-content" class="hidden flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="mt-2 text-sm text-green-600 font-medium">
          File selected successfully!
        </p>
        <p id="selected-filename" class="text-xs text-green-500 text-center max-w-xs overflow-hidden text-ellipsis"></p>
        <p class="mt-2 text-xs text-slate-500">
          Click to change file
        </p>
      </div>
      
      <input id="csv_file" name="csv_file" type="file" accept=".csv" class="hidden" required />
    </label>
  </div>
</div>
    
    <div class="pt-4">
      <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-sm font-medium shadow-sm flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        Import Data
      </button>
    </div>
  </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('csv_file');
  const uploadBox = document.getElementById('upload-box');
  const defaultContent = document.getElementById('default-content');
  const successContent = document.getElementById('success-content');
  const selectedFilename = document.getElementById('selected-filename');
  
  if (fileInput && uploadBox && defaultContent && successContent && selectedFilename) {
    fileInput.addEventListener('change', function() {
      if (fileInput.files.length > 0) {
        // File was selected - show success state
        uploadBox.classList.remove('border-slate-300', 'bg-slate-50');
        uploadBox.classList.add('border-green-300', 'bg-green-50');
        
        // Hide default content, show success content
        defaultContent.classList.add('hidden');
        successContent.classList.remove('hidden');
        
        // Display filename
        selectedFilename.textContent = fileInput.files[0].name;
      } else {
        // No file selected - show default state
        uploadBox.classList.remove('border-green-300', 'bg-green-50');
        uploadBox.classList.add('border-slate-300', 'bg-slate-50');
        
        // Show default content, hide success content
        defaultContent.classList.remove('hidden');
        successContent.classList.add('hidden');
      }
    });
  } else {
    console.error('Some elements were not found. Check your HTML structure.');
  }
});
</script>
<?php include __DIR__ . '/../layout/footer.php'; ?>