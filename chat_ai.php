<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload) || !isset($payload['messages']) || !is_array($payload['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OPENAI_API_KEY is not set on the server']);
    exit;
}

$model = isset($payload['model']) ? $payload['model'] : 'gpt-4o-mini';
$temperature = isset($payload['temperature']) ? floatval($payload['temperature']) : 0.4;

$systemPrompt = [
    'role' => 'system',
    'content' => 'Bạn là trợ lý Sunny Sport. Hỗ trợ khách đặt sân cầu lông hoặc mua phụ kiện. Hỏi rõ các thông tin còn thiếu và trả lời ngắn gọn, lịch sự. Ngôn ngữ: tiếng Việt.'
];

$messages = $payload['messages'];
array_unshift($messages, $systemPrompt);

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$body = json_encode([
    'model' => $model,
    'temperature' => $temperature,
    'messages' => $messages,
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to call OpenAI']);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo $response;
    exit;
}

$data = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? '';

echo json_encode([
    'reply' => $reply,
]); 