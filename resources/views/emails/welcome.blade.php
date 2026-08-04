@extends('emails.layout', ['title' => 'Welcome to FuelCab!'])

@section('content')
    <h1>Welcome to FuelCab, {{ $name }}!</h1>
    
    <p>Thank you for registering an account on FuelCab. We are excited to have you onboard!</p>
    
    @if($role === 'vendor')
        <p><strong>Your Vendor Application status is currently:</strong> <span class="badge badge-info">Under Review</span></p>
        <p>Our operations team will review your business credentials, GST registration, depot address, and licenses within the next 24 to 48 hours. Once verified, you will receive an approval email with login details to access your Vendor Portal.</p>
    @else
        <p>Your customer account is now fully active! You can now request quotes for bulk fuels, place orders, track deliveries, and manage your invoices directly from the storefront dashboard.</p>
        
        <div class="button-container">
            <a href="https://fuelcab.com/login" class="button">Access Storefront</a>
        </div>
    @endif
    
    <p>If you have any questions or require assistance during setup, please contact our helpline or reply directly to this email.</p>
    
    <p>Best regards,<br>The FuelCab Operations Team</p>
@endsection
