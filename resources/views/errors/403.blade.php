<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 32px;
            border-radius: 12px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        h1 {
            font-size: 48px;
            margin: 0 0 12px;
            color: #dc2626;
        }

        p {
            color: #4b5563;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>403</h1>
        <h2>Akses Ditolak</h2>
        <p>
            Maaf, kamu tidak memiliki izin untuk mengakses halaman ini.
        </p>
        <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
    </main>
</body>
</html>