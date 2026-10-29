<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>CV — password required</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f1f3f6; font: 15px/1.5 system-ui, sans-serif; color: #1f2937; }
        form { width: min(360px, 100% - 32px); padding: 28px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
        h1 { margin: 0 0 6px; font-size: 1.15rem; }
        p { margin: 0 0 16px; color: #6b7280; font-size: .9rem; }
        input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font: inherit; }
        button { margin-top: 12px; width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #1f4e79; color: #fff; font: 600 .95rem system-ui, sans-serif; cursor: pointer; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('cv.unlock') }}">
        @csrf
        <h1>This CV is password protected</h1>
        <p class="{{ $error ? 'error' : '' }}" role="{{ $error ? 'alert' : 'note' }}">{{ $error ?? 'Enter the password you were given to view it.' }}</p>
        <label for="cv-password" style="position:absolute;left:-9999px">Password</label>
        <input id="cv-password" type="password" name="password" required autofocus autocomplete="current-password">
        <button type="submit">View CV</button>
    </form>
</body>
</html>
