<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Kits\SchedulerController;
use Illuminate\Http\Request;

try {
    echo "Testing SchedulerController::overdue()...\n\n";
    
    $controller = new SchedulerController();
    $response = $controller->overdue();
    
    echo "Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        echo "Content length: " . strlen($content) . " bytes\n";
        
        if (strlen($content) < 1000) {
            echo "Content:\n" . $content . "\n";
        } else {
            echo "First 500 chars:\n" . substr($content, 0, 500) . "\n";
        }
    }
    
    echo "\nSuccess!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
