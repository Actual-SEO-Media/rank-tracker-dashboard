<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="bg-slate-50 min-h-screen p-6">
  <!-- Header Section -->
<div class="flex flex-col items-center justify-center mb-8 text-center">
  <div>
    <div class="flex items-center justify-center">
      <h1 class="text-4xl font-bold text-slate-800">
        <?php $period = $report['report_period']; echo date('F Y', strtotime($period . '-01')); ?> Report
      </h1>
    </div>
    <div class="flex items-center justify-center mt-1 mb-3">
      <span class="text-sm text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
        <?php echo htmlspecialchars($report['client_domain']); ?>
      </span>
      <span class="mx-2 text-slate-400">•</span>
      <span class="text-sm text-slate-500">
        Imported <?php echo date('M j, Y', strtotime($report['import_date'])); ?>
      </span>
    </div>
    
    <?php
    // Determine visibility class based on percentage
    $visibility_class = $stats['visibility_percentage'] >= 75 ? 'bg-green-100 text-green-800' : 
                      ($stats['visibility_percentage'] >= 50 ? 'bg-blue-100 text-blue-800' : 
                      ($stats['visibility_percentage'] >= 25 ? 'bg-amber-100 text-amber-800' : 
                      'bg-red-100 text-red-800'));
    ?>
    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium <?php echo $visibility_class; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
      </svg>
      Visibility: <?php echo number_format($stats['visibility_percentage'], 2); ?>%
      
      <?php if (isset($stats['visibility_change'])) { ?>
        <span class="ml-1.5 <?php echo $stats['visibility_change'] > 0 ? 'text-green-700' : ($stats['visibility_change'] < 0 ? 'text-red-700' : ''); ?>">
          <?php echo $stats['visibility_change'] > 0 ? '↑' : ($stats['visibility_change'] < 0 ? '↓' : ''); ?>
          <?php echo $stats['visibility_change'] > 0 ? '+' : ''; ?><?php echo number_format(abs($stats['visibility_change']), 2); ?>%
        </span>
      <?php } ?>
    </span>
  </div>
</div>
<!-- Navigation -->
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


  <!-- Bento Grid Layout -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Highlight Cards -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-medium text-slate-800">Top Rankings</h3>
        <span class="text-xs text-slate-500">Key metrics</span>
      </div>
      <div class="space-y-4">
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">First Position</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $stats['first_position']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Top 5 Positions</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $stats['top_5_positions']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">First Page</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $stats['first_page']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">First Two Pages</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $stats['first_two_pages']; ?></span>
        </div>
      </div>
    </div>

    <!-- Movement Stats -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-medium text-slate-800">Position Changes</h3>
        <span class="text-xs text-slate-500">Movements</span>
      </div>
      <div class="space-y-4">
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">New Listings</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $stats['new_listings']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Moved Up</span>
          <span class="text-lg font-semibold text-green-600"><?php echo $stats['moved_up']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Moved Down</span>
          <span class="text-lg font-semibold text-red-600"><?php echo $stats['moved_down']; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">No Change</span>
          <span class="text-lg font-semibold text-slate-500"><?php echo $stats['no_change']; ?></span>
        </div>
      </div>
    </div>

    <!-- Score Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-medium text-slate-800">Performance</h3>
        <span class="text-xs text-slate-500">Metrics</span>
      </div>
      <div class="space-y-4">
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Visibility Score</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo number_format($stats['visibility_score']); ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Net Position Change</span>
          <span class="text-lg font-semibold <?php echo $stats['net_position_change'] > 0 ? 'text-green-600' : ($stats['net_position_change'] < 0 ? 'text-red-600' : 'text-slate-500'); ?>">
            <?php echo $stats['net_position_change'] > 0 ? '+' : ''; ?><?php echo $stats['net_position_change']; ?>
          </span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Baseline</span>
          <span class="text-lg font-semibold text-slate-900"><?php echo $baseline_report ? date('M Y', strtotime($baseline_report['report_period'] . '-01')) : 'None'; ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Engines</span>
          <span class="text-sm font-medium text-slate-900"><?php
          $engines = [];
          if(!empty($google_data)) $engines[] = 'Google';
          if(!empty($google_mobile_data)) $engines[] = 'G-Mobile';
          if(!empty($yahoo_data)) $engines[] = 'Yahoo';
          if(!empty($bing_data)) $engines[] = 'Bing';
          echo implode(', ', $engines);
          ?></span>
        </div>
      </div>
    </div>

    <!-- Visibility Chart (Span 3) -->
    <div class="col-span-1 md:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
      <h3 class="text-base font-medium text-slate-800 mb-4">Visibility Trend</h3>
      <div class="h-64">
        <canvas id="visibilityChart"></canvas>
      </div>
    </div>

    <!-- Search Engine Data Summary -->
    <div class="col-span-1 md:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-100 p-6 text-center transition-all hover:shadow-md">
        <div class="text-3xl font-bold text-blue-700 mb-1"><?php echo count($google_data); ?></div>
        <div class="text-sm text-blue-600">Google Rankings</div>
      </div>
      <div class="bg-green-50 rounded-xl shadow-sm border border-green-100 p-6 text-center transition-all hover:shadow-md">
        <div class="text-3xl font-bold text-green-700 mb-1"><?php echo count($google_mobile_data); ?></div>
        <div class="text-sm text-green-600">Google Mobile</div>
      </div>
      <div class="bg-purple-50 rounded-xl shadow-sm border border-purple-100 p-6 text-center transition-all hover:shadow-md">
        <div class="text-3xl font-bold text-purple-700 mb-1"><?php echo count($yahoo_data); ?></div>
        <div class="text-sm text-purple-600">Yahoo Rankings</div>
      </div>
      <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-100 p-6 text-center transition-all hover:shadow-md">
        <div class="text-3xl font-bold text-amber-700 mb-1"><?php echo count($bing_data); ?></div>
        <div class="text-sm text-amber-600">Bing Rankings</div>
      </div>
    </div>

    <!-- Charts (Span 3) -->
    <div class="col-span-1 md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Ranking Changes Chart -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
        <h3 class="text-base font-medium text-slate-800 mb-4">Ranking Changes</h3>
        <div class="h-64">
          <canvas id="improvementsChart"></canvas>
        </div>
      </div>
      
      <!-- Ranking Distribution Chart -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
        <h3 class="text-base font-medium text-slate-800 mb-4">Position Distribution</h3>
        <div class="h-64">
          <canvas id="rankingDistributionChart"></canvas>
        </div>
      </div>
    </div>
   
  </div>


</div>

<!-- JavaScript for charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Visibility Percentage Chart
    const visCtx = document.getElementById('visibilityChart').getContext('2d');
    new Chart(visCtx, {
        type: 'bar',
        data: {
            labels: ['Google', 'Google Mobile', 'Yahoo', 'Bing'],
            datasets: [{
                label: 'Average Visibility %',
                data: [
                    <?php echo !empty($google_data) ? array_sum(array_column($google_data, 'visibility'))/count($google_data) : 0; ?>,
                    <?php echo !empty($google_mobile_data) ? array_sum(array_column($google_mobile_data, 'visibility'))/count($google_mobile_data) : 0; ?>,
                    <?php echo !empty($yahoo_data) ? array_sum(array_column($yahoo_data, 'visibility'))/count($yahoo_data) : 0; ?>,
                    <?php echo !empty($bing_data) ? array_sum(array_column($bing_data, 'visibility'))/count($bing_data) : 0; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Visibility Percentage'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Ranking Improvements Chart
    const impCtx = document.getElementById('improvementsChart').getContext('2d');
    new Chart(impCtx, {
        type: 'bar',
        data: {
            labels: ['Google', 'Google Mobile', 'Yahoo', 'Bing'],
            datasets: [
                {
                    label: 'Improved',
                    data: [
                        <?php echo count(array_filter($google_data, function($row) { return $row['difference'] > 0; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['difference'] > 0; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['difference'] > 0; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['difference'] > 0; })); ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Declined',
                    data: [
                        <?php echo count(array_filter($google_data, function($row) { return $row['difference'] < 0; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['difference'] < 0; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['difference'] < 0; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['difference'] < 0; })); ?>
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Unchanged',
                    data: [
                        <?php echo count(array_filter($google_data, function($row) { return $row['difference'] == 0; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['difference'] == 0; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['difference'] == 0; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['difference'] == 0; })); ?>
                    ],
                    backgroundColor: 'rgba(201, 203, 207, 0.7)',
                    borderColor: 'rgba(201, 203, 207, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                x: {
                    stacked: false
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Keywords'
                    }
                }
            }
        }
    });
    
    // Ranking Distribution Chart
    const rankDistCtx = document.getElementById('rankingDistributionChart').getContext('2d');
    new Chart(rankDistCtx, {
        type: 'bar',
        data: {
            labels: ['Positions 1-3', 'Positions 4-10', 'Positions 11-20', 'Positions 21-50', 'Positions 51+'],
            datasets: [
                {
                    label: 'Google',
                    data: [
                        <?php echo count(array_filter($google_data, function($row) { return $row['rank'] <= 3; })); ?>,
                        <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>,
                        <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>,
                        <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>,
                        <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 50; })); ?>
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Google Mobile',
                    data: [
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] <= 3; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>,
                        <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 50; })); ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Yahoo',
                    data: [
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] <= 3; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>,
                        <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 50; })); ?>
                    ],
                    backgroundColor: 'rgba(153, 102, 255, 0.7)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Bing',
                    data: [
                        <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] <= 3; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>,
                        <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 50; })); ?>
                    ],
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Keywords'
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>