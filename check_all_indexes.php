<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$indexes = DB::select("SELECT indexname, tablename FROM pg_indexes WHERE schemaname = 'public' ORDER BY tablename, indexname");
echo "All indexes in public schema:\n";
foreach ($indexes as $idx) {
    echo "  - {$idx->tablename}.{$idx->indexname}\n";
}
