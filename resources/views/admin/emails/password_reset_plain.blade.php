HONEST ART — ADMIN PASSWORD RESET
=================================

Hi {{ $details['name'] }},

We received a request to reset your admin account password.

Open this link (valid {{ $details['expires_in_minutes'] }} minutes, one-time use):
{{ $details['reset_url'] }}

If you did not request this, ignore this email. Your password will stay the same.

Support: {{ $details['support_email'] }}

© {{ date('Y') }} Honest Art
