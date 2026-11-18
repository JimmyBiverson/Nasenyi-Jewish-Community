<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Donation confirmation</title>
</head>
<body>
    <h1>Thank you for supporting Nasenyi</h1>
    <p>Dear {{ $donation->donor_name }},</p>
    <p>We received your donation to the Nasenyi Abayudaya Jewish Community.</p>
    <p>Amount {{ $donation->currency }} {{ $donation->amount() }}</p>
    <p>Reference {{ $donation->reference }}</p>
    <p>Nasenyi Abayudaya Jewish Community</p>
</body>
</html>
