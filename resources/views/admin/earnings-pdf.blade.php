<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SavEats Company Earnings Report</title>
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
            border-bottom: 3px solid #ef4444;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #ef4444;
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
            color: #2d5016;
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
            background-color: #ef4444;
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
        <h1>SavEats Company Earnings Report</h1>
        <div class="header-info">
            <p><strong>Generated on:</strong> {{ $exportDate }}</p>
            <p><strong>Total Transactions:</strong> {{ $totalTransactions }}</p>
        </div>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Gross Revenue</div>
            <div class="summary-value">P{{ number_format($totalGross, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Platform Fees (5%)</div>
            <div class="summary-value">P{{ number_format($totalFees, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Net to Establishments</div>
            <div class="summary-value">P{{ number_format($totalNet, 2) }}</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Establishment</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Gross Amount</th>
                <th>Platform Fee (5%)</th>
                <th>Net to Establishment</th>
                <th>Date Completed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $transaction)
            <tr>
                <td>{{ $transaction['order_number'] }}</td>
                <td>{{ $transaction['establishment_name'] }}</td>
                <td>{{ $transaction['item_name'] }}</td>
                <td>{{ $transaction['quantity'] }}</td>
                <td>P{{ number_format($transaction['total_price'], 2) }}</td>
                <td>P{{ number_format($transaction['platform_fee'], 2) }}</td>
                <td>P{{ number_format($transaction['net_earnings'], 2) }}</td>
                <td>{{ $transaction['completed_at'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="no-data">No transactions found.</td>
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

