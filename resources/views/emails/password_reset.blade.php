@extends('emails.layout', ['title' => 'Password Reset Verification Code'])

@section('content')
    <h1>Password Reset Request</h1>
    
    <p>Dear {{ $name }},</p>
    
    <p>We received a request to reset the password for your FuelCab account. To complete the request, please enter the following 6-digit verification code in the password reset window:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <span style="display: inline-block; font-size: 32px; font-weight: 800; letter-spacing: 6px; padding: 12px 30px; background-color: #f0f7f4; border: 2px dashed #155c32; border-radius: 8px; color: #155c32;">
            {{ $otp }}
        </span>
    </div>
    
    <p style="color: #718096; font-size: 14px;">This code is valid for <strong>{{ $expiry }} minutes</strong> and can only be used once. If the code expires, you will need to submit a new request.</p>
    
    <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">
    
    <p style="font-size: 13px; color: #a0aec0;">If you did not request this password reset, please ignore this email or contact support if you suspect unauthorized access to your account. Your password will remain unchanged.</p>
@endsection
