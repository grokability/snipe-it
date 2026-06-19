<?php
$xml = simplexml_load_file(__DIR__.'/clover.xml');
$m = $xml->project->metrics;
$el = (int)$m['elements']; $cel = (int)$m['coveredelements'];
$st = (int)$m['statements']; $cst = (int)$m['coveredstatements'];
$me = (int)$m['methods']; $cme = (int)$m['coveredmethods'];
printf("Lineas (statements): %.2f%% (%d / %d)\n", $cst*100/$st, $cst, $st);
printf("Metodos: %.2f%% (%d / %d)\n", $cme*100/$me, $cme, $me);
printf("Elementos: %.2f%% (%d / %d)\n", $cel*100/$el, $cel, $el);
