<?php

require_once __DIR__ . '/ProductClass.php';

class FitClass
{
    use ShopQueryTrait;

    const BODY_RANGES = [
        'chest' => [50, 200],
        'waist' => [40, 200],
        'hips' => [50, 220],
        'inseam' => [40, 120]
    ];

    const ALLOWANCES = [
        'top' => [
            'chest' => ['min' => 2, 'max' => 16, 'ideal_min' => 6, 'ideal_max' => 12]
        ],
        'outerwear' => [
            'chest' => ['min' => 8, 'max' => 24, 'ideal_min' => 12, 'ideal_max' => 20]
        ],
        'bottom' => [
            'waist' => ['min' => -2, 'max' => 6, 'ideal_min' => 0, 'ideal_max' => 3],
            'hips' => ['min' => 2, 'max' => 14, 'ideal_min' => 4, 'ideal_max' => 10],
            'inseam' => ['min' => -4, 'max' => 4, 'ideal_min' => -2, 'ideal_max' => 2]
        ],
        'dress' => [
            'chest' => ['min' => 2, 'max' => 16, 'ideal_min' => 4, 'ideal_max' => 12],
            'waist' => ['min' => 0, 'max' => 20, 'ideal_min' => 4, 'ideal_max' => 14],
            'hips' => ['min' => 2, 'max' => 18, 'ideal_min' => 4, 'ideal_max' => 12]
        ]
    ];

    const REQUIRED = [
        'top' => ['chest'],
        'outerwear' => ['chest'],
        'bottom' => ['waist', 'hips'],
        'dress' => ['chest', 'waist', 'hips']
    ];

    const TYPE_LABELS = [
        'top' => 'Tops',
        'bottom' => 'Trousers, skirts and shorts',
        'dress' => 'Dresses',
        'outerwear' => 'Jackets and coats'
    ];

    public function getMeasurements($userId)
    {
        try {
            return $this->fetchOne(
                "SELECT chest_cm, waist_cm, hips_cm, inseam_cm, updated_at
                 FROM buyer_measurements WHERE user_id = ? LIMIT 1",
                'i',
                [(int)$userId]
            );
        } catch (Throwable $error) {
            error_log('FitClass::getMeasurements ' . $error->getMessage());
            return null;
        }
    }

    public function saveMeasurements($userId, array $values)
    {
        try {
            $this->write(
                "INSERT INTO buyer_measurements (user_id, chest_cm, waist_cm, hips_cm, inseam_cm)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE chest_cm = VALUES(chest_cm), waist_cm = VALUES(waist_cm),
                     hips_cm = VALUES(hips_cm), inseam_cm = VALUES(inseam_cm)",
                'issss',
                [
                    (int)$userId,
                    $values['chest'] ?? null,
                    $values['waist'] ?? null,
                    $values['hips'] ?? null,
                    $values['inseam'] ?? null
                ]
            );
            return true;
        } catch (Throwable $error) {
            error_log('FitClass::saveMeasurements ' . $error->getMessage());
            return false;
        }
    }

    public function deleteMeasurements($userId)
    {
        try {
            $this->write("DELETE FROM buyer_measurements WHERE user_id = ?", 'i', [(int)$userId]);
            return true;
        } catch (Throwable $error) {
            error_log('FitClass::deleteMeasurements ' . $error->getMessage());
            return false;
        }
    }

    public function findMatches($userId, array $body, $garmentType = null, $limit = 60)
    {
        try {
            $sql = "SELECT p.product_id, p.title, p.price, p.size, p.item_condition, p.image_path,
                           sp.seller_id, sp.shop_name, sp.shop_name AS seller_name, c.category_name,
                           pg.garment_type, pm.chest_cm, pm.waist_cm, pm.hips_cm, pm.inseam_cm
                    FROM products p
                    JOIN product_garment_types pg ON pg.product_id = p.product_id
                    JOIN product_measurements pm ON pm.product_id = p.product_id
                    JOIN seller_profiles sp ON sp.seller_id = p.seller_id
                    JOIN categories c ON c.category_id = p.category_id
                    WHERE p.status = 'available' AND p.stock_quantity = 1
                      AND " . ProductClass::APPROVED_CONDITION . "
                      AND sp.user_id <> ?";
            $types = 'i';
            $params = [(int)$userId];
            if ($garmentType !== null && $garmentType !== '') {
                $sql .= " AND pg.garment_type = ?";
                $types .= 's';
                $params[] = $garmentType;
            }
            $sql .= " ORDER BY p.created_at DESC, p.product_id DESC LIMIT 500";

            $matches = [];
            foreach ($this->fetchAll($sql, $types, $params) as $row) {
                $garment = [
                    'chest' => $row['chest_cm'],
                    'waist' => $row['waist_cm'],
                    'hips' => $row['hips_cm'],
                    'inseam' => $row['inseam_cm']
                ];
                $fit = self::evaluateFit($row['garment_type'], $body, $garment);
                if ($fit !== null) {
                    $row['fit'] = $fit;
                    $matches[] = $row;
                }
            }

            usort($matches, function ($a, $b) {
                return $b['fit']['score'] <=> $a['fit']['score'];
            });

            return array_slice($matches, 0, (int)$limit);
        } catch (Throwable $error) {
            error_log('FitClass::findMatches ' . $error->getMessage());
            return [];
        }
    }

    public static function missingFor($garmentType, array $body)
    {
        $missing = [];
        foreach (self::REQUIRED[$garmentType] ?? [] as $dimension) {
            if (!isset($body[$dimension]) || $body[$dimension] === '' || $body[$dimension] === null) {
                $missing[] = $dimension;
            }
        }
        return $missing;
    }

    public static function evaluateFit($garmentType, array $body, array $garment)
    {
        if (!isset(self::ALLOWANCES[$garmentType])) {
            return null;
        }
        if (self::missingFor($garmentType, $body)) {
            return null;
        }

        $points = 0;
        $compared = 0;
        $details = [];

        foreach (self::ALLOWANCES[$garmentType] as $dimension => $rule) {
            $bodyValue = $body[$dimension] ?? null;
            $garmentValue = $garment[$dimension] ?? null;
            if ($bodyValue === null || $bodyValue === '' || $garmentValue === null || $garmentValue === '') {
                if (in_array($dimension, self::REQUIRED[$garmentType], true)) {
                    return null;
                }
                continue;
            }

            $difference = round((float)$garmentValue - (float)$bodyValue, 1);
            if ($difference < $rule['min'] || $difference > $rule['max']) {
                return null;
            }

            $compared++;
            if ($difference >= $rule['ideal_min'] && $difference <= $rule['ideal_max']) {
                $points += 2;
                $feel = 'comfortable';
            } else {
                $points += 1;
                $feel = $difference < $rule['ideal_min'] ? 'snug' : 'roomy';
            }

            $details[] = [
                'dimension' => $dimension,
                'garment' => (float)$garmentValue,
                'body' => (float)$bodyValue,
                'difference' => $difference,
                'feel' => $feel
            ];
        }

        if ($compared === 0) {
            return null;
        }

        $score = (int)round(($points / ($compared * 2)) * 100);
        if ($score >= 85) {
            $label = 'Great match';
        } elseif ($score >= 60) {
            $label = 'Good match';
        } else {
            $label = 'Possible match';
        }

        return ['score' => $score, 'label' => $label, 'details' => $details];
    }
}
