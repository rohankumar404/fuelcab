@extends('emails.layout', ['title' => 'Quotation Update - FuelCab'])

@section('content')
    @if($type === 'request')
        <h1>New B2B Lead / Quote Request</h1>
        <p>Dear Admin,</p>
        <p>A customer has submitted a new bulk fuel quote request. Below are the full enquiry details:</p>

        {{-- Customer Info --}}
        <div style="background-color: #f0fbf4; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #c6f6d5;">
            <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #276749; margin: 0 0 12px;">Customer Details</p>
            <table style="width: 100%; font-size: 14px; color: #2d3748; line-height: 2;">
                @if(!empty($customerName))
                <tr>
                    <td style="font-weight: 600;">Name:</td>
                    <td style="text-align: right;">{{ $customerName }}</td>
                </tr>
                @endif
                @if(!empty($customerCompany))
                <tr>
                    <td style="font-weight: 600;">Company:</td>
                    <td style="text-align: right;">{{ $customerCompany }}</td>
                </tr>
                @endif
                @if(!empty($customerEmail))
                <tr>
                    <td style="font-weight: 600;">Email:</td>
                    <td style="text-align: right;"><a href="mailto:{{ $customerEmail }}" style="color: #155c32;">{{ $customerEmail }}</a></td>
                </tr>
                @endif
                @if(!empty($customerPhone))
                <tr>
                    <td style="font-weight: 600;">Phone:</td>
                    <td style="text-align: right;">{{ $customerPhone }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Enquiry Details --}}
        <div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;">
            <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #4a5568; margin: 0 0 12px;">Enquiry Details</p>
            <table style="width: 100%; font-size: 14px; color: #4a5568; line-height: 2;">
                <tr>
                    <td style="font-weight: 600;">Product:</td>
                    <td style="text-align: right;">{{ $productName }}</td>
                </tr>
                @if(!empty($listingSlug))
                <tr>
                    <td style="font-weight: 600;">Listing:</td>
                    <td style="text-align: right;">{{ $listingSlug }}</td>
                </tr>
                @endif
                @if(!empty($vendorName))
                <tr>
                    <td style="font-weight: 600;">Vendor / Supplier:</td>
                    <td style="text-align: right;">{{ $vendorName }}</td>
                </tr>
                @endif
                <tr>
                    <td style="font-weight: 600;">Quantity Required:</td>
                    <td style="text-align: right;">{{ number_format($quantity, 2) }} units</td>
                </tr>
                @if(!empty($deliveryDate))
                <tr>
                    <td style="font-weight: 600;">Target Delivery:</td>
                    <td style="text-align: right;">{{ $deliveryDate }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if(!empty($customerMessage))
        <div style="background-color: #fffbeb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #fef3c7;">
            <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #92400e; margin: 0 0 8px;">Customer Message</p>
            <p style="font-size: 14px; color: #4a5568; margin: 0; line-height: 1.7;">{{ $customerMessage }}</p>
        </div>
        @endif

        <p>Please log in to the admin portal to respond to this inquiry and forward it to the relevant vendor.</p>

        <div class="button-container">
            <a href="{{ config('app.url') }}/admin" class="button">View in Admin Portal</a>
        </div>
    @else
        <h1>Quotation Submitted for Your Request</h1>
        <p>Dear customer,</p>
        <p>A supplier has responded to your bulk inquiry with tailored B2B volume pricing. Below are the quotation details:</p>

        <div style="background-color: #f0fbf4; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #c6f6d5;">
            <table style="width: 100%; font-size: 14px; color: #2f855a; line-height: 2;">
                <tr>
                    <td style="font-weight: 600;">Quoted Price:</td>
                    <td style="text-align: right; font-weight: 700;">₹{{ number_format($price ?? 0, 2) }} per {{ $unit }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Minimum Order:</td>
                    <td style="text-align: right;">{{ number_format($minQty ?? 0, 2) }} {{ $unit }}</td>
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
            <a href="{{ config('app.url') }}/login" class="button">View Quote &amp; Checkout</a>
        </div>
    @endif
@endsection
