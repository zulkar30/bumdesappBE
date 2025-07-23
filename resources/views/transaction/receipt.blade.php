<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resi Pengiriman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .container {
            width: 80%;
            max-width: 800px;
            background: white;
            padding: 20px;
            margin: auto;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 120px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            margin-top: 10px;
        }

        .info {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .table-container {
            width: 100%;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .qr-container {
            text-align: center;
            margin-top: 20px;
        }

        .print-btn {
            display: block;
            width: 120px;
            margin: 20px auto;
            padding: 10px;
            background: #4CAF50;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        .print-btn:hover {
            background: #45a049;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/logo/ic_launcher.png') }}" alt="Logo Toko">
            <div class="title">Resi Pengiriman</div>
        </div>

        <div class="info">
            <p><strong>ID Transaksi:</strong> {{ $transaction->id }}</p>
            <p><strong>Tanggal:</strong>
                {{ \Carbon\Carbon::createFromTimestamp($transaction->created_at)->format('d M Y, H:i') }}</p>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>Produk</th>
                    <td>{{ $transaction->product->name }}</td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>{{ $transaction->quantity }} item</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>Nama Penerima</th>
                    <td>{{ $transaction->user->name }}</td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td>{{ $transaction->user->address }}, {{ $transaction->user->city }}</td>
                </tr>
                <tr>
                    <th>Telepon</th>
                    <td>{{ $transaction->user->phoneNumber }}</td>
                </tr>
            </table>
        </div>

        <button class="print-btn" onclick="window.print()">Print Resi</button>
    </div>
</body>

</html>
