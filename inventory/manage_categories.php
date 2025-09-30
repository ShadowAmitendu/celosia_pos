<?php
require_once '../config/db.php';
$pageTitle = 'Manage Categories - CELOSIA CANDLES';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $new_category = trim($_POST['category_name'] ?? '');

    if (empty($new_category)) {
        $_SESSION['error'] = 'Category name cannot be empty';
    } elseif (strlen($new_category) > 100) {
        $_SESSION['error'] = 'Category name is too long (max 100 characters)';
    } else {
        try {
            // Check if category already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$new_category]);

            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'Category already exists';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, created_at) VALUES (?, NOW())");
                $stmt->execute([$new_category]);
                $_SESSION['success'] = 'Category added successfully';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding category: ' . $e->getMessage();
        }
    }
    header('Location: manage_categories.php');
    exit;
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        // Check if category is in use
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE category = (SELECT name FROM categories WHERE id = ?)");
        $stmt->execute([$id]);
        $usage_count = $stmt->fetchColumn();

        if ($usage_count > 0) {
            $_SESSION['error'] = 'Cannot delete category! It is used by ' . $usage_count . ' product(s)';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Category deleted successfully';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting category: ' . $e->getMessage();
    }
    header('Location: manage_categories.php');
    exit;
}

// Get all categories with usage count
try {
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.created_at,
            COUNT(i.id) as product_count
        FROM categories c
        LEFT JOIN inventory i ON c.name = i.category
        GROUP BY c.id, c.name, c.created_at
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading categories: ' . $e->getMessage();
    $categories = [];
}

include '../includes/header.inc.php';
?>

    <!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 alert-auto-hide shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2"
                 fill="currentColor"
                 viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"/>
            </svg>
            <p class="font-medium"><?php echo h($_SESSION['success']);
                unset($_SESSION['success']); ?></p>
        </div>
    </div>
<?php endif; ?>

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
            <p class="font-medium"><?php echo h($_SESSION['error']);
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
            <h1 class="heading-font text-3xl md:text-4xl font-bold text-gray-800">Manage Categories</h1>
        </div>
        <p class="text-gray-600">Add, edit, or remove product categories</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Category Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                <h2 class="heading-font text-xl font-bold text-gray-800 mb-4">Add New Category</h2>

                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Category Name
                        </label>
                        <input
                                type="text"
                                name="category_name"
                                required
                                maxlength="100"
                                placeholder="e.g., Wedding Special"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        />
                    </div>

                    <button
                            type="submit"
                            name="add_category"
                            class="w-full bg-gold hover:bg-gold-dark text-white py-3 px-6 rounded-lg font-semibold transition-colors shadow-md flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>Add Category</span>
                    </button>
                </form>

                <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0"
                             fill="currentColor"
                             viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <strong>Note:</strong> Categories in use by products cannot be deleted.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="heading-font text-xl font-bold text-gray-800">All Categories
                        (<?php echo count($categories); ?>)</h2>
                </div>

                <?php if (empty($categories)): ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <p class="text-gray-500">No categories yet. Add your first category!</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($categories as $category): ?>
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                            <?php echo h($category['name']); ?>
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <?php echo $category['product_count']; ?> product(s)
                                        </span>
                                            <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Added <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                                        </span>
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <?php if ($category['product_count'] > 0): ?>
                                            <button
                                                    disabled
                                                    class="bg-gray-200 text-gray-400 px-4 py-2 rounded-lg font-medium text-sm cursor-not-allowed"
                                                    title="Cannot delete - category is in use"
                                            >
                                                In Use
                                            </button>
                                        <?php else: ?>
                                            <a
                                                    href="?delete=<?php echo $category['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this category?')"
                                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                                            >
                                                Delete
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include '../includes/footer.inc.php'; ?>