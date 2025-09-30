-- CELOSIA CANDLES POS Database Setup

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS celosia_pos;
CREATE DATABASE celosia_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE celosia_pos;

-- Categories Table (NEW)
CREATE TABLE categories
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Inventory Table
CREATE TABLE inventory
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255)   NOT NULL,
    description  TEXT,
    category     VARCHAR(100)   NOT NULL,
    price        DECIMAL(10, 2) NOT NULL,
    quantity     INT            NOT NULL DEFAULT 0,
    image        VARCHAR(255),
    created_at   TIMESTAMP               DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP               DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_product_name (product_name)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Sales Table
CREATE TABLE sales
(
    id               INT AUTO_INCREMENT PRIMARY KEY,
    customer_name    VARCHAR(255),
    customer_phone   VARCHAR(20),
    customer_email   VARCHAR(255),
    subtotal         DECIMAL(10, 2) NOT NULL,
    discount_percent DECIMAL(5, 2)  DEFAULT 0,
    discount_amount  DECIMAL(10, 2) DEFAULT 0,
    total_amount     DECIMAL(10, 2) NOT NULL,
    payment_method   VARCHAR(50)    NOT NULL,
    notes            TEXT,
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_name (customer_name),
    INDEX idx_payment_method (payment_method),
    INDEX idx_created_at (created_at)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Sales Items Table
CREATE TABLE sales_items
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sale_id      INT            NOT NULL,
    product_id   INT            NOT NULL,
    product_name VARCHAR(255)   NOT NULL,
    quantity     INT            NOT NULL,
    price        DECIMAL(10, 2) NOT NULL,
    subtotal     DECIMAL(10, 2) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES inventory (id),
    INDEX idx_sale_id (sale_id),
    INDEX idx_product_id (product_id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Create a view for low stock items
CREATE VIEW low_stock_items AS
SELECT id,
       product_name,
       category,
       quantity,
       price
FROM inventory
WHERE quantity < 10
ORDER BY quantity ASC;

-- Create a view for sales summary
CREATE VIEW sales_summary AS
SELECT DATE(created_at)     as sale_date,
       COUNT(*)             as total_sales,
       SUM(total_amount)    as total_revenue,
       SUM(discount_amount) as total_discounts,
       AVG(total_amount)    as avg_sale_value
FROM sales
GROUP BY DATE(created_at)
ORDER BY sale_date DESC;

-- Create a view for popular products
CREATE VIEW popular_products AS
SELECT i.id,
       i.product_name,
       i.category,
       i.price,
       COALESCE(SUM(si.quantity), 0) as total_sold,
       COALESCE(SUM(si.subtotal), 0) as total_revenue
FROM inventory i
         LEFT JOIN sales_items si ON i.id = si.product_id
GROUP BY i.id, i.product_name, i.category, i.price
ORDER BY total_sold DESC;

-- Show completion message
SELECT 'Database setup complete! CELOSIA CANDLES POS is ready to use.' as Status;
