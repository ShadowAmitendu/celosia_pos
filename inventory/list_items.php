<?php
require_once '../config/db.php';
$pageTitle = 'Inventory - CELOSIA CANDLES';

// Get all inventory items with improved security
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM inventory WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (product_name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY product_name ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Get categories for filter
    $stmt = $pdo->query("SELECT DISTINCT category FROM inventory WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading inventory: ' . $e->getMessage();
    $items = [];
    $categories = [];
}

include '../includes/header.inc.php';
?>

    <!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 alert-auto-hide shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="font-medium"><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 alert-auto-hide shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="font-medium"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></p>
        </div>
    </div>
<?php endif; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800 mb-2">Inventory Management</h1>
        <p class="text-gray-600">Manage your candle products and stock levels</p>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input
                        type="text"
                        name="search"
                        value="<?php echo h($search); ?>"
                        placeholder="Search products..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                />
            </div>

            <div class="w-full md:w-48">
                <select
                        name="category"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                >
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo h($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo h($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="bg-gold hover:bg-gold-dark text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Search</span>
            </button>

            <a href="add_item.php" class="bg-pastel-pink hover:bg-opacity-80 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Add Product</span>
            </a>
        </form>
    </div>

    <!-- Inventory Grid -->
<?php if (empty($items)): ?>
    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
        <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Products Found</h3>
        <p class="text-gray-500 mb-6">
            <?php echo $search || $category ? 'Try adjusting your search criteria' : 'Start by adding your first candle product to the inventory'; ?>
        </p>
        <a href="add_item.php" class="inline-flex items-center space-x-2 bg-gold hover:bg-gold-dark text-white px-6 py-3 rounded-lg font-semibold transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Add Your First Product</span>
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow group">
                <!-- Product Image -->
                <div class="h-48 bg-gradient-to-br from-lavender to-pastel-pink flex items-center justify-center relative overflow-hidden">
                    <?php
                    $imagePath = "../assets/images/products/" . $item['image'];
                    $imageExists = $item['image'] && file_exists($imagePath);
                    ?>
                    <?php if ($imageExists): ?>
                        <img src="<?php echo h($imagePath); ?>?v=<?php echo filemtime($imagePath); ?>"
                             alt="<?php echo h($item['product_name']); ?>"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                             loading="lazy"/>
                    <?php else: ?>
                        <svg class="w-20 h-20 text-white opacity-50" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C11.5 2 11 2.19 10.59 2.59L2.59 10.59C1.8 11.37 1.8 12.63 2.59 13.41L10.59 21.41C11.37 22.2 12.63 22.2 13.41 21.41L21.41 13.41C22.2 12.63 22.2 11.37 21.41 10.59L13.41 2.59C13 2.19 12.5 2 12 2M12 4L20 12L12 20L4 12L12 4M12 6L6 12L12 18L18 12L12 6Z"/>
                        </svg>
                    <?php endif; ?>

                    <!-- Stock Badge -->
                    <?php if ($item['quantity'] < 10): ?>
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            <?php echo $item['quantity'] == 0 ? 'Out of Stock' : 'Low Stock'; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Details -->
                <div class="p-5">
                    <div class="mb-3">
                        <span class="inline-block bg-lavender text-gray-700 text-xs font-semibold px-3 py-1 rounded-full mb-2">
                            <?php echo h($item['category']); ?>
                        </span>
                        <h3 class="heading-font text-xl font-bold text-gray-800 mb-1">
                            <?php echo h($item['product_name']); ?>
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-2">
                            <?php echo h($item['description'] ?: 'No description'); ?>
                        </p>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-2xl font-bold text-gold">₹<?php echo number_format($item['price'], 2); ?></p>
                            <p class="text-sm <?php echo $item['quantity'] < 10 ? 'text-red-600 font-semibold' : 'text-gray-500'; ?>">
                                Stock: <span class="font-semibold"><?php echo $item['quantity']; ?></span>
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="edit_item.php?id=<?php echo $item['id']; ?>"
                           class="flex-1 bg-pastel-blue hover:bg-opacity-80 text-gray-800 py-2 rounded-lg font-medium text-center transition-colors text-sm">
                            Edit
                        </a>
                        <a href="delete_item.php?id=<?php echo $item['id']; ?>"
                           class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 py-2 rounded-lg font-medium text-center transition-colors text-sm">
                            Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.inc.php'; ?>