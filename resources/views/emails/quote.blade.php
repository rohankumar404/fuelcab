@extends('emails.layout', ['title' => 'Quotation Update - FuelCab'])

@section('content')
    @if($type === 'request')
        <h1>New B2B Lead / Quote Request</h1>
        <p>Dear supplier,</p>
        <p>A customer has submitted a new bulk fuel quote request matching your listing catalog. Below are the details:</p>
        
        <div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;">
            <table style="width: 100%; font-size: 14px; color: #4a5568; line-height: 2;">
                <tr>
                    <td style="font-weight: 600;">Product:</td>
                    <td style="text-align: right;">{{ $productName }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Quantity Required:</td>
                    <td style="text-align: right;">{{ number_format($quantity, 2) }} units</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Target Delivery:</td>
                    <td style="text-align: right;">{{ $deliveryDate }}</td>
                </tr>
            </table>
        </div>
        
        <p>Log in to your vendor portal dashboard to submit your volume discount pricing and dispatch timeline terms.</p>
        
        <div class="button-container">
            <a href="https://fuelcab.com/vendor/login" class="button">Submit Quotation</a>
        </div>
    @else
        <h1>Quotation Submitted for Your Request</h1>
        <p>Dear customer,</p>
        <p>A supplier has responded to your bulk inquiry with tailored B2B volume pricing. Below are the quotation details:</p>
        
        <div style="background-color: #f0fbf4; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #c6f6d5;">
            <table style="width: 100%; font-size: 14px; color: #2f855a; line-height: 2;">
                <tr>
                    <td style="font-weight: 600;">Quoted Price:</td>
                    <td style="text-align: right; font-weight: 700;">₹{{ number_format($price, 2) }} per {{ $unit }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Minimum Order:</td>
                    <td style="text-align: right;">{{ number_format($minQty, 2) }} {{ $unit }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Quote Validity:</td>
                    <td style="text-align: right;">{{ $validity }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Dispatch Time:</td>
                    <td style="text-align: right;">{{ $dispatch }}</td>
                </tr>
            </table>
        </div>
        
        <p><strong>Vendor Terms:</strong> {{ $terms ?? 'Standard terms apply.' }}</p>
        
        <div class="button-container">
            <a href="https://fuelcab.com/login" class="button">View Quote & Checkout</a>
        </div>
    @endif
@endsection
