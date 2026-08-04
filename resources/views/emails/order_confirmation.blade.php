@extends('emails.layout', ['title' => 'Order Confirmed - FuelCab'])

@section('content')
    <h1>Order Confirmed!</h1>
    
    <p>Dear {{ $customerName }},</p>
    
    <p>Thank you for your order! We are preparing your delivery dispatch. Your order reference code is <strong><code>{{ $orderNumber }}</code></strong>.</p>
    
    <div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;">
        <h3 style="margin-top: 0; color: #2d3748; font-size: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Order Details</h3>
        
        <table style="width: 100%; font-size: 14px; color: #4a5568; line-height: 2;">
            <tr>
                <td style="font-weight: 600;">Product:</td>
                <td style="text-align: right;">{{ $productName }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Quantity:</td>
                <td style="text-align: right;">{{ number_format($quantity, 2) }} Litres</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Status:</td>
                <td style="text-align: right;"><span class="badge badge-success">{{ $status }}</span></td>
            </tr>
            <tr style="border-top: 1px solid #edf2f7;">
                <td style="font-weight: 700; color: #2d3748; padding-top: 8px;">Total Paid:</td>
                <td style="text-align: right; font-weight: 700; color: #155c32; padding-top: 8px;">₹{{ number_format($total, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div style="margin: 20px 0; font-size: 14px; color: #4a5568;">
        <p><strong>Delivery Address:</strong><br>
        {{ $deliveryAddress }}</p>
    </div>
    
    <div class="button-container">
        <a href="https://fuelcab.com/order/{{ $orderId }}" class="button">Track Order Status</a>
    </div>
    
    <p>If you need to make changes or have questions about delivery times, please reply to this email or call customer support.</p>
@endsection
