-- THRIFTEDGH DATABASE
-- Initial schema for the custom-coded marketplace

CREATE DATABASE IF NOT EXISTS thriftedgh
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE thriftedgh;

-- 1. USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('buyer','seller','admin')
        NOT NULL DEFAULT 'buyer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. SELLER APPLICATIONS
CREATE TABLE seller_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    seller_type ENUM('individual','business') NOT NULL,
    shop_name VARCHAR(120) NOT NULL,
    location VARCHAR(150),
    business_details TEXT,
    status ENUM('pending','approved','rejected')
        NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- 3. SELLER PROFILES
CREATE TABLE seller_profiles (
    seller_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    shop_name VARCHAR(120) NOT NULL,
    seller_type ENUM('individual','business') NOT NULL,
    location VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- 4. CATEGORIES
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 5. PRODUCTS
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(30),
    item_condition ENUM('like_new','good','fair')
        NOT NULL,
    image_path VARCHAR(255),
    stock_quantity INT NOT NULL DEFAULT 1,
    status ENUM('available','reserved','sold','hidden')
        NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id)
        REFERENCES seller_profiles(seller_id),
    FOREIGN KEY (category_id)
        REFERENCES categories(category_id),
    CHECK (price >= 0),
    CHECK (stock_quantity IN (0,1))
) ENGINE=InnoDB;

-- 6. PRODUCT MEASUREMENTS
CREATE TABLE product_measurements (
    measurement_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL UNIQUE,
    chest_cm DECIMAL(6,2),
    waist_cm DECIMAL(6,2),
    hips_cm DECIMAL(6,2),
    inseam_cm DECIMAL(6,2),
    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. BUYER MEASUREMENTS
CREATE TABLE buyer_measurements (
    measurement_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    chest_cm DECIMAL(6,2),
    waist_cm DECIMAL(6,2),
    hips_cm DECIMAL(6,2),
    inseam_cm DECIMAL(6,2),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. CARTS
CREATE TABLE carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. CART ITEMS
CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    UNIQUE (cart_id, product_id),
    FOREIGN KEY (cart_id)
        REFERENCES carts(cart_id)
        ON DELETE CASCADE,
    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
) ENGINE=InnoDB;

-- 10. ORDERS
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_reference VARCHAR(100) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)
        REFERENCES users(user_id)
) ENGINE=InnoDB;

-- 11. ORDER ITEMS
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    fulfilment_status ENUM(
        'pending','processing','shipped','delivered','cancelled'
    ) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (order_id)
        REFERENCES orders(order_id),
    FOREIGN KEY (product_id)
        REFERENCES products(product_id),
    FOREIGN KEY (seller_id)
        REFERENCES seller_profiles(seller_id)
) ENGINE=InnoDB;

-- 12. PAYMENTS
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    provider_reference VARCHAR(150) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','success','failed')
        NOT NULL DEFAULT 'pending',
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
) ENGINE=InnoDB;

-- 13. SELLER EARNINGS
CREATE TABLE seller_earnings (
    earning_id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL UNIQUE,
    seller_id INT NOT NULL,
    gross_amount DECIMAL(10,2) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    seller_amount DECIMAL(10,2) NOT NULL,
    payout_status ENUM('pending','paid')
        NOT NULL DEFAULT 'pending',
    FOREIGN KEY (order_item_id)
        REFERENCES order_items(order_item_id),
    FOREIGN KEY (seller_id)
        REFERENCES seller_profiles(seller_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS product_garment_types (
    product_id INT NOT NULL PRIMARY KEY,
    garment_type ENUM('top','bottom','dress','outerwear') NOT NULL,
    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
