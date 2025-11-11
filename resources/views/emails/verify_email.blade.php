<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-badge {
            background-color: #17a2b8;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background-color: #f1f1f1;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #003366;
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button-container {
            text-align: center;
            margin: 25px 0;
        }
        .link-box {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            font-size: 14px;
            color: #495057;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $message->embed(public_path('assets/images/img.png')) }}"
             alt="University of Ghana Logo"
             class="logo"
             style="display: inline-block; max-width: 80px; height: auto;">
        <h1>ALUMNI ASSOCIATION</h1>
    </div>

    <div class="content">
        <h2>Dear {{ $user->first_name }},</h2>

        <div class="info-badge">✓ VERIFY YOUR EMAIL ADDRESS</div>

        <p>Thank you for registering on the <strong>UG Alumni Platform</strong>. We're excited to have you join our community of distinguished alumni.</p>

        <p>To complete your registration and access all platform features, please verify your email address by clicking the button below:</p>

        <div class="button-container">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </div>

        <p><strong>Important:</strong> This verification link will expire in 24 hours for security purposes.</p>

        <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
        <div class="link-box">{{ $verificationUrl }}</div>

        <p>If you did not create an account on the UG Alumni Platform, please disregard this email. No further action is required.</p>

        <p>Best regards,<br>
        <strong>UG Alumni Team</strong><br>
        University of Ghana</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>For assistance, contact us at alumni@ug.edu.gh</p>
        <p>&copy; {{ date('Y') }} University of Ghana Alumni Association. All rights reserved.</p>
    </div>
</body>
</html>
