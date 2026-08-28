<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verify your email — {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#020617;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#020617;">
        <tr>
            <td align="center" style="padding:48px 16px;">

                <!-- Logo / App Name -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
                    <tr>
                        <td align="center" style="padding-bottom:28px;">
                            <span style="font-size:20px;font-weight:700;color:#E2E8F0;letter-spacing:-0.4px;">
                                {{ $appName }}
                            </span>
                        </td>
                    </tr>
                </table>

                <!-- Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#0B1220;border:1px solid #1F2937;border-radius:12px;">
                    <tr>
                        <td style="padding:40px;">

                            <!-- Heading -->
                            <h1 style="margin:0 0 10px 0;font-size:22px;font-weight:700;color:#E2E8F0;letter-spacing:-0.3px;">
                                Verify your email address
                            </h1>
                            <p style="margin:0 0 28px 0;font-size:14px;color:#94A3B8;line-height:1.7;">
                                Hi {{ $user->name }}, thanks for signing up for
                                <strong style="color:#E2E8F0;">{{ $appName }}</strong>.
                                Click the button below to confirm your email address and activate your account.
                            </p>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr><td style="height:1px;background-color:#1F2937;font-size:0;line-height:0;">&nbsp;</td></tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr>
                                    <td align="left" style="border-radius:8px;background-color:#1E45FC;">
                                        <a href="{{ $verificationUrl }}"
                                           target="_blank"
                                           style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry note -->
                            <p style="margin:0 0 24px 0;font-size:13px;color:#475569;line-height:1.6;">
                                This link expires in <strong style="color:#94A3B8;">60 minutes</strong>.
                                If it expires, you can request a new one from the login page.
                            </p>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                                <tr><td style="height:1px;background-color:#1F2937;font-size:0;line-height:0;">&nbsp;</td></tr>
                            </table>

                            <!-- Fallback URL -->
                            <p style="margin:0;font-size:12px;color:#475569;line-height:1.8;">
                                If the button above doesn't work, copy and paste this URL into your browser:<br>
                                <a href="{{ $verificationUrl }}"
                                   style="color:#1E45FC;word-break:break-all;text-decoration:none;">
                                    {{ $verificationUrl }}
                                </a>
                            </p>

                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
                    <tr>
                        <td align="center" style="padding-top:24px;">
                            <p style="margin:0;font-size:12px;color:#475569;line-height:1.6;">
                                &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.<br>
                                If you did not create an account, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
