<?php
require_once '../config/db.php';
$pageTitle = 'New Sale - CELOSIA CANDLES';

// Get all available products
$stmt = $pdo->query("SELECT * FROM inventory WHERE quantity > 0 ORDER BY product_name ASC");
$products = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $notes = trim($_POST['notes'] ?? '');
    $discount_percent = floatval($_POST['discount_percent'] ?? 0);

    $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);

    if (empty($cart_items)) {
        $_SESSION['error'] = 'Please add items to the cart!';
    } else {
        try {
            $pdo->beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $discount_amount = ($subtotal * $discount_percent) / 100;
            $total_amount = $subtotal - $discount_amount;

            // Insert sale
            $stmt = $pdo->prepare("INSERT INTO sales (customer_name, customer_phone, customer_email, subtotal, discount_percent, discount_amount, total_amount, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customer_name, $customer_phone, $customer_email, $subtotal, $discount_percent, $discount_amount, $total_amount, $payment_method, $notes]);

            $sale_id = $pdo->lastInsertId();

            // Insert sale items and update inventory
            $stmt_item = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_update = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");

            foreach ($cart_items as $item) {
                $item_subtotal = $item['price'] * $item['quantity'];
                $stmt_item->execute([$sale_id, $item['id'], $item['name'], $item['quantity'], $item['price'], $item_subtotal]);
                $stmt_update->execute([$item['quantity'], $item['id']]);
            }

            $pdo->commit();

            $_SESSION['success'] = 'Sale completed successfully!';
            header('Location: invoice.php?id=' . $sale_id);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Error processing sale: ' . $e->getMessage();
        }
    }
}

include '../includes/header.inc.php';
?>

    <style>
        .cart-item {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800 mb-2">New Sale</h1>
        <p class="text-gray-600">Select products and complete the transaction</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Products Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h2 class="heading-font text-2xl font-bold text-gray-800 mb-4">Available Products</h2>

                <div class="mb-4">
                    <input
                            type="text"
                            id="product-search"
                            placeholder="Search products..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    />
                </div>

                <div id="products-grid"
                     class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card border border-gray-200 rounded-lg p-4 hover:border-gold transition-colors cursor-pointer"
                             data-id="<?php echo $product['id']; ?>"
                             data-name="<?php echo h($product['product_name']); ?>"
                             data-price="<?php echo $product['price']; ?>"
                             data-stock="<?php echo $product['quantity']; ?>"
                             data-search="<?php echo strtolower($product['product_name'] . ' ' . $product['category']); ?>">
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 bg-gradient-to-br from-lavender to-pastel-pink rounded-lg flex items-center justify-center flex-shrink-0">
                                    <?php if ($product['image'] && file_exists("../assets/images/products/" . $product['image'])): ?>
                                        <img src="../assets/images/products/<?php echo h($product['image']); ?>"
                                             alt="<?php echo h($product['product_name']); ?>"
                                             class="w-full h-full object-cover rounded-lg">
                                    <?php else: ?>
                                        <svg class="w-8 h-8 text-white"
                                             fill="currentColor"
                                             viewBox="0 0 24 24">
                                            <path d="M12 2C11.5 2 11 2.19 10.59 2.59L2.59 10.59C1.8 11.37 1.8 12.63 2.59 13.41L10.59 21.41C11.37 22.2 12.63 22.2 13.41 21.41L21.41 13.41C22.2 12.63 22.2 11.37 21.41 10.59L13.41 2.59C13 2.19 12.5 2 12 2M12 4L20 12L12 20L4 12L12 4M12 6L6 12L12 18L18 12L12 6Z"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-800 truncate"><?php echo h($product['product_name']); ?></h3>
                                    <p class="text-xs text-gray-500"><?php echo h($product['category']); ?></p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-lg font-bold text-gold">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="text-xs text-gray-500">Stock: <?php echo $product['quantity']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                <h2 class="heading-font text-2xl font-bold text-gray-800 mb-4 flex items-center justify-between">
                    <span>Cart</span>
                    <button id="clear-cart"
                            class="text-sm text-red-500 hover:text-red-700">Clear
                    </button>
                </h2>

                <div id="cart-items"
                     class="space-y-3 mb-4 max-h-[300px] overflow-y-auto">
                    <p class="text-gray-400 text-center py-8">Cart is empty</p>
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span id="subtotal"
                              class="font-semibold">₹0.00</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Discount:</label>
                        <input type="number"
                               id="discount-percent"
                               min="0"
                               max="100"
                               value="0"
                               step="0.1"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gold">
                        <span class="text-sm text-gray-600">%</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount Amount:</span>
                        <span id="discount-amount"
                              class="font-semibold text-red-500">-₹0.00</span>
                    </div>

                    <div class="flex justify-between text-lg border-t pt-3">
                        <span class="font-bold text-gray-800">Total:</span>
                        <span id="total"
                              class="font-bold text-gold">₹0.00</span>
                    </div>

                    <button id="checkout-btn"
                            disabled
                            class="w-full bg-gold hover:bg-gold-dark text-white py-3 rounded-lg font-semibold transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkout-modal"
         class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="heading-font text-2xl font-bold text-gray-800">Complete Sale</h2>
                    <button id="close-modal"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="checkout-form"
                      method="POST">
                    <input type="hidden"
                           name="cart_items"
                           id="cart-items-input">
                    <input type="hidden"
                           name="discount_percent"
                           id="discount-input">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Customer Name</label>
                            <input type="text"
                                   name="customer_name"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                            <input type="tel"
                                   name="customer_phone"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email</label>
                            <input type="email"
                                   name="customer_email"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Payment Method</label>
                            <select name="payment_method"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="UPI">UPI</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Notes (Optional)</label>
                            <textarea name="notes"
                                      rows="2"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold"></textarea>
                        </div>

                        <div class="bg-lavender rounded-lg p-4">
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-700">Subtotal:</span>
                                <span id="modal-subtotal"
                                      class="font-semibold">₹0.00</span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-700">Discount:</span>
                                <span id="modal-discount"
                                      class="font-semibold text-red-500">-₹0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-300 pt-2 mt-2">
                                <span>Total:</span>
                                <span id="modal-total"
                                      class="text-gold">₹0.00</span>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-gold hover:bg-gold-dark text-white py-3 rounded-lg font-semibold transition-colors shadow-md">
                            Complete Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        // Product click handler
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function () {
                const product = {
                    id: parseInt(this.dataset.id),
                    name: this.dataset.name,
                    price: parseFloat(this.dataset.price),
                    stock: parseInt(this.dataset.stock),
                    quantity: 1
                };

                addToCart(product);
            });
        });

        // Add to cart
        function addToCart(product) {
            const existing = cart.find(item => item.id === product.id);

            if (existing) {
                if (existing.quantity < product.stock) {
                    existing.quantity++;
                } else {
                    alert('Not enough stock!');
                    return;
                }
            } else {
                cart.push(product);
            }

            renderCart();
        }

        // Remove from cart
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            renderCart();
        }

        // Update quantity
        function updateQuantity(productId, change) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                const newQty = item.quantity + change;
                if (newQty > 0 && newQty <= item.stock) {
                    item.quantity = newQty;
                    renderCart();
                } else if (newQty <= 0) {
                    removeFromCart(productId);
                }
            }
        }

        // Render cart
        function renderCart() {
            const container = document.getElementById('cart-items');

            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-center py-8">Cart is empty</p>';
                document.getElementById('checkout-btn').disabled = true;
            } else {
                container.innerHTML = cart.map(item => `
            <div class="cart-item flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-sm text-gray-800 truncate">${item.name}</h4>
                    <p class="text-xs text-gray-500">₹${item.price.toFixed(2)} each</p>
                </div>
                <div class="flex items-center space-x-2 ml-2">
                    <button onclick="updateQuantity(${item.id}, -1)" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-bold">-</button>
                    <span class="w-8 text-center font-semibold">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-bold">+</button>
                    <button onclick="removeFromCart(${item.id})" class="w-7 h-7 bg-red-100 hover:bg-red-200 rounded text-red-600">×</button>
                </div>
            </div>
        `).join('');
                document.getElementById('checkout-btn').disabled = false;
            }

            updateTotals();
        }

        // Update totals
        function updateTotals() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountPercent = parseFloat(document.getElementById('discount-percent').value) || 0;
            const discountAmount = (subtotal * discountPercent) / 100;
            const total = subtotal - discountAmount;

            document.getElementById('subtotal').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('discount-amount').textContent = '-₹' + discountAmount.toFixed(2);
            document.getElementById('total').textContent = '₹' + total.toFixed(2);
        }

        // Discount change handler
        document.getElementById('discount-percent').addEventListener('input', updateTotals);

        // Clear cart
        document.getElementById('clear-cart').addEventListener('click', function () {
            if (confirm('Clear all items from cart?')) {
                cart = [];
                renderCart();
            }
        });

        // Product search
        document.getElementById('product-search').addEventListener('input', function (e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const searchText = card.dataset.search;
                card.style.display = searchText.includes(search) ? 'block' : 'none';
            });
        });

        // Checkout button
        document.getElementById('checkout-btn').addEventListener('click', function () {
            if (cart.length === 0) return;

            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discountPercent = parseFloat(document.getElementById('discount-percent').value) || 0;
            const discountAmount = (subtotal * discountPercent) / 100;
            const total = subtotal - discountAmount;

            document.getElementById('modal-subtotal').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('modal-discount').textContent = '-₹' + discountAmount.toFixed(2);
            document.getElementById('modal-total').textContent = '₹' + total.toFixed(2);

            document.getElementById('cart-items-input').value = JSON.stringify(cart);
            document.getElementById('discount-input').value = discountPercent;

            document.getElementById('checkout-modal').classList.remove('hidden');
        });

        // Close modal
        document.getElementById('close-modal').addEventListener('click', function () {
            document.getElementById('checkout-modal').classList.add('hidden');
        });

        // Close modal on outside click
        document.getElementById('checkout-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>

<?php include '../includes/footer.inc.php'; ?>