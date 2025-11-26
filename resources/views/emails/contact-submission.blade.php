<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('New Contact Message') }}</title>
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
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 {
            color: #1f2937;
            font-size: 24px;
            margin: 0;
        }
        .badge {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        .field {
            margin-bottom: 20px;
        }
        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .field-value {
            font-size: 16px;
            color: #1f2937;
        }
        .message-content {
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .metadata {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }
        .metadata-item {
            margin-bottom: 8px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('New Contact Message') }}</h1>
            <span class="badge">{{ config('app.name') }}</span>
        </div>

        <div class="field">
            <div class="field-label">{{ __('From') }}</div>
            <div class="field-value">{{ $contactMessage->name }} &lt;{{ $contactMessage->email }}&gt;</div>
        </div>

        <div class="field">
            <div class="field-label">{{ __('Subject') }}</div>
            <div class="field-value">{{ $contactMessage->subject }}</div>
        </div>

        <div class="field">
            <div class="field-label">{{ __('Message') }}</div>
            <div class="message-content">{{ $contactMessage->message }}</div>
        </div>

        <div class="metadata">
            <div class="metadata-item">
                <strong>{{ __('Received') }}:</strong> {{ $contactMessage->created_at->format('F j, Y \a\t g:i A') }}
            </div>
            <div class="metadata-item">
                <strong>{{ __('IP Address') }}:</strong> {{ $contactMessage->ip_address ?? __('Unknown') }}
            </div>
        </div>

        <div class="footer">
            <p>{{ __('You can reply directly to this email to respond to the sender.') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
