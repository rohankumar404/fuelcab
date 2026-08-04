@extends('emails.layout', ['title' => 'Delivery Completed - FuelCab'])

@section('content')
    <h1>Delivery Completed!</h1>
    
    <p>Dear {{ $customerName }},</p>
    
    <p>We are pleased to inform you that your fuel delivery for order <strong><code>{{ $orderNumber }}</code></strong> has been successfully completed and discharged at your operational depot location.</p>
    
    <div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7; font-size: 14px; color: #4a5568;">
        <strong>Delivery Summary:</strong><br>
        Product: {{ $productName }}<br>
        Discharged Volume: {{ number_format($quantity, 2) }} Litres<br>
        Delivery Agent / Vehicle: {{ $driverName }} ({{ $licensePlate }})<br>
        Completed At: {{ $completedAt }}
    </div>
    
    <p>The safety certificate and discharge slip have been uploaded to your dashboard. Please log in to view the documents or download the tax receipt.</p>
    
    <div class="button-container">
        <a href="https://fuelcab.com/order/{{ $orderId }}" class="button">View Order Documents</a>
    </div>
    
    <p>Thank you for choosing FuelCab. We look forward to fueling your operations again soon!</p>
    
    <p>Best regards,<br>The FuelCab Operations Team</p>
@endsection
