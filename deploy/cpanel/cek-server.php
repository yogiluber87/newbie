<?php

/*
|--------------------------------------------------------------------------
| cek-server.php - alat bantu diagnosa setelah upload ke cPanel
|--------------------------------------------------------------------------
| Cara pakai:
|  1. Upload berkas ini ke document root (mis. public_html atau .../induk/public)
|  2. Buka https://domain-anda.com/cek-server.php
|  3. HAPUS kembali berkas ini setelah selesai
|
| Tidak menampilkan isi .env maupun password database.
*/

header('Content-Type: text/plain; charset=utf-8');

$akar   = __DIR__;
$proyek = dirname($akar);   // satu tingkat di atas public

function baris(string $label, string $nilai): void
{
    printf("%-26s : %s\n", $label, $nilai);
}

function ya(bool $ok): string
{
    return $ok ? 'YA' : 'TIDAK';
}

echo "=== INFORMASI SERVER ===\n";
baris('PHP versi', PHP_VERSION);
baris('SAPI', PHP_SAPI);
baris('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] ?? '-');
baris('Folder skrip ini', $akar);
baris('Folder proyek (dugaan)', $proyek);

echo "\n=== BERKAS PENTING ===\n";
baris('index.php di docroot', ya(file_exists($akar . '/index.php')));
baris('.htaccess di docroot', ya(file_exists($akar . '/.htaccess')));
baris('vendor/autoload.php', ya(file_exists($proyek . '/vendor/autoload.php')));
baris('.env', ya(file_exists($proyek . '/.env')));
baris('bootstrap/app.php', ya(file_exists($proyek . '/bootstrap/app.php')));
baris('storage/logs bisa ditulis', ya(is_writable($proyek . '/storage/logs')));
baris('bootstrap/cache bisa ditulis', ya(is_writable($proyek . '/bootstrap/cache')));
baris('public/storage (symlink)', ya(file_exists($akar . '/storage') || is_link($akar . '/storage')));
if (is_link($akar . '/storage')) {
    baris('  -> menunjuk ke', (string) @readlink($akar . '/storage'));
} elseif (is_dir($akar . '/storage')) {
    baris('  -> catatan', 'folder biasa, bukan symlink ke storage/app/public');
}

echo "\n=== MODUL APACHE ===\n";
if (function_exists('apache_get_modules')) {
    $modul = apache_get_modules();
    baris('mod_rewrite', ya(in_array('mod_rewrite', $modul, true)));
} else {
    baris('mod_rewrite', 'tidak bisa dideteksi (bukan mod_php)');
}

echo "\n=== DATABASE ===\n";
$env = [];
foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $kunci) {
    if (preg_match('/^' . $kunci . '=(.*)$/m', (string) @file_get_contents($proyek . '/.env'), $m)) {
        $env[$kunci] = trim($m[1], " \t\"'");
    }
}
baris('DB_DATABASE', $env['DB_DATABASE'] ?? '(tidak terbaca)');
baris('DB_USERNAME', $env['DB_USERNAME'] ?? '(tidak terbaca)');

if (!empty($env['DB_DATABASE'])) {
    try {
        new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s', $env['DB_HOST'] ?? 'localhost', $env['DB_PORT'] ?? '3306', $env['DB_DATABASE']),
            $env['DB_USERNAME'] ?? '',
            $env['DB_PASSWORD'] ?? ''
        );
        baris('Koneksi database', 'BERHASIL');
    } catch (Throwable $e) {
        baris('Koneksi database', 'GAGAL: ' . $e->getMessage());
    }
}

echo "\n=== EKSTENSI WAJIB ===\n";
foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'gd'] as $ext) {
    baris($ext, ya(extension_loaded($ext)));
}

echo "\nSelesai. Hapus berkas ini dari server setelah dipakai.\n";
