<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ödeme Sayfasına Yönlendiriliyorsunuz...</title>
</head>
<body onload="document.forms['ziraat_form'].submit()">
    <div style="text-align: center; margin-top: 50px;">
        <h2>Lütfen bekleyin, ödeme sayfasına yönlendiriliyorsunuz...</h2>
        <form name="ziraat_form" action="{{ $paymentData['action'] }}" method="POST">
            @foreach($paymentData['inputs'] as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <noscript>
                <p>Tarayıcınız JavaScript desteklemiyor veya kapalı. Lütfen aşağıdaki butona tıklayarak devam edin.</p>
                <button type="submit">Devam Et</button>
            </noscript>
        </form>
    </div>
</body>
</html>

