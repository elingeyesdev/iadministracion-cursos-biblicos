<?php
$content = file_get_contents('resources/views/roles/user_roles.blade.php');
// Search for roles.user with any characters in between
if (preg_match("/route\(['\"]roles\.user\.edit['\"]\)/", $content)) {
    echo "Found with regex: YES" . PHP_EOL;
} else {
    echo "Not found with regex: NO" . PHP_EOL;
}

if (preg_match("/route\(['\"]roles\.user\.clear['\"]\)/", $content)) {
    echo "Found clear with regex: YES" . PHP_EOL;
} else {
    echo "Not found clear with regex: NO" . PHP_EOL;
}

// Show context around line 46
$lines = explode("\n", $content);
for ($i = 44; $i <= 52; $i++) {
    if (isset($lines[$i])) {
        echo "Line $i: " . $lines[$i] . PHP_EOL;
    }
}