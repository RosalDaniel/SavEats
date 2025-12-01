<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - SavEats</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #347928; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">SavEats</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0;">Verify Your Email Address</h2>
        
        <p>Hello {{ $user->name ?? ($user->fname ?? $user->business_name ?? $user->organization_name ?? 'User') }},</p>
        
        <p>Thank you for registering with SavEats! Please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('password-recovery.verify-email', ['token' => $token]) }}" 
               style="background: #347928; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                Verify Email
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 0.875rem;">
            Or copy and paste this link into your browser:<br>
            <a href="{{ route('password-recovery.verify-email', ['token' => $token]) }}" style="color: #347928; word-break: break-all;">
                {{ route('password-recovery.verify-email', ['token' => $token]) }}
            </a>
        </p>
        
        <p style="color: #ef4444; font-size: 0.875rem; margin-top: 30px;">
            <strong>Important:</strong> You must verify your email before you can log in. This link will expire in 7 days.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">
            This is an automated message from SavEats. Please do not reply to this email.
        </p>
    </div>
</body>
</html>

