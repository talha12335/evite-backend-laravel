<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <meta name="x-apple-disable-message-reformatting">
    <title>Honest Art studio invitation</title>
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
        :root { color-scheme: light; supported-color-schemes: light; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; max-width: 100% !important; }
            .px { padding-left: 20px !important; padding-right: 20px !important; }
            .py-shell { padding: 18px 12px !important; }
            .hero-title { font-size: 19px !important; }
            .detail-cell { display: block !important; width: 100% !important; padding: 6px 0 !important; }
            .invite-img { width: 100% !important; max-width: 100% !important; height: auto !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#f1f5f9;opacity:0;width:0;height:0;">
        {{ $preheader }}
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:32px 16px;" class="py-shell">
        <tr>
            <td align="center">
                <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="padding:22px 28px 18px 28px;border-bottom:1px solid #f1f5f9;" class="px">
                            <p style="margin:0 0 4px 0;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#64748b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                                Honest Art
                            </p>
                            <h1 class="hero-title" style="margin:0;font-size:21px;line-height:1.35;font-weight:700;color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
                                Studio invitation
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 20px 28px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#334155;font-size:15px;line-height:1.65;" class="px">
                            <p style="margin:0 0 18px 0;color:#0f172a;">
                                This email was sent because a host saved your address to deliver their <strong style="font-weight:600;">Honest Art studio invitation</strong>. It is a one-to-one transactional message, not a mailing list or promotion.
                            </p>

                            @if($hasDetails ?? false)
                            <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#475569;">
                                Event details (text)
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px 0;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                @foreach($detailRows as $row)
                                <tr style="background:#fafafa;">
                                    <td class="detail-cell" valign="top" style="padding:10px 14px;width:34%;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:600;color:#64748b;">
                                        {{ $row['label'] }}
                                    </td>
                                    <td class="detail-cell" valign="top" style="padding:10px 14px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#0f172a;">
                                        {{ $row['value'] }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @else
                            <p style="margin:0 0 22px 0;padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;color:#475569;">
                                Detailed fields for this invite were not stored as text. Please refer to the invitation image below for specifics.
                            </p>
                            @endif

                            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.65;color:#475569;">
                                Below is a <strong style="color:#0f172a;font-weight:600;">visual preview</strong> of the invitation card (PNG/JPEG). Your inbox may hide images until you choose &quot;Show images&quot; for this sender.
                            </p>

                            @if(!empty($imageUrl))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
                                <tr>
                                    <td align="center" style="border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;background:#fafafa;">
                                        <img src="{{ $imageUrl }}" alt="Honest Art invitation preview image" width="520" class="invite-img" style="display:block;width:100%;max-width:520px;height:auto;border:0;">
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                                For questions about date, time, or RSVP, reply to this email if your mail client supports it, or contact the host using the RSVP details above when listed.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;" class="px">
                            <p style="margin:0;font-size:11px;line-height:1.55;color:#64748b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                                © {{ date('Y') }} Honest Art · Transactional invitation · If you did not expect this message, you may disregard it.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
