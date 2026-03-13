<?php
// TODO: Translate
if (! empty($greeting)) {
    echo $greeting, "\n\n";
} else {
    echo $level == 'error' ? 'Whoops!' : 'Hello!', "\n\n";
}

if (! empty($introLines)) {
    echo implode("\n", $introLines), "\n\n";
}

if (isset($actionText)) {
    echo "{$actionText}: {$actionUrl}", "\n\n";
}

if (! empty($outroLines)) {
    echo implode("\n", $outroLines), "\n\n";
}

if (! empty($signOff)) {
    echo implode("\n", $signOff), "\n";
}
if (! empty($siteName)) {
    echo implode("\n", $siteName), "\n";
}
