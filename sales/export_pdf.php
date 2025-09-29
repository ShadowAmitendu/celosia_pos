<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? 0;

// Fetch sale details
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    die('Invoice not found!');
}

// Fetch sale items
$stmt = $pdo->prepare("SELECT * FROM sales_items WHERE sale_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?> - CELOSIA CANDLES</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            background: #fff;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }

        .header {
            border-bottom: 3px solid #D4AF37;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .brand h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .brand p {
            color: #666;
            font-size: 12px;
        }

        .invoice-info {
            text-align: right;
        }

        .invoice-info h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .invoice-info p {
            color: #666;
            font-size: 12px;
            margin: 2px 0;
        }

        .customer-details {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .customer-details h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .customer-details p {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        thead {
            background: #E6E6FA;
        }

        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            color: #333;
            font-weight: bold;
            border-bottom: 2px solid #D4AF37;
        }

        th.text-center {
            text-align: center;
        }

        th.text-right {
            text-align: right;
        }

        td {
            padding: 12px;
            font-size: 12px;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        td.text-center {
            text-align: center;
        }

        td.text-right {
            text-align: right;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .totals-content {
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
        }

        .total-row.subtotal {
            border-top: 1px solid #ddd;
        }

        .total-row.discount {
            color: #dc2626;
        }

        .total-row.grand-total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
            color: #D4AF37;
        }

        .payment-method {
            background: #f0f0f0;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .notes {
            background: #fff9e6;
            border-left: 4px solid #fbbf24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .notes h3 {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .notes p {
            font-size: 12px;
            color: #666;
        }

        .footer {
            border-top: 2px solid #eee;
            padding-top: 20px;
            text-align: center;
            margin-top: 40px;
        }

        .footer p {
            font-size: 11px;
            color: #999;
            margin: 5px 0;
        }

        @media print {
            body {
                padding: 0;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="brand">
                <h1>@CELOSIACANDLES</h1>
                <p>Handcrafted with Love</p>
                <p>Illuminating moments since 2024</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></strong></p>
                <p><?php echo date('F d, Y', strtotime($sale['created_at'])); ?></p>
                <p><?php echo date('h:i A', strtotime($sale['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Customer Details -->
    <?php if ($sale['customer_name'] || $sale['customer_phone'] || $sale['customer_email']): ?>
        <div class="customer-details">
            <h3>Bill To:</h3>
            <?php if ($sale['customer_name']): ?>
                <p><strong><?php echo htmlspecialchars($sale['customer_name']); ?></strong></p>
            <?php endif; ?>
            <?php if ($sale['customer_phone']): ?>
                <p>Phone: <?php echo htmlspecialchars($sale['customer_phone']); ?></p>
            <?php endif; ?>
            <?php if ($sale['customer_email']): ?>
                <p>Email: <?php echo htmlspecialchars($sale['customer_email']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Items Table -->
    <table>
        <thead>
        <tr>
            <th>Item</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Price</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-right">₹<?php echo number_format($item['price'], 2); ?></td>
                <td class="text-right"><strong>₹<?php echo number_format($item['subtotal'], 2); ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-content">
            <div class="total-row subtotal">
                <span>Subtotal:</span>
                <span><strong>₹<?php echo number_format($sale['subtotal'], 2); ?></strong></span>
            </div>

            <?php if ($sale['discount_percent'] > 0): ?>
                <div class="total-row discount">
                    <span>Discount (<?php echo number_format($sale['discount_percent'], 1); ?>%):</span>
                    <span><strong>-₹<?php echo number_format($sale['discount_amount'], 2); ?></strong></span>
                </div>
            <?php endif; ?>

            <div class="total-row grand-total">
                <span>Total:</span>
                <span>₹<?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="payment-method">
        <span><strong>Payment Method:</strong></span>
        <span><?php echo htmlspecialchars($sale['payment_method']); ?></span>
    </div>

    <!-- Notes -->
    <?php if ($sale['notes']): ?>
        <div class="notes">
            <h3>Notes:</h3>
            <p><?php echo nl2br(htmlspecialchars($sale['notes'])); ?></p>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for your purchase!</strong></p>
        <p>For inquiries, please contact us at @celosiacandles</p>
        <p style="margin-top: 15px;">This is a computer-generated invoice</p>
    </div>
</div>

<script>
    // Auto-print when page loads
    window.onload = function () {
        window.print();
    };
</script>
</body>
</html>