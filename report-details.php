<?php
// Include functions
require_once 'includes/functions.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to index if no ID provided
    header('Location: index.php');
    exit;
}

$report_id = (int)$_GET['id'];

// Get report details
$report = getReportDetails($report_id);

if (!$report) {
    // Redirect to index if report not found
    header('Location: index.php');
    exit;
}

// Get data for different search engines and filter out empty URLs
$google_data = array_filter(getReportData($report_id, 'google'), function($row) {
    return !empty($row['url']);
});
$bing_data = array_filter(getReportData($report_id, 'bing'), function($row) {
    return !empty($row['url']);
});
$yahoo_data = array_filter(getReportData($report_id, 'yahoo'), function($row) {
    return !empty($row['url']);
});
$google_mobile_data = array_filter(getReportData($report_id, 'google_mobile'), function($row) {
    return !empty($row['url']);
});

// Calculate improvement stats
$google_improved = array_filter($google_data, function($row) { return $row['difference'] > 0; });
$google_mobile_improved = array_filter($google_mobile_data, function($row) { return $row['difference'] > 0; });
$yahoo_improved = array_filter($yahoo_data, function($row) { return $row['difference'] > 0; });
$bing_improved = array_filter($bing_data, function($row) { return $row['difference'] > 0; });

// Top 10 rankings count
$google_top10 = array_filter($google_data, function($row) { return $row['rank'] <= 10; });
$google_mobile_top10 = array_filter($google_mobile_data, function($row) { return $row['rank'] <= 10; });
$yahoo_top10 = array_filter($yahoo_data, function($row) { return $row['rank'] <= 10; });
$bing_top10 = array_filter($bing_data, function($row) { return $row['rank'] <= 10; });

// Page title
$page_title = "Report Details: " . formatReportPeriod($report['report_period']);

// Include header
include 'includes/header.php';
?>

<?php
// Calculate stats at the top of the file
$all_keywords = [];
foreach($google_data as $row) { $all_keywords[$row['keyword']] = true; }
foreach($google_mobile_data as $row) { $all_keywords[$row['keyword']] = true; }
foreach($yahoo_data as $row) { $all_keywords[$row['keyword']] = true; }
foreach($bing_data as $row) { $all_keywords[$row['keyword']] = true; }
$total_keywords = count($all_keywords);

// Get engines analyzed
$engines = [];
if(!empty($google_data)) $engines[] = 'Google';
if(!empty($google_mobile_data)) $engines[] = 'Google Mobile';
if(!empty($yahoo_data)) $engines[] = 'Yahoo';
if(!empty($bing_data)) $engines[] = 'Bing';
$engines_analyzed = implode(', ', $engines);

// For now, hard-coded values (you can replace with actual data later)
$ranking_depth = 25;
$geographic_target = 'Local';
$baseline_date = '3/31/2020';
$baseline_keyword_count = 75;
$services = 'SEO';



// Combine all ranking data from all engines for analysis
$all_rankings = array_merge($google_data, $google_mobile_data, $yahoo_data, $bing_data);

// Initialize counters
$first_position = 0;
$top_5_positions = 0;
$first_page = 0;
$first_two_pages = 0;
$moved_up = 0;
$moved_down = 0;
$no_change = 0;
$new_listings = 0;
$total_positions_gained = 0;
$total_positions_lost = 0;
$visibility_score = 0;
$total_possible_visibility = 0;

// Process each ranking
foreach ($all_rankings as $row) {
    // Count by position
    if ($row['rank'] == 1) {
        $first_position++;
    }
    if ($row['rank'] <= 5) {
        $top_5_positions++;
    }
    if ($row['rank'] <= 10) {
        $first_page++;
    }
    if ($row['rank'] <= 20) {
        $first_two_pages++;
    }
    
    // Count by movement
    if ($row['previous_rank'] == 0) {
        $new_listings++; // Was not ranking before
    } elseif ($row['difference'] > 0) {
        $moved_up++;
        $total_positions_gained += $row['difference'];
    } elseif ($row['difference'] < 0) {
        $moved_down++;
        $total_positions_lost += abs($row['difference']);
    } else {
        $no_change++;
    }
    
    // Calculate visibility score
    // A common formula: higher rankings contribute more to visibility
    if ($row['rank'] <= 100) { // Only count rankings in top 100
        // For example: position 1 = 100 points, position 10 = 91 points, etc.
        $visibility_score += (101 - $row['rank']);
        $total_possible_visibility += 100; // Maximum possible for each keyword
    }
}

// Calculate total positions gained/lost
$net_position_change = $total_positions_gained - $total_positions_lost;

// Calculate visibility percentage
$visibility_percentage = ($total_possible_visibility > 0) ? 
    ($visibility_score / $total_possible_visibility) * 100 : 0;

?>

<div class="flex items-center mb-6">
    <a href="client.php?domain=<?php echo urlencode($report['client_domain']); ?>" class="bg-slate-100 hover:bg-slate-200 rounded-md p-2 mr-3 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-slate-900">
        <?php echo formatReportPeriod($report['report_period']); ?> Report
    </h1>
</div>

<!-- Top action bar -->
<div class="bg-white rounded-md border border-slate-200 shadow-sm p-4 mb-4">
    <div class="flex justify-between items-center">
        <div>
            <span class="text-lg font-medium text-slate-900"><?php echo htmlspecialchars($report['client_domain']); ?></span>
            <span class="ml-2 text-sm text-slate-500"><?php echo formatReportPeriod($report['report_period']); ?></span>
        </div>
        <a href="search-positions.php?id=<?php echo $report_id; ?>" class="flex items-center text-sky-600 hover:text-sky-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z" />
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z" clip-rule="evenodd" />
            </svg>
            View Search Positions
        </a>
    </div>
</div>

<!-- Bento grid layout -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
    <!-- Report Details - now first on left side -->
    <div class="md:col-span-5">
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm h-full">
            <h3 class="text-medium font-medium text-slate-900 mb-3">Report Details</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Domain</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($report['client_domain']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Keywords Analyzed</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $total_keywords; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Ranking Depth</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $ranking_depth; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Engines Analyzed</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $engines_analyzed; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Geographic Target</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $geographic_target; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Baseline Date</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $baseline_date; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Services</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo $services; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Visibility Score</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo number_format($visibility_score); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-600">Visibility Percentage</span>
                    <span class="text-sm font-medium text-slate-900"><?php echo number_format($visibility_percentage, 2); ?>%</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Metrics - slightly smaller next to report details -->
    <div class="md:col-span-7">
    <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm h-full">
        <h3 class="text-sm font-medium text-slate-900 mb-4">Performance Metrics</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <!-- First Position -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">First Position</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $first_position; ?></p>
        </div>
        
        <!-- Top 5 -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">Top 5</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $top_5_positions; ?></p>
        </div>
        
        <!-- First Page -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">First Page</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $first_page; ?></p>
        </div>
        
        <!-- First Two Pages -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">First Two Pages</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $first_two_pages; ?></p>
        </div>
        
        <!-- New Listings -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">New Listings</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $new_listings; ?></p>
        </div>
        
        <!-- Moved Up -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">Moved Up</p>
            <p class="text-2xl font-semibold text-emerald-600"><?php echo $moved_up; ?></p>
        </div>
        
        <!-- Moved Down -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">Moved Down</p>
            <p class="text-2xl font-semibold text-rose-600"><?php echo $moved_down; ?></p>
        </div>
        
        <!-- No Change -->
        <div class="bg-slate-50 rounded-md p-4 flex flex-col justify-between h-full">
            <p class="text-sm text-slate-600 mb-2">No Change</p>
            <p class="text-2xl font-semibold text-slate-900"><?php echo $no_change; ?></p>
        </div>
        </div>
    </div>
    </div>
    <!-- Bottom row with equally sized boxes -->
    <div class="md:col-span-4">
        <!-- Search Engine Data Summary -->
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm h-full">
            <h3 class="text-sm font-medium text-slate-900 mb-3">Search Engines</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-md p-3 text-center">
                    <div class="text-xl font-bold text-sky-600"><?php echo count($google_data); ?></div>
                    <div class="text-xs text-slate-600">Google</div>
                </div>
                <div class="bg-slate-50 rounded-md p-3 text-center">
                    <div class="text-xl font-bold text-emerald-600"><?php echo count($google_mobile_data); ?></div>
                    <div class="text-xs text-slate-600">Mobile</div>
                </div>
                <div class="bg-slate-50 rounded-md p-3 text-center">
                    <div class="text-xl font-bold text-violet-600"><?php echo count($yahoo_data); ?></div>
                    <div class="text-xs text-slate-600">Yahoo</div>
                </div>
                <div class="bg-slate-50 rounded-md p-3 text-center">
                    <div class="text-xl font-bold text-amber-600"><?php echo count($bing_data); ?></div>
                    <div class="text-xs text-slate-600">Bing</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visibility Percentage Chart - now equal size -->
    <div class="md:col-span-4">
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm h-full">
            <h3 class="text-sm font-medium text-slate-900 mb-3">Visibility Percentage</h3>
            <div class="h-48">
                <canvas id="visibilityChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Ranking Improvements Chart - now equal size -->
    <div class="md:col-span-4">
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm h-full">
            <h3 class="text-sm font-medium text-slate-900 mb-3">Ranking Changes</h3>
            <div class="h-48">
                <canvas id="improvementsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Full-width ranking distribution chart -->
    <div class="md:col-span-12">
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-sm">
            <h3 class="text-sm font-medium text-slate-900 mb-3">Ranking Position Distribution</h3>
            <div class="h-64">
                <canvas id="rankingDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Google ranking distribution
    const googleTop3 = <?php echo count(array_filter($google_data, function($row) { return $row['rank'] <= 3; })); ?>;
    const googleTop10 = <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>;
    const googleTop20 = <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>;
    const googleTop50 = <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>;
    const googleBeyond = <?php echo count(array_filter($google_data, function($row) { return $row['rank'] > 50; })); ?>;

    // Mobile distribution
    const mobileTop3 = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] <= 3; })); ?>;
    const mobileTop10 = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>;
    const mobileTop20 = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>;
    const mobileTop50 = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>;
    const mobileBeyond = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['rank'] > 50; })); ?>;

    // Yahoo distribution
    const yahooTop3 = <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] <= 3; })); ?>;
    const yahooTop10 = <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>;
    const yahooTop20 = <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>;
    const yahooTop50 = <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>;
    const yahooBeyond = <?php echo count(array_filter($yahoo_data, function($row) { return $row['rank'] > 50; })); ?>;

    // Bing distribution
    const bingTop3 = <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] <= 3; })); ?>;
    const bingTop10 = <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 3 && $row['rank'] <= 10; })); ?>;
    const bingTop20 = <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 10 && $row['rank'] <= 20; })); ?>;
    const bingTop50 = <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 20 && $row['rank'] <= 50; })); ?>;
    const bingBeyond = <?php echo count(array_filter($bing_data, function($row) { return $row['rank'] > 50; })); ?>;

    // Ranking Distribution Chart
    const rankDistCtx = document.getElementById('rankingDistributionChart').getContext('2d');
    new Chart(rankDistCtx, {
        type: 'bar',
        data: {
            labels: ['Positions 1-3', 'Positions 4-10', 'Positions 11-20', 'Positions 21-50', 'Positions 51+'],
            datasets: [
                {
                    label: 'Google',
                    data: [googleTop3, googleTop10, googleTop20, googleTop50, googleBeyond],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Google Mobile',
                    data: [mobileTop3, mobileTop10, mobileTop20, mobileTop50, mobileBeyond],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Yahoo',
                    data: [yahooTop3, yahooTop10, yahooTop20, yahooTop50, yahooBeyond],
                    backgroundColor: 'rgba(153, 102, 255, 0.7)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Bing',
                    data: [bingTop3, bingTop10, bingTop20, bingTop50, bingBeyond],
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate average visibility for each search engine
    const googleVisibility = <?php 
        echo !empty($google_data) ? array_sum(array_column($google_data, 'visibility'))/count($google_data) : 0; 
    ?>;
    const mobileVisibility = <?php 
        echo !empty($google_mobile_data) ? array_sum(array_column($google_mobile_data, 'visibility'))/count($google_mobile_data) : 0; 
    ?>;
    const yahooVisibility = <?php 
        echo !empty($yahoo_data) ? array_sum(array_column($yahoo_data, 'visibility'))/count($yahoo_data) : 0; 
    ?>;
    const bingVisibility = <?php 
        echo !empty($bing_data) ? array_sum(array_column($bing_data, 'visibility'))/count($bing_data) : 0; 
    ?>;
    
    // Visibility Percentage Chart
    const visCtx = document.getElementById('visibilityChart').getContext('2d');
    new Chart(visCtx, {
        type: 'bar',
        data: {
            labels: ['Google', 'Google Mobile', 'Yahoo', 'Bing'],
            datasets: [{
                label: 'Average Visibility %',
                data: [
                    googleVisibility.toFixed(1), 
                    mobileVisibility.toFixed(1), 
                    yahooVisibility.toFixed(1), 
                    bingVisibility.toFixed(1)
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
                            return context.raw + '%';
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
    
    // Ranking Improvements Chart Data
    const googleImproved = <?php echo count($google_improved); ?>;
    const googleDeclined = <?php echo count(array_filter($google_data, function($row) { return $row['difference'] < 0; })); ?>;
    const googleUnchanged = <?php echo count(array_filter($google_data, function($row) { return $row['difference'] == 0; })); ?>;
    
    const mobileImproved = <?php echo count($google_mobile_improved); ?>;
    const mobileDeclined = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['difference'] < 0; })); ?>;
    const mobileUnchanged = <?php echo count(array_filter($google_mobile_data, function($row) { return $row['difference'] == 0; })); ?>;
    
    const yahooImproved = <?php echo count($yahoo_improved); ?>;
    const yahooDeclined = <?php echo count(array_filter($yahoo_data, function($row) { return $row['difference'] < 0; })); ?>;
    const yahooUnchanged = <?php echo count(array_filter($yahoo_data, function($row) { return $row['difference'] == 0; })); ?>;
    
    const bingImproved = <?php echo count($bing_improved); ?>;
    const bingDeclined = <?php echo count(array_filter($bing_data, function($row) { return $row['difference'] < 0; })); ?>;
    const bingUnchanged = <?php echo count(array_filter($bing_data, function($row) { return $row['difference'] == 0; })); ?>;
    
    // Ranking Improvements Chart
    const impCtx = document.getElementById('improvementsChart').getContext('2d');
    new Chart(impCtx, {
        type: 'bar',
        data: {
            labels: ['Google', 'Google Mobile', 'Yahoo', 'Bing'],
            datasets: [
                {
                    label: 'Improved',
                    data: [googleImproved, mobileImproved, yahooImproved, bingImproved],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Declined',
                    data: [googleDeclined, mobileDeclined, yahooDeclined, bingDeclined],
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Unchanged',
                    data: [googleUnchanged, mobileUnchanged, yahooUnchanged, bingUnchanged],
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
});
</script>
  
   
    </div>

<?php
// Include footer
include 'includes/footer.php';
?>