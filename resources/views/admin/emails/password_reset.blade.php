<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#f59e0b,#f97316);padding:20px 24px;color:#ffffff;">
                            <h1 style="margin:0;font-size:20px;line-height:1.3;">Honest Art Admin Security</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px;">
                            <p style="margin:0 0 14px 0;font-size:15px;line-height:1.6;">
                                Hi {{ $details['name'] }},
                            </p>
                            <p style="margin:0 0 14px 0;font-size:15px;line-height:1.7;color:#334155;">
                                We received a request to reset your admin account password. Use the button below to continue.
                            </p>
                            <p style="margin:0 0 22px 0;font-size:14px;line-height:1.6;color:#64748b;">
                                This link expires in {{ $details['expires_in_minutes'] }} minutes and can only be used once.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px 0;">
                                <tr>
                                    <td align="center" style="border-radius:8px;background:#f59e0b;">
                                        <a href="{{ $details['reset_url'] }}" style="display:inline-block;padding:12px 20px;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px 0;font-size:13px;color:#475569;line-height:1.7;">
                                If the button does not work, copy and paste this URL into your browser:
                            </p>
                            <p style="margin:0 0 20px 0;word-break:break-all;font-size:12px;color:#0f172a;">
                                <a href="{{ $details['reset_url'] }}" style="color:#2563eb;text-decoration:underline;">{{ $details['reset_url'] }}</a>
                            </p>

                            <p style="margin:0 0 8px 0;font-size:13px;line-height:1.7;color:#475569;">
                                If you did not request this, you can safely ignore this email. Your password will remain unchanged.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#475569;">
                                Need help? Contact us at <a href="mailto:{{ $details['support_email'] }}" style="color:#2563eb;">{{ $details['support_email'] }}</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;color:#64748b;">
                                © {{ date('Y') }} Honest Art. This is an automated security email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
