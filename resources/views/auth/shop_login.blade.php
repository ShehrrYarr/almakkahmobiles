<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $shop->name }} — Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            width: 100%;
            max-width: 360px;
            padding: 32px 28px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        h1 { font-size: 1.3rem; margin: 0 0 4px; text-align: center; }
        .sub { text-align: center; color: #777; font-size: .85rem; margin-bottom: 22px; }
        label { font-size: .85rem; font-weight: 600; display: block; margin-bottom: 4px; }
        input[type=email], input[type=password] {
            width: 100%; padding: 10px 12px; margin-bottom: 14px;
            border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: .95rem;
        }
        button {
            width: 100%; padding: 11px; background: #2563eb; color: #fff; border: none;
            border-radius: 6px; font-weight: 600; font-size: .95rem; cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .error { background: #fde8e8; color: #b91c1c; padding: 8px 12px; border-radius: 6px; font-size: .85rem; margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $shop->name }}</h1>
        <div class="sub">Mobile Section Login</div>

        @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('shop.login.submit', $shop->slug) }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
        </form>
    </div>
</body>
</html>
