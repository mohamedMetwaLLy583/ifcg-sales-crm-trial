<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>رمز التحقق - {{ $action }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 5px;
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .action-badge {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .note {
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }
        .footer {
            border-top: 1px solid #eee;
            margin-top: 30px;
            padding-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-badge">{{ $action }}</div>
        <h2>رمز التحقق</h2>
        <p>مرحباً،</p>
        <p>رمز التحقق الخاص بك لعملية <strong>{{ $action }}</strong> هو:</p>
        
        <div class="otp-code">{{ $otp }}</div>
        
        <p>الرجاء استخدام هذا الرمز لإتمام عملية {{ $action }}. هذا الرمز صالح لمدة 10 دقائق فقط.</p>
        
        <div class="note">
            <p><strong>ملاحظة:</strong> إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.</p>
        </div>
        
        <div class="footer">
            <p>هذه الرسالة مرسلة تلقائياً، لا تقم بالرد عليها.</p>
            <p>شكراً لك،<br>فريق اثاث للديكور</p>
        </div>
    </div>
</body>
</html>
