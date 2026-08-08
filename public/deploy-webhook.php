<?php
/**
 * GitHub Webhook Endpoint — Deploy automático ao receber push em main.
 */
$secretFile = __DIR__ . '/.deploy-secret';
$secret = file_exists($secretFile) ? trim(file_get_contents($secretFile)) : '';

if ($secret) {
    $payload = file_get_contents('php://input');
    $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    $provided = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if (!hash_equals($signature, $provided)) {
        http_response_code(403);
        die('Invalid signature');
    }
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
$payload = json_decode(file_get_contents('php://input'), true);
$ref = $payload['ref'] ?? '';

if ($event !== 'push' || $ref !== 'refs/heads/main') {
    echo "Ignored\n";
    exit;
}

$gitDir  = '/home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com';
$appDir  = '/home/medicalthermo/tarefas.medicalthermo.com';
$gitBin  = '/usr/local/cpanel/3rdparty/lib/path-bin/git';

header('Content-Type: text/plain; charset=utf-8');
echo '=== Deploy iniciado em ' . date('c') . " ===\n\n";

echo "--- git pull ---\n";
$cmd = "cd {$gitDir} && {$gitBin} checkout -- . 2>&1 && {$gitBin} pull origin main 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";
flush();

if ($code !== 0) {
    http_response_code(500);
    echo "\nFALHA no git pull. Deploy abortado.\n";
    exit;
}

$htaccess = $gitDir . '/public/.htaccess';
$htcontent = file_get_contents($htaccess);
if (strpos($htcontent, 'ea-php83') === false) {
    file_put_contents($htaccess, "AddHandler application/x-httpd-ea-php83 .php\n\n" . $htcontent);
}

echo "\n--- sync para app dir ---\n";
$cmd = "rsync -a --exclude='.git' --exclude='vendor' --exclude='.env' --exclude='storage' --exclude='node_modules' {$gitDir}/ {$appDir}/ 2>&1";
exec($cmd, $output2, $code2);
echo implode("\n", $output2) . "\n";
flush();

echo "\n--- deploy/publicar.sh ---\n";
$cmd = "cd {$appDir} && /bin/bash deploy/publicar.sh 2>&1";
exec($cmd, $output3, $code3);
echo implode("\n", $output3) . "\n";

echo "\n--- sync final de volta ---\n";
$cmd = "rsync -a {$appDir}/vendor/ {$gitDir}/vendor/ 2>&1 && cp {$appDir}/.env {$gitDir}/.env 2>&1 && cp {$appDir}/database/database.sqlite {$gitDir}/database/database.sqlite 2>&1";
exec($cmd, $output4, $code4);
echo implode("\n", $output4) . "\n";

if ($code3 === 0) {
    echo "\n=== Deploy concluído com sucesso ===\n";
} else {
    http_response_code(500);
    echo "\n=== Deploy concluído com ERROS ===\n";
}
