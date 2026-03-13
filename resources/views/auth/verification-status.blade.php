<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Doğrulama</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f8fafc, #e2e8f0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(24px);
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.1);
            padding: 36px;
            text-align: center;
        }

        .card.success {
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .card.info {
            border: 1px solid rgba(129, 140, 248, 0.2);
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card.success .icon-wrapper {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.15));
            color: #16a34a;
        }

        .card.info .icon-wrapper {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(129, 140, 248, 0.15));
            color: #4f46e5;
        }

        h1 {
            font-size: 24px;
            margin: 0 0 12px;
            font-weight: 700;
        }

        p {
            margin: 0 0 20px;
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
        }

        a.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            color: #ffffff;
            box-shadow: 0 20px 35px rgba(99, 102, 241, 0.25);
        }

        a.button svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>

<body>
    <div class="card {{ $status === 'success' ? 'success' : 'info' }}">
        <div class="icon-wrapper">
            @if ($status === 'success')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11.25l2.25 2.25L15 9"></path>
                    <path d="M12 21a9 9 0 1 0-9-9 9 9 0 0 0 9 9zm0-15a6 6 0 1 1-6 6 6.006 6.006 0 0 1 6-6z">
                    </path>
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4"></path>
                    <path d="M12 16h.01"></path>
                    <path d="M21 12a9 9 0 1 0-18 0 9 9 0 0 0 18 0zm-8.25 0V8l-2.25 2.25">
                    </path>
                </svg>
            @endif
        </div>
        <h1>
            @if ($status === 'success')
                E-posta Doğrulandı
            @else
                Zaten Doğrulanmış
            @endif
        </h1>
        <p>
            @if ($status === 'success')
                Tebrikler! Hesabınız başarıyla doğrulandı. Artık tüm özellikleri güvenle kullanabilirsiniz.
            @else
                Bu e-posta adresi zaten doğrulanmış. Doğrudan mağazamıza dönebilirsiniz.
            @endif
        </p>
        <a href="{{ config('app.url') }}" class="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="M12 5l7 7-7 7"></path>
            </svg>
            Mağazaya Dön
        </a>
    </div>
</body>

</html>
