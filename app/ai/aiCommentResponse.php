<?php
declare(strict_types=1);

// Only run this file as an API endpoint for POST requests.
// If it's included from index.php during a normal page render, just return.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If you’d rather be explicit, you could do:
    // http_response_code(405);
    // echo 'Method Not Allowed';
    return;
}

header('Content-Type: application/json');

// --- Read body (JSON or form) ---
$raw = file_get_contents('php://input');
$input = null;

// Try JSON first
if ($raw !== false && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $input = $decoded;
    }
}

// Fallback: regular form POST
if ($input === null && !empty($_POST)) {
    $input = $_POST;
}

if (!is_array($input)) {
    echo json_encode([
        'error' => 'Invalid JSON body',
        // uncomment while debugging:
        // 'raw'   => $raw,
        // 'post'  => $_POST,
    ]);
    exit;
}

$event    = $input['event']    ?? '';
$question = trim((string)($input['question'] ?? ''));
$postText = trim((string)($input['post'] ?? ''));

if ($event !== 'userChat' || $question === '') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$apiKey = getenv('API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'API key missing or unreadable secret']);
    exit;
}

// Optional: trim to avoid huge payloads
$questionShort = mb_substr($question, 0, 2000);
$postShort     = mb_substr($postText, 0, 8000);

// --- System prompt ---
$systemPrompt = <<<EOT
You are an assistant for a study discussion board called "Study Hall".
You are given the text of a post and a user's question about that post.
Read the post carefully and then answer the user's question in a clear, concise, and helpful way.
If the user seems to want feedback on the post, offer constructive, polite suggestions.
EOT;

$userContent = "Post content:\n\"{$postShort}\"\n\nUser question about the post:\n\"{$questionShort}\"";

// --- OpenAI request ---
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: " . "Bearer {$apiKey}",
]);

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user',   'content' => $userContent],
];

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'model'       => 'gpt-5-nano',
        'messages'    => $messages
    ]),
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'cURL error: ' . curl_error($ch)]);
} elseif ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $response;
    exit;
} else {
    echo $response;
}

curl_close($ch);
exit;
