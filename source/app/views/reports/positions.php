<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="flex flex-col items-center justify-center mb-8 text-center hidden print-show">
    <div>
        <div class="flex items-center justify-center">
            <h1 class="text-4xl font-bold text-slate-800"><?php $period = $report['report_period']; echo date('F Y', strtotime($period . '-01')); ?> Search Engine Positions</h1>
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
    </div>
</div>
<div class="flex items-center mb-6 print-hidden relative">
     <a href="<?php echo $_ENV['SITE_URL']; ?>/details/<?php echo $report['report_id']; ?>" class="bg-white rounded-md p-2 mr-2 hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        Search Positions - <?php 
        $period = $report['report_period'];
        echo date('F Y', strtotime($period . '-01'));
        ?>
    </h1>
    <button id="report-print-btn" class="inline-flex items-center px-4 py-2 rounded-md bg-white border border-slate-200 shadow-sm text-slate-700 hover:bg-slate-50 transition-colors absolute top-0 right-0"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-500" viewBox="0 0 512 512" fill="currentColor"><path d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>Print</button>
</div>
<div class="bg-white rounded-lg shadow-md p-6 mb-6 print-no-padding">
    <!-- Tabs for different search engines -->
    <div class="mb-4 border-b">
        <ul class="flex flex-wrap -mb-px" id="seoTabs" role="tablist">
            <?php 
            $engines = [
                'google' => 'Google',
                'google_mobile' => 'Google Mobile',
                'yahoo' => 'Yahoo',
                'bing' => 'Bing'
            ];
            
            $first = true;
            foreach ($engines as $key => $label): 
                $var_name = "{$key}_data";
                $count = isset($$var_name) ? count($$var_name) : 0;
            ?>
                <li class="mr-2" role="presentation">
                    <button class="print-gray inline-block py-2 px-4 <?php echo $first ? 'text-blue-600 hover:text-blue-800 font-medium border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 font-medium border-b-2 border-transparent'; ?> rounded-t-lg" 
                            id="<?php echo $key; ?>-tab" data-tabs-target="#<?php echo $key; ?>" type="button" role="tab" 
                            aria-controls="<?php echo $key; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                        <?php echo $label; ?> (<?php echo $count; ?>)
                    </button>
                </li>
            <?php 
                $first = false;
            endforeach; 
            ?>
        </ul>
    </div>
    <!-- Tab content -->
    <div id="tabContent">
        <?php 
        $first = true;
        foreach ($engines as $key => $label): 
            $var_name = "{$key}_data";
            $data = isset($$var_name) ? $$var_name : [];
            $count = isset($$var_name) ? count($$var_name) : 0;
        ?>
            <?php if( !$first ) {?>
                <div class="page-break hidden"></div>
            <?php } ?>
            <div class="<?php echo $first ? 'block' : 'hidden'; ?> print-show" id="<?php echo $key; ?>" role="tabpanel" aria-labelledby="<?php echo $key; ?>-tab">
                <h3 class="text-2xl border-b py-2 px-4 text-gray-500 font-medium mb-6 hidden print-show"><?php echo $label; ?> (<?php echo $count; ?>)</h3>
                <?php if (empty($data)): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                        <p>No <?php echo $label; ?> rankings available for this report.</p>
                    </div>
                <?php else: ?>
                    <!-- Filter/search controls -->
                    <div class="mb-4 flex flex-wrap items-center gap-4 print-hidden">
                        <div class="relative">
                            <input type="text" id="<?php echo $key; ?>-search" placeholder="Search keywords..." 
                                   class="border rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                   onkeyup="filterTable('<?php echo $key; ?>-table', this.value)">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <select id="<?php echo $key; ?>-rank-filter" class="border rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                onchange="filterRank('<?php echo $key; ?>-table', this.value)">
                            <option value="0">All rankings</option>
                            <option value="10">Top 10</option>
                            <option value="20">Top 20</option>
                            <option value="50">Top 50</option>
                            <option value="100">Top 100</option>
                        </select>
                        
                        <button onclick="sortTable('<?php echo $key; ?>-table', 1)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm">
                            Sort by Rank
                        </button>
                        
                        <button onclick="sortTable('<?php echo $key; ?>-table', 3, true)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm">
                            Sort by Change
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="<?php echo $key; ?>-table" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-base fon text-gray-500 uppercase tracking-wider">Keyword</th>
                                    <th class="px-4 py-2 text-left text-base font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                    <th class="px-4 py-2 text-left text-base font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                    <th class="px-4 py-2 text-left text-base font-medium text-gray-500 uppercase tracking-wider">Previous</th>
                                    <th class="px-4 py-2 text-left text-base font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                </tr>
                            </thead>
                              <tbody class="bg-white divide-y divide-gray-200">
    <?php foreach ($data as $row): ?>
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 whitespace-nowrap"><?php echo $row['keyword']; ?></td>
            <td class="px-4 py-2 whitespace-nowrap truncate max-w-xs">
                <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="text-blue-500 hover:underline">
                    <?php echo htmlspecialchars($row['url']); ?>
                </a>
            </td>
            <td class="px-4 py-2 whitespace-nowrap" data-rank="<?php echo $row['rank']; ?>">
                <?php if ($row['rank'] == 101): ?>
                    <span class="text-gray-400">—</span>
                <?php else: ?>
                    <div class="flex items-center space-x-1.5">
                        <?php if ($row['rank'] <= 3): ?>
                            <span><?php echo $row['rank']; ?> 😊</span>
                        <?php else: ?>
                            <span><?php echo $row['rank']; ?></span>
                        <?php endif; ?>
                        <?php if ($row['previous_rank'] == 101): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-300" viewBox="0 0 20 20" fill="currentColor" title="New entry">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <?php if ($row['previous_rank'] == 101): ?>
                    <span class="text-gray-400">—</span>
                <?php else: ?>
                    <?php echo $row['previous_rank']; ?>
                <?php endif; ?>
            </td>
            <td class="px-4 py-2 whitespace-nowrap" data-change="<?php echo $row['difference']; ?>">
                <?php if ($row['difference'] == 100): ?>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300 mr-1.5" viewBox="0 0 20 20" fill="currentColor" title="New entry">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="text-blue-600 font-medium">New</span>
                    </div>
                <?php elseif ($row['difference'] == -100): ?>
                    <div class="flex items-center">
                        <div class="h-6 w-1 bg-red-500 rounded-sm mr-2"></div>
                        <span class="text-red-600 font-medium">Out</span>
                    </div>
                <?php elseif ($row['difference'] > 0): ?>
                    <div class="flex items-center">
                        <span class="text-green-500 font-medium text-xl">+<?php echo $row['difference']; ?> ↑</span>
                    </div>
                <?php elseif ($row['difference'] < 0 && $row['difference'] != -100): ?>
                    <div class="flex items-center">
                        <span class="text-red-600 font-medium"><?php echo $row['difference']; ?></span>
                    </div>
                <?php elseif ($row['rank'] == 101 && $row['previous_rank'] == 101): ?>
                    <div class="flex items-center">
                        <span class="text-gray-400 font-medium">Not ranked</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center">
                        <div class="h-0.5 w-6 bg-gray-300 rounded-sm"></div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                          
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php 
            $first = false;
        endforeach; 
        ?>
    </div>
</div>

</div>

<!-- JavaScript for tab switching and table functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('[role="tab"]');
    const tabPanels = document.querySelectorAll('[role="tabpanel"]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('hidden');
            });
            
            // Show selected panel
            const targetId = this.getAttribute('data-tabs-target');
            document.querySelector(targetId).classList.remove('hidden');
            
            // Update active state
            tabButtons.forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-500', 'border-transparent');
                btn.setAttribute('aria-selected', 'false');
            });
            
            this.classList.remove('text-gray-500', 'border-transparent');
            this.classList.add('text-blue-600', 'border-blue-600');
            this.setAttribute('aria-selected', 'true');
        });
    });
    
    // Initialize tables (sort by rank by default)
    setTimeout(function() {
        sortTable('google-table', 1);
        // Add similar calls for other tables
    }, 100);
});

// Table filtering function
function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const filterValue = query.toLowerCase();
    
    for (let i = 0; i < rows.length; i++) {
        const keywordCell = rows[i].getElementsByTagName('td')[0];
        if (keywordCell) {
            const keywordText = keywordCell.textContent || keywordCell.innerText;
            if (keywordText.toLowerCase().indexOf(filterValue) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}

// Filter by rank function
function filterRank(tableId, rankLimit) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const limit = parseInt(rankLimit);
    
    if (limit === 0) {
        // Show all rows
        for (let i = 0; i < rows.length; i++) {
            rows[i].style.display = "";
        }
        return;
    }
    
    for (let i = 0; i < rows.length; i++) {
        const rankCell = rows[i].getElementsByTagName('td')[1];
        if (rankCell) {
            const rank = parseInt(rankCell.getAttribute('data-rank'));
            if (rank <= limit) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}

// Table sorting function
function sortTable(tableId, columnIndex, isNumeric = false) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let switching = true;
    let rows, shouldSwitch, i;
    let switchCount = 0;
    let direction = "asc";
    
    while (switching) {
        switching = false;
        rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (i = 0; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            
            const x = rows[i].getElementsByTagName('td')[columnIndex];
            const y = rows[i + 1].getElementsByTagName('td')[columnIndex];
            
            let xValue, yValue;
            
            if (isNumeric) {
                // For numeric columns, use data attribute
                xValue = parseInt(x.getAttribute('data-change') || 0);
                yValue = parseInt(y.getAttribute('data-change') || 0);
            } else if (columnIndex === 1) {
                // For rank column
                xValue = parseInt(x.getAttribute('data-rank') || 0);
                yValue = parseInt(y.getAttribute('data-rank') || 0);
            } else {
                // For text columns
                xValue = x.textContent.toLowerCase();
                yValue = y.textContent.toLowerCase();
            }
            
            if (direction === "asc") {
                if (xValue > yValue) {
                    shouldSwitch = true;
                    break;
                }
            } else {
                if (xValue < yValue) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchCount++;
        } else {
            if (switchCount === 0 && direction === "asc") {
                direction = "desc";
                switching = true;
            }
        }
    }
}

document.getElementById('report-print-btn').addEventListener('click', () => {
    window.print();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>