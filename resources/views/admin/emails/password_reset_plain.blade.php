--------------------------------------------------------------------------------
  HONEST ART — ADMIN PASSWORD RESET
--------------------------------------------------------------------------------

Hello {{ $details['name'] }},

We received a request to reset the password for your admin account.

RESET LINK (one-time use, expires in {{ $details['expires_in_minutes'] }} minutes):
{{ $details['reset_url'] }}

Security reminders:
• Use this link only if you requested a password reset.
• If you did not request this, ignore this email — your password stays unchanged.
• Never share this link with anyone.

Support:
{{ $details['support_email'] }}

--------------------------------------------------------------------------------
© {{ date('Y') }} Honest Art · Automated message · Do not reply with passwords
--------------------------------------------------------------------------------
