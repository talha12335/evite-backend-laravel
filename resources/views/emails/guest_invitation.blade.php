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
                                You are invited to a studio event
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 20px 28px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#334155;font-size:15px;line-height:1.65;" class="px">
                            <p style="margin:0 0 18px 0;color:#0f172a;">
                                Hello! A host has created an Honest Art studio invitation for you. Below you will find the event details along with a visual preview of the invitation card. Please review the information and reach out to the host if you have any questions.
                            </p>
                            <p style="margin:0 0 12px 0;color:#475569;font-size:14px;">
                                This is a one-to-one transactional message sent on behalf of the event host. You are receiving it because your email address was entered as a guest. This is not a mailing list, newsletter, or promotional email.
                            </p>

                            @if($hasDetails ?? false)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 6px 0;">
                                <tr>
                                    <td style="height:1px;background:#e2e8f0;line-height:1px;font-size:1px;">&nbsp;</td>
                                </tr>
                            </table>
                            <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#475569;">
                                Event details
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
                                The event details are included in the invitation card image below. Please review the image for date, time, location, and RSVP information.
                            </p>
                            @endif

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 6px 0;">
                                <tr>
                                    <td style="height:1px;background:#e2e8f0;line-height:1px;font-size:1px;">&nbsp;</td>
                                </tr>
                            </table>
                            <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#475569;">
                                Invitation card preview
                            </p>
                            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.65;color:#475569;">
                                Your personalized invitation card is attached below. If the image does not display, please check your email settings and select &quot;Show images&quot; or &quot;Always display images from this sender&quot; in your email client.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
                                <tr>
                                    <td align="center" style="border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;background:#fafafa;">
                                        <img src="cid:invitation-preview" alt="Your Honest Art studio invitation card with event details including date, time, location, and RSVP information" width="520" class="invite-img" style="display:block;width:100%;max-width:520px;height:auto;border:0;">
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 6px 0;">
                                <tr>
                                    <td style="height:1px;background:#e2e8f0;line-height:1px;font-size:1px;">&nbsp;</td>
                                </tr>
                            </table>
                            <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#475569;">
                                Questions or RSVP
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.65;color:#475569;">
                                To confirm your attendance or ask about the event, please contact the host directly using the RSVP details listed above. You may also reply to this email if your mail client supports it. We look forward to seeing you at the studio!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;" class="px">
                            <p style="margin:0;font-size:11px;line-height:1.55;color:#64748b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                                © {{ date('Y') }} Honest Art · This is a transactional invitation, not a promotional message. You received this because a host entered your email address for their event. If you did not expect this message, you may safely disregard it.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
