@extends('emails.layout', ['title' => 'Invoice for Order - FuelCab'])

@section('content')
    <h1>Tax Invoice / Receipt</h1>
    
    <p>Dear {{ $customerName }},</p>
    
    <p>Please find below the detailed receipt/invoice details for your completed order <strong><code>{{ $orderNumber }}</code></strong>.</p>
    
    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin: 20px 0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
            <thead>
                <tr style="background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 15px; color: #4a5568; font-weight: 700;">Description</th>
                    <th style="padding: 12px 15px; color: #4a5568; font-weight: 700; text-align: right;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #edf2f7;">
                    <td style="padding: 12px 15px; color: #2d3748;">
                        <strong>{{ $productName }}</strong><br>
                        <span style="font-size: 12px; color: #718096;">Qty: {{ number_format($quantity, 2) }} L @ ₹{{ number_format($unitPrice, 2) }}/L</span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; color: #2d3748; vertical-align: middle;">
                        ₹{{ number_format($subtotal, 2) }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #edf2f7;">
                    <td style="padding: 10px 15px; color: #4a5568;">Tax / GST (18%)</td>
                    <td style="padding: 10px 15px; text-align: right; color: #4a5568;">₹{{ number_format($tax, 2) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #edf2f7;">
                    <td style="padding: 10px 15px; color: #4a5568;">Logistics & Delivery Fee</td>
                    <td style="padding: 10px 15px; text-align: right; color: #4a5568;">₹{{ number_format($deliveryFee, 2) }}</td>
                </tr>
                <tr style="background-color: #f0fdf4;">
                    <td style="padding: 12px 15px; color: #155c32; font-weight: 700;">Total Paid</td>
                    <td style="padding: 12px 15px; text-align: right; color: #155c32; font-weight: 700; font-size: 16px;">
                        ₹{{ number_format($total, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <p style="font-size: 13px; color: #718096;">Payment was processed successfully via <strong>{{ strtoupper($paymentMethod) }}</strong>. A PDF copy of this tax invoice has also been generated and saved under your order dashboard profile.</p>
    
    <div class="button-container">
        <a href="https://fuelcab.com/order/{{ $orderId }}" class="button">View Online Invoice</a>
    </div>
@endsection
