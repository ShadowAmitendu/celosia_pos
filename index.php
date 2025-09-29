<?php
require_once 'config/db.php';
$pageTitle = 'Dashboard - CELOSIA CANDLES';

// Get statistics
$stats = [];

// Total inventory items
$stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory");
$stats['total_items'] = $stmt->fetch()['count'] ?? 0;

// Low stock items (less than 10)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory WHERE quantity < 10");
$stats['low_stock'] = $stmt->fetch()['count'] ?? 0;

// Today's sales
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(created_at) = CURDATE()");
$todaySales = $stmt->fetch();
$stats['today_sales'] = $todaySales['count'] ?? 0;
$stats['today_revenue'] = $todaySales['total'] ?? 0;

// Recent sales
$stmt = $pdo->query("SELECT * FROM sales ORDER BY created_at DESC LIMIT 5");
$recentSales = $stmt->fetchAll();

include 'includes/header.inc.php';
?>

    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-lavender via-pastel-pink to-pastel-mint rounded-3xl shadow-2xl p-8 md:p-12 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-10 rounded-full -ml-24 -mb-24"></div>

        <div class="relative z-10">
            <h1 class="heading-font text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Welcome to @CELOSIACANDLES
            </h1>
            <p class="text-gray-700 text-lg md:text-xl mb-6 max-w-2xl">
                Your complete point of sale and inventory management system. Track your handcrafted candles, manage sales, and grow your business with elegance.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="sales/new_sale.php" class="bg-gold hover:bg-gold-dark text-white px-8 py-3 rounded-full font-semibold transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Start New Sale</span>
                </a>
                <a href="inventory/add_item.php" class="bg-white hover:bg-gray-50 text-gray-800 px-8 py-3 rounded-full font-semibold transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Add Product</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Items -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-pastel-pink hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Products</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_items']; ?></h3>
                </div>
                <div class="bg-pastel-pink rounded-full p-4">
                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-yellow-400 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Low Stock Alert</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['low_stock']; ?></h3>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-pastel-blue hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Today's Sales</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['today_sales']; ?></h3>
                </div>
                <div class="bg-pastel-blue rounded-full p-4">
                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-gold hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Today's Revenue</p>
                    <h3 class="text-3xl font-bold text-gray-800">₹<?php echo number_format($stats['today_revenue'], 2); ?></h3>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="heading-font text-2xl font-bold text-gray-800">Recent Sales</h2>
            <a href="sales/sales_history.php" class="text-gold hover:text-gold-dark font-semibold text-sm flex items-center space-x-1">
                <span>View All</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php if (empty($recentSales)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500">No sales yet. Start your first sale!</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold text-sm">Invoice #</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold text-sm">Date</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold text-sm">Customer</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-semibold text-sm">Amount</th>
                        <th class="text-center py-3 px-4 text-gray-600 font-semibold text-sm">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentSales as $sale): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 font-medium text-gray-800">#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td class="py-3 px-4 text-gray-600"><?php echo date('M d, Y', strtotime($sale['created_at'])); ?></td>
                            <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-800">₹<?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td class="py-3 px-4 text-center">
                                <a href="sales/invoice.php?id=<?php echo $sale['id']; ?>" class="text-gold hover:text-gold-dark font-medium text-sm">
                                    View Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php include 'includes/footer.inc.php'; ?>