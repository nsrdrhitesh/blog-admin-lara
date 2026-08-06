<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background:#0f172a; color:#e2e8f0;
               display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .card { text-align:center; }
        h1 { font-size: 1.75rem; margin-bottom:.5rem; }
        p { color:#94a3b8; }
        a { color:#60a5fa; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Blog CMS is running 🎉</h1>
        <p>Laravel {{ app()->version() }} on PHP {{ PHP_VERSION }}</p>
        <p><a href="/admin">Go to admin panel</a></p>
    </div>
</body>
</html>
