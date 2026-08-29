<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportsController;
use App\Models\Teacher;

$teacher = Teacher::first();
if (!$teacher) { echo 'No teacher found'; exit(1); }

Auth::loginUsingId($teacher->user_id);

$request = \Illuminate\Http\Request::create('/analytics/export-pdf', 'POST', [
    'period'         => 'weekly',
    'year'           => date('Y'),
    'paper_size'     => 'A4',
    'running_header' => 'first',
    'page_numbers'   => 'footer',
]);
$request->setMethod('POST');

try {
    $controller = new ReportsController();
    $response = $controller->exportAnalyticsPdf($request);

    // Response should be a BinaryFileResponse or StreamedResponse
    $content = '';
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    $size = strlen($content);
    $isPdf = strpos($content, '%PDF') === 0;

    echo 'PDF generated successfully!' . PHP_EOL;
    echo 'Size: ' . number_format($size) . ' bytes' . PHP_EOL;
    echo 'Valid PDF header: ' . ($isPdf ? 'YES' : 'NO') . PHP_EOL;

    // Save to disk for visual check
    file_put_contents('scratch_analytics_report.pdf', $content);
    echo 'Saved to scratch_analytics_report.pdf' . PHP_EOL;

} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'At: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    if ($e->getPrevious()) {
        echo 'Caused by: ' . $e->getPrevious()->getMessage() . PHP_EOL;
    }
}
