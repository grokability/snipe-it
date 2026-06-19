<?php
// Uncovered lines de un archivo en un clover dado: php _unc.php <sufijo> <clover>
$target = $argv[1];
$clover = $argv[2] ?? __DIR__.'/clover.xml';
$xml = simplexml_load_file($clover);
foreach ($xml->xpath('//file') as $file) {
    $name = str_replace('\\', '/', (string) $file['name']);
    if (! str_ends_with($name, $target)) {
        continue;
    }
    $un = [];
    foreach ($file->line as $l) {
        if ((string) $l['type'] === 'method') {
            continue;
        }
        if ((int) $l['count'] === 0) {
            $un[] = (int) $l['num'];
        }
    }
    $r = [];
    $s = $p = null;
    foreach ($un as $n) {
        if ($s === null) {
            $s = $p = $n;
        } elseif ($n === $p + 1) {
            $p = $n;
        } else {
            $r[] = $s === $p ? "$s" : "$s-$p";
            $s = $p = $n;
        }
    }
    if ($s !== null) {
        $r[] = $s === $p ? "$s" : "$s-$p";
    }
    echo $name."\n";
    echo 'Uncovered ('.count($un).'): '.implode(', ', $r)."\n";
    return;
}
echo "no match\n";
