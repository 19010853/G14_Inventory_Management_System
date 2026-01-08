<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h2 style="color: #333; margin-top: 0;">Hi {{ $name }},</h2>
        
        <p>Your account has been created successfully.</p>
        
        <p>Your temporary password is: <strong style="font-size: 16px; color: #007bff; background-color: #e7f3ff; padding: 8px 12px; border-radius: 4px; display: inline-block; font-family: monospace;">{{ $password }}</strong></p>
        
        <p>Please change your password after logging in for security.</p>
        
        <p style="margin-top: 30px;">Best regards,<br>Group 14</p>
    </div>
</body>
</html>

