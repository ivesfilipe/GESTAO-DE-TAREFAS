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
$phpBin  = '/opt/cpanel/ea-php83/root/usr/bin/php';

header('Content-Type: text/plain; charset=utf-8');
echo '=== Deploy iniciado em ' . date('c') . " ===\n\n";

echo "--- git pull ---\n";
$cmd = "cd {$gitDir} && {$gitBin} pull origin main 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";
flush();

if ($code !== 0) {
    http_response_code(500);
    echo "\nFALHA no git pull. Deploy abortado.\n";
    exit;
}

echo "\n--- sync arquivos para app dir ---\n";
// Sincroniza arquivos novos/alterados do git dir para o app dir
// (rsync sem apagar arquivos que so existem no destino como vendor/ e .env)
$cmd = "rsync -a --exclude='.git' --exclude='vendor' --exclude='.env' --exclude='storage' --exclude='node_modules' {$gitDir}/ {$appDir}/ 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";
flush();

echo "\n--- deploy/publicar.sh ---\n";
$cmd = "cd {$appDir} && /bin/bash deploy/publicar.sh 2>&1";
exec($cmd, $output, $code);
echo implode("\n", $output) . "\n";

echo "\n--- sync vendor/db de volta para git dir ---\n";
// Copia vendor e .env (atualizados pelo composer) de volta para o git dir
$cmd = "rsync -a {$appDir}/vendor/ {$gitDir}/vendor/ 2>&1 && cp {$appDir}/.env {$gitDir}/.env 2>&1 && cp {$appDir}/database/database.sqlite {$gitDir}/database/database.sqlite 2>&1";
exec($cmd, $output, $code2);
echo implode("\n", $output) . "\n";

if ($code === 0) {
    echo "\n=== Deploy concluído com sucesso ===\n";
} else {
    http_response_code(500);
    echo "\n=== Deploy concluído com ERROS ===\n";
}
