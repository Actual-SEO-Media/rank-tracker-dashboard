<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="flex items-center mb-6">
    <a href="index.php?action=reports&domain=<?php echo urlencode($report['client_domain']); ?>" class="bg-white rounded-md p-2 mr-2 hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        <?php 
        $period = $report['report_period'];
        echo date('F Y', strtotime($period . '-01'));
        ?> Report
    </h1>
</div>
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-700">Report Details</h2>
        <div class="flex space-x-4">
            <a href="index.php?action=positions&id=<?php echo $report['report_id']; ?>" class="text-blue-600 hover:text-blue-900 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z" />
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z" clip-rule="evenodd" />
                </svg>
                View Search Positions
            </a>
            <a href="index.php?action=import&domain=<?php echo urlencode($report['client_domain']); ?>" class="text-green-600 hover:text-green-900 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                Import New Report
            </a>
        </div>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
            <p class="text-sm text-gray-700">
                <span class="font-medium">Domain:</span> <?php echo htmlspecialchars($report['client_domain']); ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Report Period:</span> <?php 
                $period = $report['report_period'];
                echo date('F Y', strtotime($period . '-01'));
                ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Import Date:</span> <?php echo date('F j, Y', strtotime($report['import_date'])); ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">File Name:</span> <?php echo htmlspecialchars($report['file_name'] ?? 'N/A'); ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Engines Analyzed:</span> <?php 
                $engines = [];
                if(!empty($google_data)) $engines[] = 'Google';
                if(!empty($google_mobile_data)) $engines[] = 'Google Mobile';
                if(!empty($yahoo_data)) $engines[] = 'Yahoo';
                if(!empty($bing_data)) $engines[] = 'Bing';
                echo implode(', ', $engines); 
                ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings in the First Position:</span> <?php echo $stats['first_position']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings in the Top 5 Positions:</span> <?php echo $stats['top_5_positions']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings on the First Page:</span> <?php echo $stats['first_page']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings on the First Two Pages:</span> <?php echo $stats['first_two_pages']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings New:</span> <?php echo $stats['new_listings']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings Which Moved Up:</span> <?php echo $stats['moved_up']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings Which Moved Down:</span> <?php echo $stats['moved_down']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Listings Which Did Not Change:</span> <?php echo $stats['no_change']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Total Positions Gained/Lost:</span> <?php echo $stats['net_position_change'] > 0 ? '+' : ''; ?><?php echo $stats['net_position_change']; ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Visibility Score:</span> <?php echo number_format($stats['visibility_score']); ?>
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Visibility Percentage:</span> <?php echo number_format($stats['visibility_percentage'], 2); ?>%
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Baseline Report:</span> <?php echo $baseline_report ? date('F Y', strtotime($baseline_report['report_period'] . '-01')) : 'None set'; ?>
            </p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Visibility Percentage Chart -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Average Visibility Percentage</h3>
            <div class="h-64">
                <canvas id="visibilityChart"></canvas>
            </div>
        </div>
        
        <!-- Ranking Improvements Chart -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ranking Changes</h3>
            <div class="h-64">
                <canvas id="improvementsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Ranking Distribution Chart -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Ranking Position Distribution</h3>
        <div class="h-64">
            <canvas id="rankingDistributionChart"></canvas>
        </div>
    </div>
    
    <!-- Search Engine Data Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-700"><?php echo count($google_data); ?></div>
            <div class="text-sm text-blue-600">Google Rankings</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-700"><?php echo count($google_mobile_data); ?></div>
            <div class="text-sm text-green-600">Google Mobile Rankings</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-purple-700"><?php echo count($yahoo_data); ?></div>
            <div class="text-sm text-purple-600">Yahoo Rankings</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-700"><?php echo count($bing_data); ?></div>
            <div class="text-sm text-yellow-600">Bing Rankings</div>
        </div>
    </div>
    
    <!-- Top Performing Keywords -->
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top Performing Keywords (Google)</h3>
    <?php if (empty($top_google)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
            <p>No Google rankings available for this report.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($top_google as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 whitespace-nowrap"><?php echo htmlspecialchars($row['keyword']); ?></td>
                            <td class="px-4 py-2 whitespace-nowrap font-semibold"><?php echo $row['rank']; ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?php echo $row['previous_rank']; ?></td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <?php if ($row['difference'] > 0): ?>
                                    <span class="text-green-600">+<?php echo $row['difference']; ?></span>
                                <?php elseif ($row['difference'] < 0): ?>
                                    <span class="text-red-600"><?php echo $row['difference']; ?></span>
                                <?php else: ?>
                                    <span class="text-gray-500">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap truncate max-w-xs">
                                <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                    <?php echo htmlspecialchars($row['url']); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Most Improved Keywords -->
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Most Improved Keywords (Google)</h3>
    <?php if (empty($most_improved)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
            <p>No improved Google rankings available for this report.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($most_improved as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 whitespace-nowrap"><?php echo htmlspecialchars($row['keyword']); ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?php echo $row['rank']; ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?php echo $row['previous_rank']; ?></td>
                            <td class="px-4 py-2 whitespace-nowrap font-semibold">
                                <span class="text-green-600">+<?php echo $row['difference']; ?></span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap truncate max-w-xs">
                                <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                    <?php echo htmlspecialchars($row['url']); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- View all rankings button -->
    <div class="mt-8 text-center">
        <a href="index.php?action=positions&id=<?php echo $report['report_id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z" />
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z" clip-rule="evenodd" />
            </svg>
            View All Search Positions
        </a>
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