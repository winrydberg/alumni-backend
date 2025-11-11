<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #002147;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #002147;
            margin-top: 0;
            font-size: 22px;
        }
        .warning-box {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box strong {
            color: #856404;
        }
        .button {
            display: inline-block;
            background-color: #002147;
            color: white !important;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        .button:hover {
            background-color: #003366;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            font-size: 12px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
        .security-notice {
            margin-top: 20px;
            padding: 15px;
            background-color: #e7f3ff;
            border-radius: 4px;
            border-left: 4px solid #0066cc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/images/img.png')) }}"
                 alt="University of Ghana Logo"
                 class="logo"
                 style="display: inline-block; max-width: 80px; height: auto;">
            <h1>ALUMNI ASSOCIATION</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $user->first_name }},</h2>

            <p>We received a request to reset the password for your University of Ghana Alumni account.</p>

            <div class="info-box">
                <p><strong>Account Details:</strong></p>
                <p>📧 <strong>Email:</strong> {{ $user->email }}</p>
                <p>👤 <strong>Name:</strong> {{ $user->full_name }}</p>
            </div>

            <p>Click the button below to reset your password. This link will expire in <strong>1 hour</strong> for security reasons.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.frontend_url') }}/reset-password?token={{ $token }}&email={{ urlencode($user->email) }}" class="button">
                    Reset Password
                </a>
            </div>

            <p style="margin-top: 20px; font-size: 14px; color: #666;">
                Or copy and paste this link into your browser:<br>
                <span style="word-break: break-all; color: #0066cc;">
                    {{ config('app.frontend_url') }}/reset-password?token={{ $token }}&email={{ urlencode($user->email) }}
                </span>
            </p>

            <div class="warning-box">
                <strong>⚠️ Didn't request this?</strong><br>
                If you didn't request a password reset, please ignore this email. Your password will remain unchanged and secure.
            </div>

            <div class="security-notice">
                <strong>🛡️ Security Tips:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Never share your password with anyone</li>
                    <li>Use a strong, unique password</li>
                    <li>This link expires in 1 hour</li>
                    <li>If you suspect unauthorized access, contact support immediately</li>
                </ul>
            </div>

            <p style="margin-top: 30px;">Best regards,<br>
            <strong>University of Ghana Alumni Association</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated security email. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} University of Ghana. All rights reserved.</p>
            <p>P.O. Box LG 25, Legon, Accra, Ghana</p>
        </div>
    </div>
</body>
</html>
