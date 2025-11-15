<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Donation History Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #2d5016;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Donation History Report</h1>
    <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Donation Number</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Date Donated</th>
                <th>Foodbank</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $donation)
            <tr>
                <td>{{ $donation['donation_number'] }}</td>
                <td>{{ $donation['item_name'] }}</td>
                <td>{{ $donation['category'] }}</td>
                <td>{{ $donation['quantity'] }}</td>
                <td>{{ $donation['date_donated'] }}</td>
                <td>{{ $donation['foodbank'] }}</td>
                <td>{{ $donation['status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No donations found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

