<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Confirm Your Subscription') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1f2937;
            font-size: 24px;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 0 0 16px;
            color: #4b5563;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #6b7280;
        }
        .link-fallback {
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $blogName }}</h1>
        </div>

        <div class="content">
            <p>{{ __('Hello') }}{{ $subscriber->name ? ' ' . $subscriber->name : '' }},</p>

            <p>{{ __('Thank you for subscribing to our newsletter! Please click the button below to confirm your subscription.') }}</p>

            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="button">{{ __('Confirm Subscription') }}</a>
            </div>

            <p>{{ __('If you did not subscribe to our newsletter, you can safely ignore this email.') }}</p>

            <p class="link-fallback">
                {{ __('If the button above does not work, copy and paste this link into your browser:') }}<br>
                <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p>{{ __('This link will expire in 24 hours.') }}</p>
            <p>&copy; {{ date('Y') }} {{ $blogName }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
