<?php

require_once __DIR__ . '/ShopQueryTrait.php';
require_once __DIR__ . '/StorefrontClass.php';

class ProductClass
{
    use ShopQueryTrait;

    const SIZES = [
        'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'One Size',
        '28', '30', '32', '34', '36', '38', '40', '42', '44', '46'
    ];

    const CONDITIONS = [
        'like_new' => 'Like new',
        'good' => 'Good',
        'fair' => 'Fair'
    ];

    const GARMENT_TYPES = [
        'top' => 'Top or shirt',
        'bottom' => 'Trousers, skirt or shorts',
        'dress' => 'Dress or jumpsuit',
        'outerwear' => 'Jacket, coat or hoodie'
    ];

    const REQUIRED_MEASUREMENTS = [
        'top' => ['chest'],
        'outerwear' => ['chest'],
        'bottom' => ['waist', 'hips'],
        'dress' => ['chest', 'waist', 'hips']
    ];

    const OPTIONAL_MEASUREMENTS = [
        'top' => [],
        'outerwear' => [],
        'bottom' => ['inseam'],
        'dress' => []
    ];

    const STATUS_LABELS = [
        'available' => 'Available',
        'reserved' => 'Reserved in a checkout',
        'sold' => 'Sold'
    ];

    public function getCategories()
    {
        try {
            return $this->fetchAll("SELECT category_id, category_name AS name FROM categories ORDER BY category_name ASC");
        } catch (Throwable $error) {
            error_log('ProductClass::getCategories ' . $error->getMessage());
            return [];
        }
    }

    public function addProduct($sellerId, array $data, $garmentType, array $measurements, $imageFile)
    {
        $connection = null;
        try {
            $connection = $this->conn();
            $connection->begin_transaction();

            $insert = $this->write(
                "INSERT INTO products
                    (seller_id, category_id, title, description, price, size, item_condition, image_path, stock_quantity, status)
                 SELECT sp.seller_id, ?, ?, ?, ?, ?, ?, ?, 1, 'available'
                 FROM seller_profiles sp
                 WHERE sp.seller_id = ? AND " . StorefrontClass::APPROVED_CONDITION,
                'issssssi',
                [
                    (int)$data['category_id'],
                    $data['title'],
                    $data['description'],
                    $data['price'],
                    $data['size'],
                    $data['item_condition'],
                    $imageFile,
                    (int)$sellerId
                ]
            );

            if ($insert['affected'] !== 1) {
                $connection->rollback();
                return ['ok' => false, 'error' => 'not_approved'];
            }

            $productId = (int)$insert['id'];
            $this->saveGarmentMeasurements($productId, $garmentType, $measurements);
            $connection->commit();
            return ['ok' => true, 'id' => $productId];
        } catch (Throwable $error) {
            if ($connection) {
                $connection->rollback();
            }
            error_log('ProductClass::addProduct ' . $error->getMessage());
            return ['ok' => false, 'error' => 'database'];
        }
    }

    public function updateProduct($productId, $sellerId, array $data, $garmentType, array $measurements, $newImageFile)
    {
        $connection = null;
        try {
            $connection = $this->conn();
            $connection->begin_transaction();

            $owned = $this->lockOwnedAvailable($productId, $sellerId);
            if (!$owned) {
                $connection->rollback();
                return ['ok' => false, 'error' => 'not_editable'];
            }

            $this->write(
                "UPDATE products
                 SET category_id = ?, title = ?, description = ?, price = ?, size = ?,
                     item_condition = ?, image_path = COALESCE(?, image_path)
                 WHERE product_id = ? AND seller_id = ? AND status = 'available'",
                'issssssii',
                [
                    (int)$data['category_id'],
                    $data['title'],
                    $data['description'],
                    $data['price'],
                    $data['size'],
                    $data['item_condition'],
                    $newImageFile,
                    (int)$productId,
                    (int)$sellerId
                ]
            );

            $this->saveGarmentMeasurements((int)$productId, $garmentType, $measurements);
            $connection->commit();
            return [
                'ok' => true,
                'old_image' => $newImageFile !== null ? $owned['image_path'] : null
            ];
        } catch (Throwable $error) {
            if ($connection) {
                $connection->rollback();
            }
            error_log('ProductClass::updateProduct ' . $error->getMessage());
            return ['ok' => false, 'error' => 'database'];
        }
    }

    public function removeProduct($productId, $sellerId)
    {
        $connection = null;
        try {
            $connection = $this->conn();
            $connection->begin_transaction();

            $owned = $this->lockOwnedAvailable($productId, $sellerId);
            if (!$owned) {
                $connection->rollback();
                return ['ok' => false, 'error' => 'not_editable'];
            }

            $this->write(
                "UPDATE products SET status = 'hidden'
                 WHERE product_id = ? AND seller_id = ? AND status = 'available'",
                'ii',
                [(int)$productId, (int)$sellerId]
            );
            $connection->commit();
            return ['ok' => true];
        } catch (Throwable $error) {
            if ($connection) {
                $connection->rollback();
            }
            error_log('ProductClass::removeProduct ' . $error->getMessage());
            return ['ok' => false, 'error' => 'database'];
        }
    }

    public function getProductsBySeller($sellerId)
    {
        try {
            return $this->fetchAll(
                "SELECT p.product_id, p.title, p.price, p.size, p.item_condition, p.image_path,
                        p.status, p.created_at, c.category_name, pg.garment_type
                 FROM products p
                 JOIN categories c ON c.category_id = p.category_id
                 LEFT JOIN product_garment_types pg ON pg.product_id = p.product_id
                 WHERE p.seller_id = ? AND p.status <> 'hidden'
                 ORDER BY p.created_at DESC, p.product_id DESC",
                'i',
                [(int)$sellerId]
            );
        } catch (Throwable $error) {
            error_log('ProductClass::getProductsBySeller ' . $error->getMessage());
            return [];
        }
    }

    public function getOwnedProduct($productId, $sellerId)
    {
        try {
            return $this->fetchOne(
                "SELECT p.product_id, p.seller_id, p.category_id, p.title, p.description, p.price,
                        p.size, p.item_condition, p.image_path, p.status,
                        pg.garment_type, pm.chest_cm, pm.waist_cm, pm.hips_cm, pm.inseam_cm
                 FROM products p
                 LEFT JOIN product_garment_types pg ON pg.product_id = p.product_id
                 LEFT JOIN product_measurements pm ON pm.product_id = p.product_id
                 WHERE p.product_id = ? AND p.seller_id = ? AND p.status <> 'hidden'
                 LIMIT 1",
                'ii',
                [(int)$productId, (int)$sellerId]
            );
        } catch (Throwable $error) {
            error_log('ProductClass::getOwnedProduct ' . $error->getMessage());
            return null;
        }
    }

    public function getAvailableBySeller($sellerId, $limit, $offset)
    {
        try {
            return $this->fetchAll(
                "SELECT p.product_id, p.title, p.price, p.size, p.item_condition, p.image_path,
                        c.category_name, sp.shop_name AS seller_name
                 FROM products p
                 JOIN categories c ON c.category_id = p.category_id
                 JOIN seller_profiles sp ON sp.seller_id = p.seller_id
                 WHERE p.seller_id = ? AND p.status = 'available' AND p.stock_quantity = 1
                 ORDER BY p.created_at DESC, p.product_id DESC
                 LIMIT ? OFFSET ?",
                'iii',
                [(int)$sellerId, (int)$limit, (int)$offset]
            );
        } catch (Throwable $error) {
            error_log('ProductClass::getAvailableBySeller ' . $error->getMessage());
            return [];
        }
    }

    public function countAvailableBySeller($sellerId)
    {
        try {
            $row = $this->fetchOne(
                "SELECT COUNT(*) AS total FROM products
                 WHERE seller_id = ? AND status = 'available' AND stock_quantity = 1",
                'i',
                [(int)$sellerId]
            );
            return $row ? (int)$row['total'] : 0;
        } catch (Throwable $error) {
            error_log('ProductClass::countAvailableBySeller ' . $error->getMessage());
            return 0;
        }
    }

    private function lockOwnedAvailable($productId, $sellerId)
    {
        return $this->fetchOne(
            "SELECT p.product_id, p.image_path
             FROM products p
             JOIN seller_profiles sp ON sp.seller_id = p.seller_id
             WHERE p.product_id = ? AND p.seller_id = ? AND p.status = 'available'
               AND p.stock_quantity = 1 AND " . StorefrontClass::APPROVED_CONDITION . "
             FOR UPDATE",
            'ii',
            [(int)$productId, (int)$sellerId]
        );
    }

    private function saveGarmentMeasurements($productId, $garmentType, array $measurements)
    {
        if ($garmentType === null || $garmentType === '') {
            $this->write("DELETE FROM product_measurements WHERE product_id = ?", 'i', [(int)$productId]);
            $this->write("DELETE FROM product_garment_types WHERE product_id = ?", 'i', [(int)$productId]);
            return;
        }
        $this->write(
            "INSERT INTO product_garment_types (product_id, garment_type) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE garment_type = VALUES(garment_type)",
            'is',
            [(int)$productId, $garmentType]
        );
        $this->write(
            "INSERT INTO product_measurements (product_id, chest_cm, waist_cm, hips_cm, inseam_cm)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE chest_cm = VALUES(chest_cm), waist_cm = VALUES(waist_cm),
                 hips_cm = VALUES(hips_cm), inseam_cm = VALUES(inseam_cm)",
            'issss',
            [
                (int)$productId,
                $measurements['chest'] ?? null,
                $measurements['waist'] ?? null,
                $measurements['hips'] ?? null,
                $measurements['inseam'] ?? null
            ]
        );
    }
}
