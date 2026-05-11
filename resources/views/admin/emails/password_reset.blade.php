<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Reset your password</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .px { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <!-- Preheader (hidden in many clients) -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        Reset your Honest Art admin password — link expires in {{ $details['expires_in_minutes'] }} minutes.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,0.08);border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 45%,#b45309 100%);padding:28px 32px;" class="px">
                            <p style="margin:0 0 6px 0;font-size:11px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.75);font-family:Georgia,'Times New Roman',serif;">
                                Honest Art
                            </p>
                            <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
                                Admin password reset
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="px" style="padding:32px 32px 28px 32px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
                            <p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;color:#0f172a;">
                                Hi {{ $details['name'] }},
                            </p>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.65;color:#334155;">
                                We received a request to reset the password for your <strong style="color:#0f172a;">admin</strong> account. Use the secure button below to choose a new password.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 20px 0;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;width:100%;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.55;color:#92400e;">
                                        <strong>Security note:</strong> this link expires in <strong>{{ $details['expires_in_minutes'] }} minutes</strong> and works only once. If you did not request this, you can ignore this message.
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0;">
                                <tr>
                                    <td align="left" style="border-radius:10px;background:#d97706;">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $details['reset_url'] }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="12%" strokecolor="#d97706" fillcolor="#d97706">
                                            <w:anchorlock/>
                                            <center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Reset my password</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-- -->
                                        <a href="{{ $details['reset_url'] }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 28px;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;line-height:1.2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
                                            Reset my password
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px 0;font-size:13px;line-height:1.6;color:#64748b;">
                                Button not working? Copy this link into your browser:
                            </p>
                            <p style="margin:0 0 24px 0;padding:12px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;word-break:break-all;font-size:12px;line-height:1.5;color:#0f172a;font-family:Consolas,'Courier New',monospace;">
                                <a href="{{ $details['reset_url'] }}" style="color:#0369a1;text-decoration:underline;">{{ $details['reset_url'] }}</a>
                            </p>

                            <p style="margin:0;font-size:13px;line-height:1.65;color:#64748b;">
                                Questions? Reply to this email or write to
                                <a href="mailto:{{ $details['support_email'] }}" style="color:#0369a1;font-weight:600;text-decoration:none;">{{ $details['support_email'] }}</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;" class="px">
                            <p style="margin:0;font-size:11px;line-height:1.5;color:#94a3b8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
                                © {{ date('Y') }} Honest Art · Automated security message · Do not share this link with anyone.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
