#!/usr/bin/env bash
#
# build-paket.sh — membuat paket upload cPanel dari proyek ini.
#
# Hasil (di folder dist/):
#   induk.zip        -> seluruh aplikasi. Upload ke /home/USERNAME/ lalu ekstrak.
#                       Document Root subdomain: /home/USERNAME/induk/public
#   public_html.zip  -> isi folder public + index.php siap pakai untuk cara di
#                       mana document root terkunci di public_html.
#
# Jalankan:  bash deploy/build-paket.sh
set -euo pipefail

AKAR="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$AKAR/dist"
BUILD="$DIST/build"
PHPBIN="$(command -v php || echo /c/laragon/bin/php/php-8.3.36-Win32-vs16-x64/php.exe)"

echo "== Membersihkan cache Laravel =="
if [ -x "$PHPBIN" ]; then
  (cd "$AKAR" && "$PHPBIN" artisan route:clear config:clear view:clear >/dev/null 2>&1 || true)
fi

echo "== Menyiapkan folder build =="
rm -rf "$DIST"
mkdir -p "$BUILD/induk" "$BUILD/public_html"

echo "== Menyalin proyek (tanpa .git, .env, tests, cache) =="
(cd "$AKAR" && tar -cf - \
  --exclude='./.git' \
  --exclude='./node_modules' \
  --exclude='./tests' \
  --exclude='./dist' \
  --exclude='./deploy' \
  --exclude='./.env' \
  --exclude='./.env.testing' \
  --exclude='./.phpunit.result.cache' \
  --exclude='./storage/logs/*.log' \
  --exclude='./storage/framework/cache/data/*' \
  --exclude='./storage/framework/sessions/*' \
  --exclude='./storage/framework/views/*' \
  --exclude='./public/storage' \
  . | tar -xf - -C "$BUILD/induk")

echo "== Menyiapkan isi public_html =="
cp -a "$AKAR/public/." "$BUILD/public_html/" 2>/dev/null || true
rm -rf "$BUILD/public_html/storage"                     # symlink Windows tidak dibawa
cp "$AKAR/deploy/cpanel/index.php-public-html" "$BUILD/public_html/index.php"
cp "$AKAR/deploy/cpanel/htaccess-minimal.txt" "$BUILD/public_html/.htaccess"

echo "== Membuat arsip zip =="
"$PHPBIN" "$AKAR/deploy/buat-zip.php" "$BUILD/induk" "$DIST/induk.zip" \
  "storage/logs/*.log" "storage/framework/cache/data/*" \
  "storage/framework/sessions/*" "storage/framework/views/*"

"$PHPBIN" "$AKAR/deploy/buat-zip.php" "$BUILD/public_html" "$DIST/public_html.zip" --tanpa-akar

echo ""
echo "Selesai. Paket siap upload:"
ls -lh "$DIST"/induk.zip "$DIST"/public_html.zip
echo ""
echo "Cara A (disarankan): upload induk.zip  -> /home/USERNAME/ , Document Root subdomain = /home/USERNAME/induk/public"
echo "Cara B (docroot terkunci) : upload induk.zip -> /home/USERNAME/ dan isi public_html.zip -> public_html/"
echo "Kedua cara membutuhkan: .env server, php artisan storage:link (atau symlink manual), impor database."