<?php

require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

echo "=== MEMULAI BACKUP FIREBASE DATABASE ===\n";

$targets = [
    [
        'name' => 'uji-petik (Database Asli / Production)',
        'credentials' => __DIR__ . '/storage/app/firebase/firebase_credentials_production.json',
        'url' => 'https://uji-petik-default-rtdb.firebaseio.com/',
        'prefix' => 'backup_uji_petik_prod_'
    ],
    [
        'name' => 'tikor-project (Staging / Dev)',
        'credentials' => __DIR__ . '/storage/app/firebase/firebase_credentials.json',
        'url' => 'https://tikor-project-default-rtdb.firebaseio.com/',
        'prefix' => 'backup_tikor_project_staging_'
    ]
];

$backupDir = __DIR__ . '/storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

foreach ($targets as $target) {
    echo "\n[+] Menghubungi " . $target['name'] . "...\n";
    if (!file_exists($target['credentials'])) {
        echo "[-] File kredensial tidak ditemukan: " . $target['credentials'] . "\n";
        continue;
    }

    try {
        $factory = (new Factory)
            ->withServiceAccount($target['credentials'])
            ->withDatabaseUri($target['url']);

        $database = $factory->createDatabase();
        
        echo "[+] Mengunduh seluruh root node data (/)... \n";
        $data = $database->getReference('/')->getValue();

        if ($data === null) {
            echo "[!] Data kosong / null pada " . $target['url'] . "\n";
            $data = [];
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filePath = $backupDir . '/' . $target['prefix'] . $timestamp . '.json';
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($filePath, $json);

        $nodeCount = is_array($data) ? count($data) : 0;
        $fileSizeKb = round(filesize($filePath) / 1024, 2);

        echo "✅ BERHASIL BACKUP!\n";
        echo "   - File: " . $filePath . "\n";
        echo "   - Ukuran: " . $fileSizeKb . " KB\n";
        echo "   - Top-level Nodes: " . implode(', ', array_keys($data ?? [])) . "\n";
    } catch (\Throwable $e) {
        echo "❌ GAGAL backup " . $target['name'] . ": " . $e->getMessage() . "\n";
    }
}

echo "\n=== PROSES BACKUP SELESAI ===\n";
