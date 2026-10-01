<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sanctum Auth')</title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; margin: 0; }
        .card { max-width: 420px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        h1 { font-size: 20px; margin-top: 0; }
        label { display: block; margin-top: 16px; font-size: 14px; }
        input[type=text], input[type=email], input[type=password] { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 24px; width: 100%; padding: 10px; background: #2563eb; color: #fff; border: 0; border-radius: 4px; font-size: 15px; cursor: pointer; }
        .error { color: #dc2626; font-size: 13px; margin-top: 4px; }
        .sub { margin-top: 16px; font-size: 14px; text-align: center; }
        pre { background: #111827; color: #d1fae5; padding: 12px; border-radius: 4px; overflow-x: auto; font-size: 13px; }
        .logout { background: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>