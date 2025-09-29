<?php
require_once '../config/db.php';
$pageTitle = 'Add Product - CELOSIA CANDLES';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $image = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO inventory (product_name, description, category, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $description, $category, $price, $quantity, $image]);

        $_SESSION['success'] = 'Product added successfully!';
        header('Location: list_items.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error adding product: ' . $e->getMessage();
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
                            placeholder="e.g., Lavender Dreams"
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
                            placeholder="Describe your candle..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    ></textarea>
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
                            <option value="Regular Candle">Regular Candle</option>
                            <option value="Boxed Set - Regular">Boxed Set - Regular</option>
                            <option value="Floral Candle">Floral Candle</option>
                            <option value="Festive Special">Festive Special</option>
                            <option value="Premium Set">Premium Set</option>
                            <option value="Gift Set">Gift Set</option>
                            <option value="Free Gift">Free Gift</option>
                            <option value="Scented">Scented</option>
                            <option value="Jewellery">Jewellery</option>
                            <option value="Animal and Birds">Animal and Birds</option>
                            <option value="Letters">Letters</option>
                            <option value="Food Candles">Food Candles</option>
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
                                accept="image/*"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-lavender file:text-gray-700 hover:file:bg-opacity-80"
                        />
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, or WEBP (Max 5MB)</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
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
                        <span>Add Product</span>
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