<?php
// Analiza clover.xml y lista los archivos de app/ con mas lineas sin cubrir.
$xml = simplexml_load_file(__DIR__.'/clover.xml');
$files = $xml->xpath('//file');
echo 'Total file nodes: '.count($files)."\n";

$rows = [];
foreach ($files as $file) {
    $name = str_replace('\\', '/', (string) $file['name']);
    if (strpos($name, '/app/') === false) {
        continue;
    }
    $mm = $file->xpath('metrics');
    if (! $mm) {
        continue;
    }
    $m = $mm[count($mm) - 1]; // file-level metrics (last)
    $total = (int) $m['statements'];
    $covered = (int) $m['coveredstatements'];
    $uncovered = $total - $covered;
    if ($uncovered < 30) {
        continue;
    }
    $short = substr($name, strpos($name, '/app/') + 1);
    $pct = $total > 0 ? round($covered / $total * 100, 1) : 0;
    $rows[] = [$short, $covered, $total, $uncovered, $pct];
}
usort($rows, fn ($a, $b) => $b[3] - $a[3]);
printf("%-55s %7s %7s %9s %6s\n", 'FILE', 'COV', 'TOTAL', 'UNCOV', '%');
echo str_repeat('-', 90)."\n";
foreach (array_slice($rows, 0, 30) as $r) {
    printf("%-55s %7d %7d %9d %5.1f%%\n", $r[0], $r[1], $r[2], $r[3], $r[4]);
}
