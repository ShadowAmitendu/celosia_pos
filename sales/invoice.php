<?php
require_once '../config/db.php';
$pageTitle = 'Invoice - CELOSIA CANDLES';

$id = $_GET['id'] ?? 0;

// Fetch sale details
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    $_SESSION['error'] = 'Invoice not found!';
    header('Location: sales_history.php');
    exit;
}

// Fetch sale items
$stmt = $pdo->prepare("SELECT * FROM sales_items WHERE sale_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

include '../includes/header.inc.php';
?>

    <style>
        @media print {
            nav, footer, .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-container {
                box-shadow: none !important;
                margin: 0 !important;
            }
        }
    </style>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center mb-6 no-print">
        <a href="sales_history.php"
           class="text-gold hover:text-gold-dark font-semibold flex items-center space-x-2">
            <svg class="w-5 h-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Back to Sales History</span>
        </a>

        <div class="flex gap-3">
            <button onclick="window.print()"
                    class="bg-gold hover:bg-gold-dark text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-md flex items-center space-x-2">
                <svg class="w-5 h-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Invoice</span>
            </button>
        </div>
    </div>

    <!-- Invoice -->
    <div class="print-container bg-white rounded-2xl shadow-lg p-8 md:p-12 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="border-b-2 border-gray-200 pb-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="bg-gradient-to-br from-lavender to-pastel-pink rounded-full p-2">
                            <svg class="w-10 h-10 text-gold"
                                 fill="currentColor"
                                 viewBox="0 0 24 24">
                                <path d="M12 2C11.5 2 11 2.19 10.59 2.59L2.59 10.59C1.8 11.37 1.8 12.63 2.59 13.41L10.59 21.41C11.37 22.2 12.63 22.2 13.41 21.41L21.41 13.41C22.2 12.63 22.2 11.37 21.41 10.59L13.41 2.59C13 2.19 12.5 2 12 2M12 4L20 12L12 20L4 12L12 4M12 6L6 12L12 18L18 12L12 6Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="heading-font text-3xl font-bold text-gray-800">@CELOSIACANDLES</h1>
                            <p class="text-sm text-gray-600">Handcrafted with Love</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Illuminating moments since 2024</p>
                </div>

                <div class="text-right">
                    <h2 class="heading-font text-2xl font-bold text-gray-800 mb-2">INVOICE</h2>
                    <p class="text-gray-600">#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></p>
                    <p class="text-sm text-gray-500"><?php echo date('F d, Y', strtotime($sale['created_at'])); ?></p>
                    <p class="text-sm text-gray-500"><?php echo date('h:i A', strtotime($sale['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Customer Details -->
        <?php if ($sale['customer_name'] || $sale['customer_phone'] || $sale['customer_email']): ?>
            <div class="mb-6 bg-lavender bg-opacity-30 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Bill To:</h3>
                <?php if ($sale['customer_name']): ?>
                    <p class="text-gray-700"><?php echo htmlspecialchars($sale['customer_name']); ?></p>
                <?php endif; ?>
                <?php if ($sale['customer_phone']): ?>
                    <p class="text-gray-600 text-sm">Phone: <?php echo htmlspecialchars($sale['customer_phone']); ?></p>
                <?php endif; ?>
                <?php if ($sale['customer_email']): ?>
                    <p class="text-gray-600 text-sm">Email: <?php echo htmlspecialchars($sale['customer_email']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full">
                <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-3 px-2 text-gray-700 font-semibold">Item</th>
                    <th class="text-center py-3 px-2 text-gray-700 font-semibold">Qty</th>
                    <th class="text-right py-3 px-2 text-gray-700 font-semibold">Price</th>
                    <th class="text-right py-3 px-2 text-gray-700 font-semibold">Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="border-b border-gray-200">
                        <td class="py-3 px-2 text-gray-800"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="py-3 px-2 text-center text-gray-700"><?php echo $item['quantity']; ?></td>
                        <td class="py-3 px-2 text-right text-gray-700">
                            ₹<?php echo number_format($item['price'], 2); ?></td>
                        <td class="py-3 px-2 text-right text-gray-800 font-semibold">
                            ₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-6">
            <div class="w-full md:w-1/2 space-y-2">
                <div class="flex justify-between py-2">
                    <span class="text-gray-700">Subtotal:</span>
                    <span class="font-semibold text-gray-800">₹<?php echo number_format($sale['subtotal'], 2); ?></span>
                </div>

                <?php if ($sale['discount_percent'] > 0): ?>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-700">Discount (<?php echo number_format($sale['discount_percent'], 1); ?>%):</span>
                        <span class="font-semibold text-red-600">-₹<?php echo number_format($sale['discount_amount'], 2); ?></span>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between py-3 border-t-2 border-gray-300">
                    <span class="text-xl font-bold text-gray-800">Total:</span>
                    <span class="text-2xl font-bold text-gold">₹<?php echo number_format($sale['total_amount'], 2); ?></span>
                </div>

                <div class="flex justify-between py-2 bg-lavender bg-opacity-30 rounded px-3">
                    <span class="text-gray-700 font-semibold">Payment Method:</span>
                    <span class="text-gray-800 font-semibold"><?php echo htmlspecialchars($sale['payment_method']); ?></span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if ($sale['notes']): ?>
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <h3 class="font-semibold text-gray-800 mb-1">Notes:</h3>
                <p class="text-gray-700 text-sm"><?php echo nl2br(htmlspecialchars($sale['notes'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="border-t-2 border-gray-200 pt-6 mt-8">
            <div class="text-center">
                <p class="text-gray-600 mb-2">Thank you for your purchase!</p>
                <p class="text-sm text-gray-500">For inquiries, please contact us at @celosiacandles</p>
                <p class="text-xs text-gray-400 mt-4">This is a computer-generated invoice</p>
            </div>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>