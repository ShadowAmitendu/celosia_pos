<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CELOSIA CANDLES POS'; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FFF8F0',
                        lavender: '#E6E6FA',
                        pastel: {
                            pink: '#FFD1DC',
                            blue: '#B4D7E8',
                            mint: '#D4F1E8'
                        },
                        gold: '#D4AF37',
                        'gold-dark': '#B8941F'
                    }
                },
                fontFamily: {
                    'heading': ['Inter', 'sans-serif'],        // optional custom heading font
                    'mono-custom': ['JetBrains Mono', 'monospace'] // monospace
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
          rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .heading-font {
            font-family: 'Inter Display', sans-serif;
        }

        code, pre, kbd, samp, .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-cream min-h-screen">

<!-- Navigation Bar -->
<nav class="bg-gradient-to-r from-lavender to-pastel-pink shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
            <!-- Logo & Brand -->
            <?php
            // Determine the correct path to index.php based on current location
            $rootPath = '';
            if (strpos($_SERVER['PHP_SELF'], '/inventory/') !== false ||
                    strpos($_SERVER['PHP_SELF'], '/sales/') !== false) {
                $rootPath = '../';
            }
            ?>
            <a href="<?php echo $rootPath; ?>index.php"
               class="flex items-center space-x-3 group">
                <div class="bg-white rounded-full p-2 shadow-md group-hover:shadow-xl transition-shadow">
                    <svg class="w-10 h-10 text-gold"
                         fill="currentColor"
                         viewBox="0 0 24 24">
                        <path d="M12 2C11.5 2 11 2.19 10.59 2.59L2.59 10.59C1.8 11.37 1.8 12.63 2.59 13.41L10.59 21.41C11.37 22.2 12.63 22.2 13.41 21.41L21.41 13.41C22.2 12.63 22.2 11.37 21.41 10.59L13.41 2.59C13 2.19 12.5 2 12 2M12 4L20 12L12 20L4 12L12 4M12 6L6 12L12 18L18 12L12 6Z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="heading-font text-2xl font-bold text-gray-800">@CELOSIACANDLES</h1>
                    <p class="text-xs text-gray-600">Handcrafted with Love</p>
                </div>
            </a>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="<?php echo $rootPath; ?>index.php"
                   class="text-gray-700 hover:text-gold font-medium transition-colors flex items-center space-x-1">
                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="<?php echo $rootPath; ?>inventory/list_items.php"
                   class="text-gray-700 hover:text-gold font-medium transition-colors flex items-center space-x-1">
                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Inventory</span>
                </a>

                <a href="<?php echo $rootPath; ?>sales/new_sale.php"
                   class="bg-gold hover:bg-gold-dark text-white px-6 py-2 rounded-full font-semibold transition-colors shadow-md flex items-center space-x-2">
                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Sale</span>
                </a>

                <a href="<?php echo $rootPath; ?>sales/sales_history.php"
                   class="text-gray-700 hover:text-gold font-medium transition-colors flex items-center space-x-1">
                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Sales History</span>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn"
                    class="md:hidden text-gray-700">
                <svg class="w-6 h-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
             class="hidden md:hidden pb-4 space-y-2">
            <a href="<?php echo $rootPath; ?>index.php"
               class="block py-2 px-4 text-gray-700 hover:bg-white rounded-lg transition-colors">Dashboard</a>
            <a href="<?php echo $rootPath; ?>inventory/list_items.php"
               class="block py-2 px-4 text-gray-700 hover:bg-white rounded-lg transition-colors">Inventory</a>
            <a href="<?php echo $rootPath; ?>sales/new_sale.php"
               class="block py-2 px-4 text-gray-700 hover:bg-white rounded-lg transition-colors">New Sale</a>
            <a href="<?php echo $rootPath; ?>sales/sales_history.php"
               class="block py-2 px-4 text-gray-700 hover:bg-white rounded-lg transition-colors">Sales History</a>
        </div>
    </div>
</nav>

<!-- Main Content Wrapper -->
<main class="container mx-auto px-4 py-8">
