<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="now">
    <title>Ödeme Yönlendiriliyor...</title>
</head>
<body onload="document.pay_form.submit()">

    <form name="pay_form" method="post" action="{{ $paymentData['action'] }}">
        @foreach($paymentData['inputs'] as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

</body>
</html>