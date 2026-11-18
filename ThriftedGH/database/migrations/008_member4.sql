-- Apply once to an existing database; no existing data is replaced.
ALTER TABLE orders
 ADD COLUMN reservation_expires_at DATETIME NULL,
 ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'GHS',
 ADD COLUMN paid_at DATETIME NULL,
 ADD COLUMN payment_review TINYINT(1) NOT NULL DEFAULT 0,
 ADD INDEX idx_pending_expiry (payment_status, reservation_expires_at);
ALTER TABLE products ADD COLUMN reservation_order_id INT NULL,
 ADD INDEX idx_reservation_order (reservation_order_id);
ALTER TABLE order_items ADD COLUMN title_at_purchase VARCHAR(150) NULL,
 ADD UNIQUE KEY uq_order_product (order_id, product_id);
