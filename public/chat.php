<?php
session_start();

// Handle personality selection
if (isset($_POST['style'])) {
    $_SESSION['style'] = $_POST['style'];
}
$style = $_POST['style'] ?? $_SESSION['style'] ?? 'normal';


// Handle chat reset
if (isset($_POST['clear'])) {
    $_SESSION['chat'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle prompt via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {
    $apiKey = '84RhbRJXrltzcl3vJ0Q9XqYuHYn4t7LC3h4ArmXWAgU8mxf1b9dtJQQJ99BEAC5RqLJXJ3w3AAABACOGRNpM';
    $endpoint = 'https://logicnetcrm-ai.openai.azure.com/openai/deployments/gpt-4o-mini/chat/completions?api-version=2025-01-01-preview';

    $prompt = trim($_POST['prompt']);
    $_SESSION['chat'] = $_SESSION['chat'] ?? [];
    $_SESSION['chat'][] = ['role' => 'user', 'content' => $prompt];

    // Set system message
        switch ($style) {
            case 'pirate':
                $systemContent = "Ești un pirat. Vorbește ca un lup de mare. Folosește expresii precum 'Arrr!', 'matelot', 'pământean' și 'fulgere și tunete!'.";
                break;
            case 'robot':
                $systemContent = "Ești un asistent robot. Răspunde în propoziții scurte și mecanice. Referă-te la tine ca 'această unitate'.";
                break;
            case 'corporate':
                $systemContent = "Ești un asistent corporatist. Vorbește în jargon de afaceri. Folosește cuvinte precum 'sinergie', 'valoare adăugată', 'indicatori de performanță' și 'perspective acționabile', fără să spui de fapt mare lucru.";
                break; 
            case 'georgist':
                $systemContent = "Esti un mare marea fan al lui Calin Georgescu. Il vezi ca pe un guru iluminat. Scrie ca un lider spiritual care vorbește cu solemnitate despre rolul sacru al României în echilibrul planetei. Folosește metafore cosmice, limbaj ezoteric și fraze solemne. Invocă des Carpații, codurile energetice, ADN-ul spiritual, rugăciunea colectivă și pericolele globalismului. Transmite certitudini absolute despre misiunea divină a românilor și folosește un ton profetic, dar blând. Fiecare propoziție trebuie să pară o revelație.";
                break;
            default:
                $systemContent = "Ești un asistent util și prietenos.";
        }

    $systemMessage = ['role' => 'system', 'content' => $systemContent];
    $history = array_merge([$systemMessage], array_slice($_SESSION['chat'], -10));

    $data = [
        'messages' => $history,
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $headers = [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $reply = $result['choices'][0]['message']['content'] ?? 'No response.';
    $_SESSION['chat'][] = ['role' => 'assistant', 'content' => $reply];

    echo $reply;
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat Bot cu personalitate</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 700px; margin: auto; }
        #chat-box { border: 1px solid #ccc; padding: 10px; background: #f9f9f9; height: 400px; overflow-y: auto; margin-bottom: 10px; }
        .user { font-weight: bold; }
        .assistant { color: #0077cc; }
        .message { margin-bottom: 10px; }
        textarea { width: 100%; height: 60px; }
        button { margin-top: 10px; }
        .controls { display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center; }
    </style>
</head>
<body>

<h2>Chat Bot cu personalitate</h2>

<div class="controls">
    <form method="post">
        <label for="style">Personalitate:</label>
        <select name="style" id="style" onchange="document.getElementById('style-hidden').value = this.value;">
            <option value="normal" <?= $style === 'normal' ? 'selected' : '' ?>>Normala</option>
            <option value="pirate" <?= $style === 'pirate' ? 'selected' : '' ?>>Pirat</option>
            <option value="robot" <?= $style === 'robot' ? 'selected' : '' ?>>Robot</option>
            <option value="corporate" <?= $style === 'corporate' ? 'selected' : '' ?>>Corporatist</option>
            <option value="georgist" <?= $style === 'georgist' ? 'selected' : '' ?>>Georgist</option>
        </select>
    </form>
    <form method="post">
        <button name="clear" value="1">🧹 Clear Chat</button>
    </form>
</div>

<div id="chat-box">
    <?php if (isset($_SESSION['chat'])): ?>
        <?php foreach ($_SESSION['chat'] as $message): ?>
            <div class="message <?= $message['role'] ?>">
                <strong><?= ucfirst($message['role']) ?>:</strong>
                <?= nl2br(htmlspecialchars($message['content'])) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<form id="chat-form">
    <input type="hidden" name="style" id="style-hidden" value="<?= $style ?>">
    <textarea name="prompt" id="prompt" placeholder="Mesaj..." required></textarea>
    <button type="submit">Trimite</button>
</form>

<script>
const form = document.getElementById('chat-form');
const chatBox = document.getElementById('chat-box');
const promptField = document.getElementById('prompt');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const prompt = promptField.value.trim();
    if (!prompt) return;

    const style = document.getElementById('style').value;
    document.getElementById('style-hidden').value = style;

    const userMsg = document.createElement('div');
    userMsg.className = 'message user';
    userMsg.innerHTML = `<strong>User:</strong> ${prompt}`;
    chatBox.appendChild(userMsg);
    promptField.value = '';

    const aiMsg = document.createElement('div');
    aiMsg.className = 'message assistant';
    const span = document.createElement('span');
    span.className = 'typing';
    aiMsg.innerHTML = `<strong>Assistant:</strong> `;
    aiMsg.appendChild(span);
    chatBox.appendChild(aiMsg);
    chatBox.scrollTop = chatBox.scrollHeight;

    try {
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ prompt, style })
        });
        const text = await res.text();
        typeText(span, text);
    } catch (err) {
        span.textContent = 'Error loading response.';
    }
});

function typeText(el, text, i = 0) {
    if (i < text.length) {
        el.innerHTML += (text.charAt(i) === '\n') ? '<br>' : text.charAt(i);
        chatBox.scrollTop = chatBox.scrollHeight;
        setTimeout(() => typeText(el, text, i + 1), 20);
    }
}
</script>

</body>
</html>
