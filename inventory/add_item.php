<?php
require_once '../config/db.php';
$pageTitle = 'Add Product - CELOSIA CANDLES';

// Fetch categories from database
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading categories: ' . $e->getMessage();
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $image = '';

    // Validation
    $errors = [];

    if (empty($product_name)) {
        $errors[] = 'Product name is required';
    } elseif (strlen($product_name) > 255) {
        $errors[] = 'Product name is too long (max 255 characters)';
    }

    if (empty($category)) {
        $errors[] = 'Category is required';
    }

    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0';
    } elseif ($price > 999999.99) {
        $errors[] = 'Price is too high';
    }

    if ($quantity < 0) {
        $errors[] = 'Quantity cannot be negative';
    } elseif ($quantity > 999999) {
        $errors[] = 'Quantity is too high';
    }

    if (strlen($description) > 5000) {
        $errors[] = 'Description is too long (max 5000 characters)';
    }

    // Handle image upload with improved validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file
        if (!in_array($file_ext, $allowed)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed';
        } elseif ($file_size > $max_size) {
            $errors[] = 'File size must be less than 5MB';
        } elseif (!getimagesize($file_tmp)) {
            $errors[] = 'File is not a valid image';
        } else {
            // Generate unique filename
            $image = uniqid('product_', true) . '.' . $file_ext;
            $target_path = $upload_dir . $image;

            if (!move_uploaded_file($file_tmp, $target_path)) {
                $errors[] = 'Failed to upload image';
                $image = '';
            }
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inventory (product_name, description, category, price, quantity, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$product_name, $description, $category, $price, $quantity, $image]);

            $_SESSION['success'] = 'Product added successfully!';
            header('Location: list_items.php');
            exit;
        } catch (PDOException $e) {
            // Delete uploaded image if database insert fails
            if ($image && file_exists($upload_dir . $image)) {
                unlink($upload_dir . $image);
            }
            $errors[] = 'Error adding product: ' . $e->getMessage();
        }
    }

    // Store errors in session
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include '../includes/header.inc.php';
?>

    <!-- Error Messages -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-md">
        <div class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0"
                 fill="currentColor"
                 viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                      clip-rule="evenodd"/>
            </svg>
            <div class="font-medium"><?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?></div>
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
            <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800">Add New Product</h1>
        </div>
        <p class="text-gray-600">Add a new candle to your inventory</p>
    </div>

    <!-- Add Product Form -->
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form method="POST"
                  enctype="multipart/form-data">
                <!-- Product Name -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                            type="text"
                            name="product_name"
                            required
                            maxlength="255"
                            value="<?php echo h($_POST['product_name'] ?? ''); ?>"
                            placeholder="e.g., Lavender Dreams Candle"
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
                            maxlength="5000"
                            placeholder="Describe your candle..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    ><?php echo h($_POST['description'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Optional - Max 5000 characters</p>
                </div>

                <!-- Category and Price Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <?php if (empty($categories)): ?>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-2">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0"
                                         fill="currentColor"
                                         viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-yellow-700">
                                        <strong>No categories available!</strong> Please <a href="manage_categories.php"
                                                                                            class="underline font-semibold">add
                                            categories</a> first.
                                    </div>
                                </div>
                            </div>
                            <select name="category"
                                    disabled
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100">
                                <option>No categories available</option>
                            </select>
                        <?php else: ?>
                            <div class="relative">
                                <select
                                        name="category"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent appearance-none"
                                >
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo h($cat); ?>" <?php echo ($_POST['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                            <?php echo h($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <a href="manage_categories.php"
                                   class="text-gold hover:underline">Manage categories</a>
                            </p>
                        <?php endif; ?>
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
                                min="0.01"
                                max="999999.99"
                                step="0.01"
                                value="<?php echo h($_POST['price'] ?? ''); ?>"
                                placeholder="0.00"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                    </div>
                </div>

                <!-- Quantity and Image Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Quantity -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Initial Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                                type="number"
                                name="quantity"
                                required
                                min="0"
                                max="999999"
                                value="<?php echo h($_POST['quantity'] ?? ''); ?>"
                                placeholder="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Product Image
                        </label>
                        <input
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-lavender file:text-gray-700 hover:file:bg-opacity-80 cursor-pointer"
                        />
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, or WEBP (Max 5MB)</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button
                            type="submit"
                            class="flex-1 bg-gold hover:bg-gold-dark text-white py-3 px-6 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2"
                            <?php echo empty($categories) ? 'disabled' : ''; ?>
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
                        <span>Add Product</span>
                    </button>

                    <a
                            href="list_items.php"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-6 rounded-lg font-semibold transition-colors text-center flex items-center justify-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>