@extends('emails.layout', ['title' => 'Vendor Application Approved!'])

@section('content')
    <h1>Application Approved!</h1>
    
    <p>Dear {{ $contactPerson }},</p>
    
    <p>We are pleased to inform you that your vendor application for <strong>{{ $companyName }}</strong> has been fully verified and approved by the FuelCab operations board.</p>
    
    <p>Your unique Vendor Code is: <strong><code>{{ $vendorCode }}</code></strong></p>
    
    <p>You can now log in to the FuelCab Vendor Portal using your registered email address to set up your product listings, adjust unit prices, manage inventory levels, and respond to bulk quote inquiries.</p>
    
    <div class="button-container">
        <a href="https://fuelcab.com/vendor/login" class="button">Access Vendor Portal</a>
    </div>
    
    <p>Please review our vendor operational guidelines and SLA expectations to ensure high-quality fulfillment times.</p>
    
    <p>Congratulations, and welcome to our supplier network!<br>The FuelCab Operations Team</p>
@endsection
