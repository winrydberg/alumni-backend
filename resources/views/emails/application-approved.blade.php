<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Approved</title>
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
        .success-badge {
            background-color: #28a745;
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
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
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
        <h2>Congratulations, {{ $user->first_name }}!</h2>

        <div class="success-badge">✓ APPLICATION APPROVED</div>

        <p>We are pleased to inform you that your alumni membership application has been approved.</p>

        <p><strong>Application Details:</strong></p>
        <ul>
            <li><strong>Name:</strong> {{ $user->title }} {{ $user->first_name }} {{ $user->last_name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Approval Date:</strong> {{ $user->approved_at ? $user->approved_at->format('F d, Y') : now()->format('F d, Y') }}</li>
        </ul>

        @if($generatedPassword)
        <div style="background-color: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #856404;">🔑 Your Login Credentials</h3>
            <p style="margin: 10px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 10px 0;"><strong>Password:</strong> <span style="font-size: 18px; font-weight: bold; color: #003366; background-color: #fff; padding: 5px 10px; border: 2px solid #003366; border-radius: 3px;">{{ $generatedPassword }}</span></p>
            <p style="margin-top: 15px; color: #856404; font-size: 14px;"><strong>⚠️ Important:</strong> Please change this password after your first login for security purposes.</p>
        </div>
        @endif

        <p>You now have full access to the alumni portal and all its benefits, including:</p>
        <ul>
            <li>Access to the alumni directory</li>
            <li>Networking opportunities with fellow alumni</li>
            <li>Invitations to alumni events and programs</li>
            <li>Career development resources</li>
            <li>Alumni newsletter subscriptions</li>
        </ul>

        <p>You can now log in to your account and explore all the features available to you.</p>

        <a href="{{ config('app.frontend_url') }}/login" class="button">Log In to Your Account</a>

        <p style="margin-top: 30px;">Welcome to the University of Ghana Alumni community!</p>

        <p>Best regards,<br>
        <strong>University of Ghana Alumni Association</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} University of Ghana Alumni Association. All rights reserved.</p>
    </div>
</body>
</html>
