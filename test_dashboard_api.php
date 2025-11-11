<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\Kits\MaintenanceDashboardController;
use Illuminate\Http\Request;

try {
    $controller = new MaintenanceDashboardController();
    $response = $controller->getTodoItems(new Request());
    $data = json_decode($response->getContent(), true);
    
    echo "Total items: " . $data['total'] . PHP_EOL;
    
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . PHP_EOL;
    }
    
    if (isset($data['rows'])) {
        echo "\nItems:\n";
        foreach ($data['rows'] as $item) {
            echo "- [{$item['priority_label']}] {$item['type']}: {$item['task']} (Due: {$item['due_date']})\n";
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
