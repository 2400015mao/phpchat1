<?php
// .envから設定を読み込む（簡易実装）
$env = parse_ini_file('.env');
$apiKey = $env['OPENAI_API_KEY'] ?? '';
$model = "gpt-5-mini";

$result = "";
$input = $_POST['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($input)) {
    $url = "https://api.openai.com/v1/chat/completions";

    // プロンプトの設定
    $data = [
        "model" => $model,
        "messages" => [
            [
                "role" => "system", 
                "content" => "与えられたテキストから最も重要な単語を1つ選び、次のフォーマットで返してください。余計な説明は一切不要です。\n原語: [単語]\n英訳: [English Translation]"
            ],
            ["role" => "user", "content" => $input]
        ],
        "temperature" => 0.3
    ];

    // cURLによるリクエスト（ライブラリ不使用）
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $result = "Error: " . curl_error($ch);
    } else {
        $json = json_decode($response, true);
        $result = $json['choices'][0]['message']['content'] ?? "エラーが発生しました。";
    }
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Chat Keyword Extractor</title>
</head>
<body>
    <h2>キーワード抽出チャット</h2>
    <form method="post">
        <input type="text" name="message" placeholder="メッセージを入力..." style="width: 300px;" required>
        <button type="submit">送信</button>
    </form>

    <?php if ($result): ?>
        <h3>実行結果:</h3>
        <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px;"><?php echo htmlspecialchars($result); ?></pre>
    <?php endif; ?>
</body>
</html>