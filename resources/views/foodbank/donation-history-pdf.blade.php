<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Donation History Report</title>
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
        }
        .summary p {
            margin: 5px 0;
            font-size: 11px;
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
        <h1>Donation History Report</h1>
        <div class="header-info">
            <p><strong>Foodbank:</strong> {{ $foodbankName }}</p>
            <p><strong>Generated on:</strong> {{ $exportDate }}</p>
            <p><strong>Total Donations:</strong> {{ $totalDonations }}</p>
        </div>
    </div>
    
    <div class="summary">
        <p><strong>Report Summary:</strong> This report contains all donation records received by this foodbank with complete details including item information, scheduling, and donor details.</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Donation #</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Type</th>
                <th>Date Donated</th>
                <th>Scheduled Date</th>
                <th>Scheduled Time</th>
                <th>Expiry Date</th>
                <th>Collected Date</th>
                <th>Status</th>
                <th>Donor (Establishment)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $donation)
            <tr>
                <td>{{ $donation['donation_number'] }}</td>
                <td>{{ $donation['item_name'] }}</td>
                <td>{{ $donation['category'] }}</td>
                <td>{{ $donation['quantity'] }}</td>
                <td>{{ $donation['unit'] }}</td>
                <td>{{ $donation['donation_type'] }}</td>
                <td>{{ $donation['date_donated'] }}</td>
                <td>{{ $donation['scheduled_date'] }}</td>
                <td>{{ $donation['scheduled_time'] }}</td>
                <td>{{ $donation['expiry_date'] }}</td>
                <td>{{ $donation['collected_at'] }}</td>
                <td>{{ $donation['status'] }}</td>
                <td>{{ $donation['donor'] }}</td>
            </tr>
            @if(!empty($donation['description']) && $donation['description'] !== 'N/A')
            <tr>
                <td colspan="13" style="font-size: 8px; color: #666; padding-left: 20px;">
                    <strong>Description:</strong> {{ $donation['description'] }}
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="13" class="no-data">No donations found.</td>
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

