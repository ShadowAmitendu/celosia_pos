<?php
require_once '../config/db.php';
$pageTitle = 'Edit Product - CELOSIA CANDLES';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image = $item['image'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed)) {
            // Delete old image
            if ($item['image'] && file_exists($upload_dir . $item['image'])) {
                unlink($upload_dir . $item['image']);
            }

            $image = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE inventory SET product_name = ?, description = ?, category = ?, price = ?, quantity = ?, image = ? WHERE id = ?");
        $stmt->execute([$product_name, $description, $category, $price, $quantity, $image, $id]);

        $_SESSION['success'] = 'Product updated successfully!';
        header('Location: list_items.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating product: ' . $e->getMessage();
    }
}

include '../includes/header.inc.php';
?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <a href="list_items.php" class="text-gold hover:text-gold-dark">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800">Edit Product</h1>
        </div>
        <p class="text-gray-600">Update product details and inventory</p>
    </div>

    <!-- Edit Product Form -->
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form method="POST" enctype="multipart/form-data">
                <!-- Current Image Preview -->
                <?php if ($item['image'] && file_exists("../assets/images/products/" . $item['image'])): ?>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">Current Image</label>
                        <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>"
                             alt="Current product image"
                             class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                    </div>
                <?php endif; ?>

                <!-- Product Name -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="product_name"
                        required
                        value="<?php echo htmlspecialchars($item['product_name']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    />
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Description
                    </label>
                    <textarea
                        name="description"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    ><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <!-- Category and Price Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="category"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        >
                            <option value="">Select Category</option>
                            <option value="Scented Candle" <?php echo $item['category'] === 'Scented Candle' ? 'selected' : ''; ?>>Scented Candle</option>
                            <option value="Seasonal" <?php echo $item['category'] === 'Seasonal' ? 'selected' : ''; ?>>Seasonal</option>
                            <option value="Wellness" <?php echo $item['category'] === 'Wellness' ? 'selected' : ''; ?>>Wellness</option>
                            <option value="Premium" <?php echo $item['category'] === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Gift Set" <?php echo $item['category'] === 'Gift Set' ? 'selected' : ''; ?>>Gift Set</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Price (₹) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="price"
                            required
                            min="0"
                            step="0.01"
                            value="<?php echo $item['price']; ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                    </div>
                </div>

                <!-- Quantity and Image Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Quantity -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="quantity"
                            required
                            min="0"
                            value="<?php echo $item['quantity']; ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Update Product Image
                        </label>
                        <input
                            type="file"
                            name="image"
                            accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-lavender file:text-gray-700 hover:file:bg-opacity-80"
                        />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="flex-1 bg-gold hover:bg-gold-dark text-white py-3 px-6 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Update Product</span>
                    </button>

                    <a
                        href="list_items.php"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-6 rounded-lg font-semibold transition-colors text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>