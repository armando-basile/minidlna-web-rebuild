<?php
include_once "conf/config.php";
include_once "core/minidlna.php";

echo "<h1>Debug MiniDLNA Parsing</h1>";

// Test 1: Fetch content
echo "<h2>Test 1: Fetch content</h2>";
$content = @file_get_contents('http://127.0.0.1:8200');
if ($content === false) {
    echo "ERROR: Cannot fetch content<br>";
    exit;
}
echo "Content fetched successfully<br>";
echo "<textarea style='width:100%; height:200px;'>" . htmlspecialchars($content) . "</textarea><br>";

// Test 2: Regex matching
echo "<h2>Test 2: Regex Matching</h2>";
$audioMatches = [];
$videoMatches = [];
$imageMatches = [];

$audioResult = preg_match('/<td>Audio files<\/td>\s*<td>(\d+)<\/td>/', $content, $audioMatches);
$videoResult = preg_match('/<td>Video files<\/td>\s*<td>(\d+)<\/td>/', $content, $videoMatches);
$imageResult = preg_match('/<td>Image files<\/td>\s*<td>(\d+)<\/td>/', $content, $imageMatches);

echo "Audio: preg_match result = $audioResult<br>";
echo "Audio matches: <pre>" . print_r($audioMatches, true) . "</pre>";

echo "Video: preg_match result = $videoResult<br>";
echo "Video matches: <pre>" . print_r($videoMatches, true) . "</pre>";

echo "Image: preg_match result = $imageResult<br>";
echo "Image matches: <pre>" . print_r($imageMatches, true) . "</pre>";

// Test 3: Extract values
echo "<h2>Test 3: Extract Values</h2>";
$audio = isset($audioMatches[1]) ? (int)$audioMatches[1] : 0;
$video = isset($videoMatches[1]) ? (int)$videoMatches[1] : 0;
$images = isset($imageMatches[1]) ? (int)$imageMatches[1] : 0;

echo "Audio: $audio<br>";
echo "Video: $video<br>";
echo "Images: $images<br>";

// Test 4: Call actual function
echo "<h2>Test 4: Call GetMiniDLNAStatus()</h2>";
try {
    $status = minidlna::GetMiniDLNAStatus();
    echo "Audio from function: " . $status->AUDIO . "<br>";
    echo "Video from function: " . $status->VIDEO . "<br>";
    echo "Images from function: " . $status->IMAGES . "<br>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

// Test 5: Search for specific patterns
echo "<h2>Test 5: Search for 'Video files'</h2>";
if (strpos($content, 'Video files') !== false) {
    echo "✓ 'Video files' FOUND in content<br>";
    // Mostra il contesto intorno a "Video files"
    $pos = strpos($content, 'Video files');
    $context = substr($content, max(0, $pos - 50), 150);
    echo "Context: <pre>" . htmlspecialchars($context) . "</pre>";
} else {
    echo "✗ 'Video files' NOT FOUND in content<br>";
}

// Test 6: Case sensitivity check
echo "<h2>Test 6: Case Check</h2>";
if (preg_match('/<td>video files<\/td>/i', $content)) {
    echo "Found with case-insensitive search<br>";
}
?>