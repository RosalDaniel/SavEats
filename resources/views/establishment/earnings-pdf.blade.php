<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Earnings Report - {{ $establishmentName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #2d5016;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2d5016;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header-info {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
        }
        .summary {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }
        .summary-item.gross .summary-value {
            color: #3b82f6;
        }
        .summary-item.fee .summary-value {
            color: #ef4444;
        }
        .summary-item.net .summary-value {
            color: #10b981;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #2d5016;
            color: white;
            font-weight: 600;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Earnings Report</h1>
        <div class="header-info">
            <p><strong>Establishment:</strong> {{ $establishmentName }}</p>
            <p><strong>Generated on:</strong> {{ $exportDate }}</p>
            <p><strong>Total Orders:</strong> {{ $totalOrders }}</p>
        </div>
    </div>
    
    <div class="summary">
        <div class="summary-item gross">
            <div class="summary-label">Total Gross Earnings</div>
            <div class="summary-value">₱{{ number_format($totalGross, 2) }}</div>
        </div>
        <div class="summary-item fee">
            <div class="summary-label">Total Platform Fees (5%)</div>
            <div class="summary-value">₱{{ number_format($totalFees, 2) }}</div>
        </div>
        <div class="summary-item net">
            <div class="summary-label">Total Net Earnings</div>
            <div class="summary-value">₱{{ number_format($totalNet, 2) }}</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Gross Amount</th>
                <th>Platform Fee (5%)</th>
                <th>Net Earnings</th>
                <th>Customer</th>
                <th>Payment</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $order)
            <tr>
                <td>{{ $order['order_number'] }}</td>
                <td>{{ $order['product_name'] }}</td>
                <td>{{ $order['quantity'] }}</td>
                <td>₱{{ number_format($order['unit_price'], 2) }}</td>
                <td>₱{{ number_format($order['total_price'], 2) }}</td>
                <td>₱{{ number_format($order['platform_fee'], 2) }}</td>
                <td>₱{{ number_format($order['net_earnings'], 2) }}</td>
                <td>{{ $order['customer_name'] }}</td>
                <td>{{ $order['payment_method'] }}</td>
                <td>{{ $order['completed_at'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="no-data">No orders found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>This report was generated automatically by SavEats Platform</p>
        <p>For questions or concerns, please contact support.</p>
    </div>
</body>
</html>

