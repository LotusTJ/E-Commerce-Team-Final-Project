CREATE TABLE IF NOT EXISTS product_garment_types (
    product_id INT NOT NULL PRIMARY KEY,
    garment_type ENUM('top','bottom','dress','outerwear') NOT NULL,
    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
