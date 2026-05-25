<?php
$payload = json_encode([
    'subject'    => 'Hello everyone',
    'html'       => '<h1>Hi!</h1>',
    'recipients' => [
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob',   'email' => 'bob@example.com'],
    ],
]);

$ctx = stream_context_create([
    'http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content'       => $payload,
        'ignore_errors' => true,
    ],
]);

$response = file_get_contents('http://127.0.0.1:8002/mail/send-bulk', false, $ctx);
$status   = $http_response_header[0] ?? 'no response';

echo "Status : $status\n";
echo "Body   : $response\n";
