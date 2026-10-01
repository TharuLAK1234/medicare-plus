<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | MediCare Plus</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center;
               justify-content: center; min-height: 100vh; margin: 0; background: #f1f5f9; }
        .box { text-align: center; padding: 48px; background: #fff;
               border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        h1 { font-size: 5rem; margin: 0; color: #0A6E82; }
        h2 { color: #1e293b; margin: 8px 0 16px; }
        p  { color: #64748b; }
        a  { color: #0A6E82; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="box">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for does not exist or has been moved.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>">← Back to Home</a>
    </div>
</body>
</html>
