<?php
global $pdo;
require_once '../config/db.php';
$pageTitle = 'Edit Product - CELOSIA CANDLES';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    $_SESSION['error'] = 'Invalid product ID';
    header('Location: list_items.php');
    exit;
}

// Fetch product details
try {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        header('Location: list_items.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading product: ' . $e->getMessage();
    header('Location: list_items.php');
    exit;
}

// Fetch all categories for dropdown
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading categories: ' . $e->getMessage();
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');

    // Validation
    $errors = [];

    if (empty($product_name)) {
        $errors[] = 'Product name is required';
    }

    if (empty($category)) {
        $errors[] = 'Category is required';
    }

    if (empty($price) || !is_numeric($price) || $price < 0) {
        $errors[] = 'Valid price is required';
    }

    if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) {
        $errors[] = 'Valid quantity is required';
    }

    // Handle image upload
    $image_path = $product['image']; // Keep existing image by default

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = 'Invalid image type. Only JPG, PNG, and WEBP are allowed';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = 'Image size must be less than 5MB';
        } else {
            // ✅ CORRECT PATH
            $upload_dir = '../assets/images/products/';

            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'product_' . $product_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                    unlink($upload_dir . $product['image']);
                }
                // ✅ STORE ONLY FILENAME, NOT PATH
                $image_path = $new_filename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE inventory 
                SET product_name = ?, 
                    description = ?, 
                    category = ?, 
                    price = ?, 
                    quantity = ?,
                    image = ?
                WHERE id = ?
            ");

            $stmt->execute([
                    $product_name,
                    $description,
                    $category,
                    $price,
                    $quantity,
                    $image_path,
                    $product_id
            ]);

            $_SESSION['success'] = 'Product updated successfully';
            header('Location: list_items.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error updating product: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include '../includes/header.inc.php';
?>

    <!-- Error Messages -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 alert-auto-hide shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2"
                 fill="currentColor"
                 viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                      clip-rule="evenodd"/>
            </svg>
            <p class="font-medium"><?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?></p>
        </div>
    </div>
<?php endif; ?>

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
            <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800">Edit Product</h1>
        </div>
        <p class="text-gray-600">Update product information and inventory details</p>
    </div>

    <!-- Edit Form -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form method="POST"
                  enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Name -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input
                                type="text"
                                name="product_name"
                                value="<?php echo h($product['product_name']); ?>"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                placeholder="Enter product name"
                        />
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Description
                        </label>
                        <textarea
                                name="description"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                placeholder="Enter product description"
                        ><?php echo h($product['description']); ?></textarea>
                    </div>

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
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo h($cat); ?>" <?php echo ($product['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo h($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">
                            <a href="manage_categories.php"
                               class="text-gold hover:underline">Manage Categories</a>
                        </p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Price (₹) <span class="text-red-500">*</span>
                        </label>
                        <input
                                type="number"
                                name="price"
                                value="<?php echo h($product['price']); ?>"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                placeholder="0.00"
                        />
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Current Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                                type="number"
                                name="quantity"
                                value="<?php echo h($product['quantity']); ?>"
                                min="0"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                placeholder="0"
                        />
                    </div>

                    <!-- Current Image Display -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Current Image
                        </label>
                        <?php if (!empty($product['image'])): ?>
                            <div class="border border-gray-300 rounded-lg p-4">
                                <img
                                        src="../assets/images/products/<?php echo h($product['image']); ?>"
                                        alt="<?php echo h($product['product_name']); ?>"
                                        class="w-full h-48 object-contain"
                                        onerror="this.src='../assets/images/placeholder.png'"
                                />
                            </div>
                        <?php else: ?>
                            <div class="border border-gray-300 rounded-lg p-4 text-center text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-2"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p>No image uploaded</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Image Upload -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Update Product Image
                        </label>
                        <input
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                        <p class="text-sm text-gray-500 mt-1">
                            Leave empty to keep current image. Accepted formats: JPG, PNG, WEBP (Max: 5MB)
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button
                            type="submit"
                            class="flex-1 bg-gold hover:bg-gold-dark text-white py-3 px-6 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Update Product</span>
                    </button>

                    <a
                            href="list_items.php"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-6 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Additional Info Card -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mt-6">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                          clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-700">
                    <p class="font-semibold mb-1">Product Information</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Created: <?php echo date('M d, Y \a\t g:i A', strtotime($product['created_at'])); ?></li>
                        <li>Last
                            Updated: <?php echo date('M d, Y \a\t g:i A', strtotime($product['updated_at'])); ?></li>
                        <li>Product ID: #<?php echo $product['id']; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>