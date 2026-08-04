<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'FuelCab Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f6f9f7;
            color: #2d3748;
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f6f9f7;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(21, 92, 50, 0.05);
            border: 1px solid #e8eef0;
        }
        .header {
            background-color: #155c32;
            padding: 30px;
            text-align: center;
        }
        .header img {
            height: 40px;
            margin-bottom: 10px;
            vertical-align: middle;
        }
        .header-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header-title span {
            color: #33b248;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .content h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 16px;
            color: #4a5568;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #155c32;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(21, 92, 50, 0.15);
            transition: background-color 0.2s ease;
        }
        .button:hover {
            background-color: #0d3a1f;
        }
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #edf2f7;
            font-size: 12px;
            color: #a0aec0;
        }
        .footer a {
            color: #155c32;
            text-decoration: none;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #def7ec; color: #03543f; }
        .badge-warning { background-color: #fdf2f2; color: #9b1c1c; }
        .badge-info { background-color: #e1effe; color: #1e429f; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 class="header-title">Fuel<span>Cab</span></h1>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p>© {{ date('Y') }} FuelCab India. All rights reserved.</p>
                <p>Need support? Visit our <a href="https://fuelcab.com/support">Support Center</a> or email <a href="mailto:support@fuelcab.com">support@fuelcab.com</a></p>
                <p style="margin-top: 15px; font-size: 11px;">You are receiving this email because you registered an account on FuelCab.com.</p>
            </div>
        </div>
    </div>
</body>
</html>
