<?php
global $pdo;
require_once '../config/db.php';
$pageTitle = 'Delete Product - CELOSIA CANDLES';

$id = $_GET['id'] ?? 0;

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = 'Product not found!';
    header('Location: list_items.php');
    exit;
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check if product is used in any sales
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_items WHERE product_id = ?");
        $stmt->execute([$id]);
        $sales_count = $stmt->fetchColumn();

        if ($sales_count > 0) {
            $_SESSION['error'] = 'Cannot delete this product! It has been used in ' . $sales_count . ' sale(s). Consider marking it as out of stock instead.';
            header('Location: list_items.php');
            exit;
        }

        // Delete the image file if it exists
        if ($item['image']) {
            $image_path = '../assets/images/products/' . $item['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = 'Product deleted successfully!';
        header('Location: list_items.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting product: ' . $e->getMessage();
        header('Location: list_items.php');
        exit;
    }
}

include '../includes/header.inc.php';
?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <a href="list_items.php"
               class="text-gold hover:text-gold-dark">
                <svg class="w-6 h-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800">Delete Product</h1>
        </div>
        <p class="text-gray-600">Confirm product deletion</p>
    </div>

    <!-- Confirmation Card -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-4">
                    <svg class="w-16 h-16 text-red-600"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <h2 class="heading-font text-2xl font-bold text-gray-800 text-center mb-4">
                Are you sure you want to delete this product?
            </h2>

            <p class="text-gray-600 text-center mb-6">
                This action cannot be undone. All product information will be permanently removed.
            </p>

            <!-- Product Preview -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-24 h-24 bg-gradient-to-br from-lavender to-pastel-pink rounded-lg flex items-center justify-center flex-shrink-0">
                        <?php if ($item['image'] && file_exists("../assets/images/products/" . $item['image'])): ?>
                            <img src="../assets/images/products/<?php echo h($item['image']); ?>"
                                 alt="<?php echo h($item['product_name']); ?>"
                                 class="w-full h-full object-cover rounded-lg">
                        <?php else: ?>
                            <svg class="w-12 h-12 text-white"
                                 fill="currentColor"
                                 viewBox="0 0 24 24">
                                <path d="M12 2C11.5 2 11 2.19 10.59 2.59L2.59 10.59C1.8 11.37 1.8 12.63 2.59 13.41L10.59 21.41C11.37 22.2 12.63 22.2 13.41 21.41L21.41 13.41C22.2 12.63 22.2 11.37 21.41 10.59L13.41 2.59C13 2.19 12.5 2 12 2M12 4L20 12L12 20L4 12L12 4M12 6L6 12L12 18L18 12L12 6Z"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-xl text-gray-800 mb-1">
                            <?php echo h($item['product_name']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mb-2">
                            <?php echo h($item['category']); ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-gold">₹<?php echo number_format($item['price'], 2); ?></span>
                            <span class="text-sm text-gray-500">Stock: <?php echo $item['quantity']; ?> units</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Form -->
            <form method="POST">
                <div class="flex gap-4">
                    <button type="submit"
                            name="confirm_delete"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors shadow-md">
                        Yes, Delete Product
                    </button>
                    <a href="list_items.php"
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-6 rounded-lg font-semibold transition-colors text-center">
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Additional Warning -->
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-yellow-700">
                        <strong>Note:</strong> If this product has been used in previous sales, you won't be able to
                        delete it.
                        Instead, you can set the quantity to 0 to mark it as out of stock.
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>