<?php
// Simple test file to check /livewire/update endpoint
$url = 'http://biopacific.test/livewire/update';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'fingerprint' => ['id' => 1, 'name' => 'job-openings-form'],
    'serverMemo' => ['data' => [], 'checksum' => 'test'],
    'updates' => []
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: test'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $http_code . "\n";
echo "Response Length: " . strlen($response) . "\n";
echo "First 200 chars: " . substr($response, 0, 200) . "\n";
?>
