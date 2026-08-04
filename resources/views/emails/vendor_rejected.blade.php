@extends('emails.layout', ['title' => 'Vendor Application Status Update'])

@section('content')
    <h1>Application Status: Rejected</h1>
    
    <p>Dear {{ $contactPerson }},</p>
    
    <p>Thank you for your interest in joining the FuelCab supplier network. We have reviewed the application submitted for <strong>{{ $companyName }}</strong>.</p>
    
    <p>Regrettably, we cannot approve your vendor application at this time due to the following reason(s):</p>
    
    <div style="background-color: #fffaf0; border-left: 4px solid #dd6b20; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 14px; color: #7b341e;">
        <strong>Reason:</strong> {{ $reason }}
    </div>
    
    <p>Common rejection reasons include incomplete DLT registration details, illegible GST/PAN documentation, or invalid fuel storage licenses.</p>
    
    <p>If you believe this is an error or would like to re-submit corrected business documents, please contact our vendor relations department at <a href="mailto:vendors@fuelcab.com">vendors@fuelcab.com</a>.</p>
    
    <p>Sincerely,<br>The FuelCab Compliance Board</p>
@endsection
