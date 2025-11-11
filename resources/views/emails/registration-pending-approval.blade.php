<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            border-radius: 4px;
        }
        .highlight strong {
            color: #856404;
        }
        .details-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .details-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .details-box li {
            margin: 8px 0;
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
        .contact-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #e7f3ff;
            border-radius: 4px;
            border-left: 4px solid #0066cc;
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
            <h2>Hello {{ $user->first_name }} {{ $user->last_name }},</h2>

            <p>Thank you for registering with the University of Ghana Alumni Portal!</p>

            <div class="highlight">
                <strong>✓ Your registration has been received successfully and is currently pending approval from the university.</strong>
            </div>

            <div class="details-box">
                <p><strong>Your Registration Details:</strong></p>
                <ul>
                    <li><strong>Name:</strong> {{ $user->title }} {{ $user->first_name }} {{ $user->last_name }}</li>
                    <li><strong>Email:</strong> {{ $user->email }}</li>
                    <li><strong>Phone:</strong> {{ $user->phone_number }}</li>
                    <li><strong>Date of Birth:</strong> {{ date('F d, Y', strtotime($user->dob)) }}</li>
                </ul>
            </div>

            <p>Our team will review your application and you will receive an email notification once your account has been approved or if we need any additional information.</p>

            <p><strong>Processing Time:</strong> This process typically takes 2-3 business days.</p>

            <div class="contact-info">
                <p><strong>Need Help?</strong></p>
                <p>If you have any questions or concerns, please contact us at <strong>alumni@ug.edu.gh</strong></p>
            </div>

            <p style="margin-top: 30px;">Best regards,<br>
            <strong>University of Ghana Alumni Association</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} University of Ghana. All rights reserved.</p>
            <p>P.O. Box LG 25, Legon, Accra, Ghana</p>
        </div>
    </div>
</body>
</html>
