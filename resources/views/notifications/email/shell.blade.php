<!DOCTYPE html>
<html lang="{{ $lang ?? 'cs' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #1f2937;
            color: #ffffff;
            padding: 24px 32px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        .email-body {
            padding: 32px;
        }
        .email-footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .email-footer p {
            margin: 4px 0;
        }
        @media (max-width: 480px) {
            .email-wrapper {
                border-radius: 0;
            }
            .email-header,
            .email-body,
            .email-footer {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>
</head>
<body style="margin:0;padding:20px;background-color:#f3f4f6;">
    <div class="email-wrapper">
        @if (! empty($subject))
        <div class="email-header">
            <h1>{{ $subject }}</h1>
        </div>
        @endif
        <div class="email-body">
            {!! $body !!}
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} {{ $businessName ?? config('app.name') }}</p>
            @if (! empty($businessAddress))
            <p>{{ $businessAddress }}</p>
            @endif
        </div>
    </div>
</body>
</html>
