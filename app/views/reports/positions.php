<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="flex items-center mb-6">
    <a href="index.php?action=details&id=<?php echo $report['report_id']; ?>" class="bg-white rounded-md p-2 mr-2 hover:bg-gray-100">
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
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
                    <button class="inline-block py-2 px-4 <?php echo $first ? 'text-blue-600 hover:text-blue-800 font-medium border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 font-medium border-b-2 border-transparent'; ?> rounded-t-lg" 
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
        ?>
            <div class="<?php echo $first ? 'block' : 'hidden'; ?>" id="<?php echo $key; ?>" role="tabpanel" aria-labelledby="<?php echo $key; ?>-tab">
                <?php if (empty($data)): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                        <p>No <?php echo $label; ?> rankings available for this report.</p>
                    </div>
                <?php else: ?>
                    <!-- Filter/search controls -->
                    <div class="mb-4 flex flex-wrap items-center gap-4">
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keyword</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($data as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 whitespace-nowrap"><?php echo htmlspecialchars($row['keyword']); ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap truncate max-w-xs">
                                            <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                                <?php echo htmlspecialchars($row['url']); ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap" data-rank="<?php echo $row['rank']; ?>"><?php echo $row['rank']; ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap"><?php echo $row['previous_rank']; ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap" data-change="<?php echo $row['difference']; ?>">
                                            <?php if ($row['difference'] > 0): ?>
                                                <span class="text-green-600">+<?php echo $row['difference']; ?></span>
                                            <?php elseif ($row['difference'] < 0): ?>
                                                <span class="text-red-600"><?php echo $row['difference']; ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-500">0</span>
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
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>