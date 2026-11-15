<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f8fafc; color:#1f2937; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .box { text-align:center; padding:2rem; }
        h1 { font-size:4rem; margin:0; color:#dc2626; }
        p { color:#4b5563; margin:.5rem 0 1.5rem; }
        a { display:inline-block; background:#2563eb; color:#fff; text-decoration:none; padding:.6rem 1.2rem; border-radius:.5rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>403</h1>
        <p>Anda tidak memiliki akses ke halaman ini.</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </div>
</body>
</html>
