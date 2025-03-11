<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="flex items-center mb-6">
    <a href="<?php echo SITE_URL; ?>/details/<?php echo $report['report_id']; ?>" class="bg-white rounded-md p-2 mr-2 hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        Keywords - <?php 
        $period = $report['report_period'];
        echo date('F Y', strtotime($period . '-01'));
        ?>
    </h1>
</div>
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-base fon text-gray-500 uppercase tracking-wider">Keyword</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php
                foreach($report['report_keywords'] as $k){
            ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap"><?php echo $k; ?></td>
                    </tr>
            <?php
                }
            ?>
        </tbody>              
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>