<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
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
        .warning-badge {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            margin: 20px 0;
        }
        .reason-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
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
        <h2>Dear {{ $user->first_name }} {{ $user->last_name }},</h2>

        <div class="warning-badge">APPLICATION STATUS UPDATE</div>

        <p>Thank you for your interest in joining the University of Ghana Alumni Association.</p>

        <p>After careful review, we regret to inform you that we are unable to approve your alumni membership application at this time.</p>

        @if($reason)
        <div class="reason-box">
            <strong>Reason:</strong>
            <p>{{ $reason }}</p>
        </div>
        @endif

        <p><strong>What can you do next?</strong></p>
        <ul>
            <li>Review the information you submitted and ensure all details are accurate</li>
            <li>If you believe this decision was made in error, please contact us</li>
            <li>You may reapply after addressing the issues mentioned above</li>
        </ul>

        <p><strong>Contact Information:</strong></p>
        <p>If you have any questions or need clarification, please don't hesitate to reach out to us at:</p>
        <ul>
            <li>Email: alumni@ug.edu.gh</li>
            <li>Phone: +233 XXX XXX XXX</li>
        </ul>

        <p>We appreciate your understanding and interest in the University of Ghana Alumni community.</p>

        <p>Best regards,<br>
        <strong>University of Ghana Alumni Association</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} University of Ghana Alumni Association. All rights reserved.</p>
    </div>
</body>
</html>

