<?php
// Muestra rangos de lineas sin cubrir para un archivo dado del clover.
$target = $argv[1] ?? 'Helper.php';
$xml = simplexml_load_file(__DIR__.'/clover.xml');
foreach ($xml->xpath('//file') as $file) {
    $name = str_replace('\\', '/', (string) $file['name']);
    if (! str_ends_with($name, $target)) {
        continue;
    }
    $uncovered = [];
    foreach ($file->line as $line) {
        if ((string) $line['type'] === 'method') {
            continue;
        }
        if ((int) $line['count'] === 0) {
            $uncovered[] = (int) $line['num'];
        }
    }
    // Agrupa en rangos contiguos.
    $ranges = [];
    $start = $prev = null;
    foreach ($uncovered as $n) {
        if ($start === null) {
            $start = $prev = $n;
        } elseif ($n === $prev + 1) {
            $prev = $n;
        } else {
            $ranges[] = $start === $prev ? "$start" : "$start-$prev";
            $start = $prev = $n;
        }
    }
    if ($start !== null) {
        $ranges[] = $start === $prev ? "$start" : "$start-$prev";
    }
    echo $name."\n";
    echo 'Uncovered lines ('.count($uncovered).'): '.implode(', ', $ranges)."\n";
    return;
}
echo "No match for $target\n";
