<?php
global $pdo;
require_once '../config/db.php';
$pageTitle = 'Sales History - CELOSIA CANDLES';

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Filters
$search = $_GET['search'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT * FROM sales WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ? OR id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = intval($search);
}

if ($payment_method) {
    $sql .= " AND payment_method = ?";
    $params[] = $payment_method;
}

if ($date_from) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
}

// Get total count for pagination
$count_sql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get sales with pagination
$sql .= " ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Get payment methods for filter
$stmt = $pdo->query("SELECT DISTINCT payment_method FROM sales ORDER BY payment_method");
$payment_methods = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate summary statistics
$summary_sql = "SELECT COUNT(*) as total_sales, COALESCE(SUM(total_amount), 0) as total_revenue, COALESCE(SUM(discount_amount), 0) as total_discounts FROM sales WHERE 1=1";
$summary_params = [];

if ($search) {
    $summary_sql .= " AND (customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ? OR id = ?)";
    $summary_params[] = "%$search%";
    $summary_params[] = "%$search%";
    $summary_params[] = "%$search%";
    $summary_params[] = intval($search);
}

if ($payment_method) {
    $summary_sql .= " AND payment_method = ?";
    $summary_params[] = $payment_method;
}

if ($date_from) {
    $summary_sql .= " AND DATE(created_at) >= ?";
    $summary_params[] = $date_from;
}

if ($date_to) {
    $summary_sql .= " AND DATE(created_at) <= ?";
    $summary_params[] = $date_to;
}

$stmt = $pdo->prepare($summary_sql);
$stmt->execute($summary_params);
$summary = $stmt->fetch();

include '../includes/header.inc.php';
?>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800 mb-2">Sales History</h1>
        <p class="text-gray-600">View and manage all your sales transactions</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-pastel-blue">
            <p class="text-gray-500 text-sm font-medium mb-1">Total Sales</p>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($summary['total_sales']); ?></h3>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-gold">
            <p class="text-gray-500 text-sm font-medium mb-1">Total Revenue</p>
            <h3 class="text-3xl font-bold text-gray-800">
                ₹<?php echo number_format($summary['total_revenue'], 2); ?></h3>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-pastel-pink">
            <p class="text-gray-500 text-sm font-medium mb-1">Total Discounts</p>
            <h3 class="text-3xl font-bold text-gray-800">
                ₹<?php echo number_format($summary['total_discounts'], 2); ?></h3>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <form method="GET"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <input type="text"
                       name="search"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search by name, phone, email, or ID..."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent">
            </div>

            <div>
                <select name="payment_method"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent">
                    <option value="">All Payment Methods</option>
                    <?php foreach ($payment_methods as $method): ?>
                        <option value="<?php echo htmlspecialchars($method); ?>"
                                <?php echo $payment_method === $method ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($method); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <input type="date"
                       name="date_from"
                       value="<?php echo htmlspecialchars($date_from); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                       placeholder="From Date">
            </div>

            <div>
                <input type="date"
                       name="date_to"
                       value="<?php echo htmlspecialchars($date_to); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                       placeholder="To Date">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-gold hover:bg-gold-dark text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-md">
                    Filter
                </button>
                <a href="sales_history.php"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <?php if (empty($sales)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Sales Found</h3>
                <p class="text-gray-500 mb-6">No sales match your search criteria</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-lavender to-pastel-pink">
                    <tr>
                        <th class="text-left py-4 px-6 text-gray-700 font-semibold">Invoice #</th>
                        <th class="text-left py-4 px-6 text-gray-700 font-semibold">Date & Time</th>
                        <th class="text-left py-4 px-6 text-gray-700 font-semibold">Customer</th>
                        <th class="text-left py-4 px-6 text-gray-700 font-semibold">Payment</th>
                        <th class="text-right py-4 px-6 text-gray-700 font-semibold">Amount</th>
                        <th class="text-center py-4 px-6 text-gray-700 font-semibold">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-bold text-gray-800">#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-gray-800"><?php echo date('M d, Y', strtotime($sale['created_at'])); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($sale['created_at'])); ?></div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-gray-800 font-medium">
                                    <?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer'); ?>
                                </div>
                                <?php if ($sale['customer_phone']): ?>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($sale['customer_phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-block bg-pastel-blue px-3 py-1 rounded-full text-xs font-semibold text-gray-700">
                                    <?php echo htmlspecialchars($sale['payment_method']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="font-bold text-lg text-gray-800">
                                    ₹<?php echo number_format($sale['total_amount'], 2); ?></div>
                                <?php if ($sale['discount_amount'] > 0): ?>
                                    <div class="text-xs text-red-500">
                                        -₹<?php echo number_format($sale['discount_amount'], 2); ?> off
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="invoice.php?id=<?php echo $sale['id']; ?>"
                                       class="bg-gold hover:bg-gold-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        View
                                    </a>
                                    <a href="export_pdf.php?id=<?php echo $sale['id']; ?>"
                                       target="_blank"
                                       class="bg-pastel-pink hover:bg-opacity-80 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_records); ?>
                        of <?php echo $total_records; ?> results
                    </div>

                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&payment_method=<?php echo urlencode($payment_method); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&payment_method=<?php echo urlencode($payment_method); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                               class="px-4 py-2 <?php echo $i === (int)$page ? 'bg-gold text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition-colors">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&payment_method=<?php echo urlencode($payment_method); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.inc.php'; ?>