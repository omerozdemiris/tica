<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Sayfasına Yönlendiriliyorsunuz...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f6f7ec;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h2 {
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 2rem auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body onload="document.forms['ziraat_form'].submit()">
    <div class="container">
        <h2>Lütfen bekleyin, ödeme sayfasına yönlendiriliyorsunuz...</h2>
        <div class="spinner"></div>
        <form name="ziraat_form" action="{{ $paymentData['action'] }}" method="POST">
            @foreach($paymentData['inputs'] as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <noscript>
                <p>Tarayıcınız JavaScript desteklemiyor veya kapalı. Lütfen aşağıdaki butona tıklayarak devam edin.</p>
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-size: 1rem; margin-top: 1rem;">Devam Et</button>
            </noscript>
        </form>
    </div>
</body>
</html>
