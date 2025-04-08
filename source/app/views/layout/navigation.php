 <div class="flex justify-between mb-6">
  <!-- Left side buttons -->
  <div class="flex gap-4">
    <a href="index.php?action=positions&id=<?php echo $report['report_id']; ?>" class="inline-flex items-center px-4 py-2 rounded-md bg-white border border-slate-200 shadow-sm text-slate-700 hover:bg-slate-50 transition-colors">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z" />
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z" clip-rule="evenodd" />
      </svg>
      View Search Engine Positions
    </a>
    <a href="/" class="inline-flex items-center px-3 py-1.5 text-medium rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors">
      Keywords
    </a>
    <a href="/" class="inline-flex items-center px-3 py-1.5 text-medium rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors">
      Baseline Report
    </a>
     <a href="/" class="inline-flex items-center px-3 py-1.5 text-medium rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors">
      Summary
    </a>
  </div>
  
  <!-- Right side buttons -->
  <div class="flex gap-4">
    <!-- Report Navigation -->
    <div class="flex rounded-lg border border-slate-200 overflow-hidden">
      <a href="<?php echo $navigation['prev_report_id'] ? 'index.php?action=view_report&id=' . $navigation['prev_report_id'] : '#'; ?>"
    class="flex items-center justify-center px-2 py-1 bg-white <?php echo !$navigation['prev_report_id'] ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50'; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </a>
      <button type="button" class="px-3 py-1 bg-white hover:bg-slate-50 text-sm text-slate-700 border-l border-r border-slate-200"
    onclick="document.getElementById('period-selector').classList.toggle('hidden')">
    <?php echo date('M Y', strtotime($period . '-01')); ?>
        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <a href="<?php echo $navigation['next_report_id'] ? 'index.php?action=view_report&id=' . $navigation['next_report_id'] : '#'; ?>"
    class="flex items-center justify-center px-2 py-1 bg-white <?php echo !$navigation['next_report_id'] ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50'; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
      </a>
    </div>
    <a href="index.php?action=import&domain=<?php echo urlencode($report['client_domain']); ?>" class="inline-flex items-center px-4 py-2 rounded-md bg-white border border-slate-200 shadow-sm text-slate-700 hover:bg-slate-50 transition-colors">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
      </svg>
      New Report
    </a>
  </div>
</div>
