@extends('emails.layout', ['title' => 'Order Cancelled - FuelCab'])

@section('content')
    <h1>Order Cancelled</h1>
    
    <p>Dear {{ $customerName }},</p>
    
    <p>We are writing to confirm that order <strong><code>{{ $orderNumber }}</code></strong> has been cancelled.</p>
    
    <div style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 14px; color: #742a2a;">
        <strong>Cancellation Details:</strong><br>
        Your order for {{ number_format($quantity, 2) }} Litres of {{ $productName }} has been cancelled.
    </div>
    
    <p><strong>Refund Status:</strong> If you had already paid for this order via wallet or gateway, the transaction refund will be credited back to your original payment source or FuelCab wallet within 3-5 business days.</p>
    
    <p>If you did not initiate this request or have questions regarding the cancellation reasons, please contact our helpline immediately.</p>
    
    <p>We apologize for any inconvenience caused.<br>Sincerely,<br>The FuelCab Support Team</p>
@endsection
