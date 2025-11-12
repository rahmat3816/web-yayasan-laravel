<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$component = new App\Livewire\TahfizhTargetPlanner();
$component->mount();
$component->updatedJuz(30);

echo 'Juz: ' . $component->juz . PHP_EOL;
echo 'Surah options: ' . count($component->surahOptions) . PHP_EOL;
echo json_encode(array_slice($component->surahOptions, 0, 3), JSON_PRETTY_PRINT) . PHP_EOL;
