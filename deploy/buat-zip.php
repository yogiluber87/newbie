<?php

/*
|--------------------------------------------------------------------------
| buat-zip.php — pembuat arsip zip dengan pemisah path "/" (aman di Linux)
|--------------------------------------------------------------------------
| Compress-Archive milik Windows menulis pemisah "\" di dalam zip sehingga
| berisiko diekstrak sebagai nama berkas aneh di cPanel. Skrip ini memakai
| ZipArchive (pemisah "/") dan bisa melewati berkas/folder tertentu.
|
| Pemakaian:
|   php deploy/buat-zip.php <folder-sumber> <berkas.zip> [--tanpa-akar] [pola-kecuali...]
|
| Contoh:
|   php deploy/buat-zip.php dist/build/induk dist/induk.zip \
|       "backup/*" "storage/logs/*.log"
*/

if ($argc < 3) {
    fwrite(STDERR, "Pemakaian: php buat-zip.php <folder-sumber> <berkas.zip> [--tanpa-akar] [pola-kecuali...]\n");
    exit(1);
}

$sumber    = rtrim(str_replace('\\', '/', $argv[1]), '/');
$tujuan    = $argv[2];
$sisa      = array_slice($argv, 3);
$tanpaAkar = in_array('--tanpa-akar', $sisa, true);
$kecuali   = array_values(array_filter($sisa, function ($p) {
    return $p !== '--tanpa-akar';
}));

if (!is_dir($sumber)) {
    fwrite(STDERR, "Folder sumber tidak ditemukan: {$sumber}\n");
    exit(1);
}

$akarArsip = $tanpaAkar ? '' : basename($sumber) . '/';

if (file_exists($tujuan)) {
    unlink($tujuan);
}

$zip = new ZipArchive();
if ($zip->open($tujuan, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Gagal membuat berkas zip: {$tujuan}\n");
    exit(1);
}

/** Apakah path relatif termasuk yang dikecualikan? */
function dikecualikan(string $relatif, array $pola): bool
{
    foreach ($pola as $p) {
        if (fnmatch($p, $relatif) || fnmatch($p . '/*', $relatif) || str_starts_with($relatif . '/', rtrim($p, '/') . '/')) {
            return true;
        }
    }

    return false;
}

$jumlah = 0;
$iterasi = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sumber, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterasi as $item) {
    $path    = str_replace('\\', '/', $item->getPathname());
    $relatif = ltrim(substr($path, strlen($sumber)), '/');

    if ($relatif === '' || dikecualikan($relatif, $kecuali)) {
        continue;
    }

    // Lewati symlink (mis. public/storage) supaya tidak menyalin isi ganda
    if (is_link($item->getPathname())) {
        continue;
    }

    $namaArsip = $akarArsip . $relatif;

    if ($item->isDir()) {
        $zip->addEmptyDir($namaArsip);
        continue;
    }

    $zip->addFile($path, $namaArsip);
    $jumlah++;
}

$zip->close();

printf(
    "%-22s %6.1f MB  (%d berkas)\n",
    basename($tujuan),
    filesize($tujuan) / 1048576,
    $jumlah
);
