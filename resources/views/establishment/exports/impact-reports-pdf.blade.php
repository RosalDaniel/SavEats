<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Report - {{ $establishmentName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 40px;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2d5016;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #2d5016;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .header .date {
            font-size: 14px;
            color: #9ca3af;
        }
        
        .summary-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2d5016;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .summary-card-wrapper {
            display: table-row;
        }
        
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .summary-card .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2d5016;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .data-table th {
            background: #2d5016;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
        }
        
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-style: italic;
        }
        
        @media print {
            body {
                padding: 20px;
            }
        }
        
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Impact Report</h1>
        <div class="subtitle">{{ $establishmentName }}</div>
        <div class="date">Generated: {{ $reportDate }}</div>
        <div class="date">Date Range: {{ $dateRange }}</div>
    </div>
    
        <div class="summary-section">
            <div class="section-title">Summary</div>
            <table class="summary-cards" style="width: 100%; margin-bottom: 30px;">
                <tr>
                    <td style="width: 33.33%; padding: 10px;">
                        <div class="summary-card">
                            <div class="label">Food Saved</div>
                            <div class="value">{{ number_format($foodSaved) }}</div>
                        </div>
                    </td>
                    <td style="width: 33.33%; padding: 10px;">
                        <div class="summary-card">
                            <div class="label">Total Earnings</div>
                            <div class="value">P{{ number_format($costSavings, 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 33.33%; padding: 10px;">
                        <div class="summary-card">
                            <div class="label">Food Donations Completed</div>
                            <div class="value">{{ number_format($foodDonated) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    
    <div class="summary-section">
        <div class="section-title">Monthly Trends</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Items Saved</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($monthlyData))
                    @foreach($monthlyData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td>{{ number_format($data['value']) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="no-data">No monthly data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="summary-section">
        <div class="section-title">Daily Trends (Last 7 Days)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Items Saved</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($dailyData))
                    @foreach($dailyData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td>{{ number_format($data['value']) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="no-data">No daily data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="summary-section">
        <div class="section-title">Yearly Trends</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Items Saved</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($yearlyData))
                    @foreach($yearlyData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td>{{ number_format($data['value']) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="no-data">No yearly data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="summary-section">
        <div class="section-title">Foodbanks Ranking of Donated Items</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Foodbank Name</th>
                    <th>Quantity</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($topDonatedItems))
                    @foreach($topDonatedItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['foodbank_name'] }}</td>
                            <td>{{ number_format($item['quantity']) }}</td>
                            <td>{{ $item['percentage'] }}%</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="no-data">No donation data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>This report was generated on {{ $reportDate }} by SavEats Platform</p>
        <p>For questions or support, please contact your system administrator.</p>
    </div>
</body>
</html>

