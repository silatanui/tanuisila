<?php
$source = 'C:/xampp/htdocs/tanuisila/IMG_20260625_115331.jpg';
$dest = 'C:/xampp/htdocs/tanuisila/public/profile.jpg';

$log = [];
$log[] = "Source existence check: " . (file_exists($source) ? "YES" : "NO");
if (file_exists($source)) {
    $log[] = "Source size: " . filesize($source) . " bytes";
    $success = copy($source, $dest);
    $log[] = "Copy result: " . ($success ? "SUCCESS" : "FAILED");
    if (!$success) {
        $err = error_get_last();
        $log[] = "Error: " . json_encode($err);
    }
} else {
    $log[] = "Searching folder contents...";
    $files = glob('C:/xampp/htdocs/tanuisila/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            $log[] = "  File: " . basename($file) . " (" . filesize($file) . " bytes)";
        }
    }
}

$log[] = "Dest existence: " . (file_exists($dest) ? "YES" : "NO");

file_put_contents('C:/xampp/htdocs/tanuisila/imagetest_result.txt', implode("\n", $log));
echo "DONE";
