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
    echo "Ignored (event={$event}, ref={$ref})\n";
    exit;
}

$repoDir = '/home/medicalthermo/home/medicalthermo/tarefas.medicalthermo.com';
$workTree = '/home/medicalthermo/tarefas.medicalthermo.com';
$gitBin = '/usr/local/cpanel/3rdparty/lib/path-bin/git';

header('Content-Type: text/plain; charset=utf-8');
echo "=== Deploy iniciado em " . date('c') . " ===\n\n";

echo "--- git pull ---\n";
$cmd = "cd {$repoDir} && {$gitBin} pull origin main 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";

if ($code !== 0) {
    http_response_code(500);
    echo "\nFALHA no git pull (exit code {$code}). Deploy abortado.\n";
    exit;
}

echo "\n--- deploy/publicar.sh ---\n";
$cmd = "cd {$workTree} && /bin/bash deploy/publicar.sh 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";

if ($code === 0) {
    echo "\n=== Deploy concluído com sucesso ===\n";
} else {
    http_response_code(500);
    echo "\n=== Deploy concluído com ERROS (exit code {$code}) ===\n";
}
