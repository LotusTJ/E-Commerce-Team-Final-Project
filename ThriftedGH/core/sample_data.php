<?php
/**
 * ThriftedGH - Sample Data for Frontend Mockup (Task 2)
 *
 * NOTE: This file provides sample products, categories, and sellers for
 * frontend layout and styling purposes before the MySQL database connection
 * is implemented in subsequent tasks.
 *
 * Fields match the column definitions in database/thriftedgh.sql.
 */

// Sample Categories
$sampleCategories = [
    1 => 'Jackets & Coats',
    2 => 'T-Shirts & Tops',
    3 => 'Trousers & Jeans',
    4 => 'Dresses & Skirts',
    5 => 'Shirts',
    6 => 'Knitwear & Sweaters',
    7 => 'Footwear & Accessories'
];

// Sample Sellers (Matching seller_profiles table)
$sampleSellers = [
    1 => [
        'seller_id'    => 1,
        'shop_name'    => 'Accra Vintage Vault',
        'seller_type'  => 'business',
        'location'     => 'Osu, Accra',
        'description'  => 'Curating rare 80s & 90s vintage windbreakers, jackets, and retro streetwear in Greater Accra.',
        'rating'       => '4.9',
        'items_count'  => 18
    ],
    2 => [
        'seller_id'    => 2,
        'shop_name'    => 'Campus Thrift Hub',
        'seller_type'  => 'individual',
        'location'     => 'Legon Campus, Accra',
        'description'  => 'University student running a curated collection of oversized graphic tees and trendy denim.',
        'rating'       => '4.8',
        'items_count'  => 12
    ],
    3 => [
        'seller_id'    => 3,
        'shop_name'    => 'Kantamanto Archive',
        'seller_type'  => 'business',
        'location'     => 'CBD, Accra',
        'description'  => 'Direct hand-picked one-of-one gems from the heart of Kantamanto market. Washed and ready to wear.',
        'rating'       => '5.0',
        'items_count'  => 24
    ],
    4 => [
        'seller_id'    => 4,
        'shop_name'    => 'Kumasi Threads & Kicks',
        'seller_type'  => 'individual',
        'location'     => 'KNUST Campus, Kumasi',
        'description'  => 'Student thrifter specializing in casual vintage shirts, cargo trousers, and clean streetwear.',
        'rating'       => '4.7',
        'items_count'  => 9
    ]
];

// Sample Products (Matching products and product_measurements tables)
$sampleProducts = [
    1 => [
        'product_id'     => 1,
        'title'          => '90s Vintage Oversized Denim Jacket',
        'category_id'    => 1,
        'category_name'  => 'Jackets & Coats',
        'price'          => 220.00,
        'size'           => 'L',
        'item_condition' => 'like_new',
        'condition_label'=> 'Like New',
        'seller_id'      => 1,
        'seller_name'    => 'Accra Vintage Vault',
        'image_path'     => 'images/products/denim-jacket.svg',
        'description'    => 'Authentic 90s vintage medium-wash oversized denim jacket with heavy brass buttons and twin chest pockets. Perfectly broken-in denim with no stains or tears. Pre-washed and sanitized.',
        'measurements'   => [
            'chest_cm' => 58.0,
            'waist_cm' => 54.0,
            'hips_cm'  => null,
            'inseam_cm'=> null
        ],
        'is_featured'    => true
    ],
    2 => [
        'product_id'     => 2,
        'title'          => 'Retro Graphic Band Tee (Tour Edition)',
        'category_id'    => 2,
        'category_name'  => 'T-Shirts & Tops',
        'price'          => 95.00,
        'size'           => 'M',
        'item_condition' => 'good',
        'condition_label'=> 'Good',
        'seller_id'      => 2,
        'seller_name'    => 'Campus Thrift Hub',
        'image_path'     => 'images/products/graphic-tee.svg',
        'description'    => 'Heavyweight cotton graphic band tee with a tasteful vintage fade. Single stitch hem detailing. Soft fabric feel with minimal wash wear. Great everyday casual streetwear piece.',
        'measurements'   => [
            'chest_cm' => 52.0,
            'waist_cm' => 50.0,
            'hips_cm'  => null,
            'inseam_cm'=> null
        ],
        'is_featured'    => true
    ],
    3 => [
        'product_id'     => 3,
        'title'          => 'Pleated Wide-Leg Corduroy Trousers',
        'category_id'    => 3,
        'category_name'  => 'Trousers & Jeans',
        'price'          => 150.00,
        'size'           => '32',
        'item_condition' => 'like_new',
        'condition_label'=> 'Like New',
        'seller_id'      => 3,
        'seller_name'    => 'Kantamanto Archive',
        'image_path'     => 'images/products/corduroy-pants.svg',
        'description'    => 'Deep olive brown corduroy trousers featuring double pleats, tapered wide legs, and reinforced pocket hems. Thick, high-grade cord fabric in immaculate condition.',
        'measurements'   => [
            'chest_cm' => null,
            'waist_cm' => 82.0,
            'hips_cm'  => 102.0,
            'inseam_cm'=> 76.0
        ],
        'is_featured'    => true
    ],
    4 => [
        'product_id'     => 4,
        'title'          => 'Floral Pattern Silk Slip Midi Dress',
        'category_id'    => 4,
        'category_name'  => 'Dresses & Skirts',
        'price'          => 130.00,
        'size'           => 'S',
        'item_condition' => 'good',
        'condition_label'=> 'Good',
        'seller_id'      => 2,
        'seller_name'    => 'Campus Thrift Hub',
        'image_path'     => 'images/products/slip-dress.svg',
        'description'    => 'Elegant 90s inspired vintage slip dress with subtle floral print, adjustable spaghetti straps, and a side-slit detail. Fluid silhouette suitable for warm Ghanaian weather.',
        'measurements'   => [
            'chest_cm' => 44.0,
            'waist_cm' => 40.0,
            'hips_cm'  => 48.0,
            'inseam_cm'=> null
        ],
        'is_featured'    => true
    ],
    5 => [
        'product_id'     => 5,
        'title'          => 'Casual Striped Oxford Button-Down Shirt',
        'category_id'    => 5,
        'category_name'  => 'Shirts',
        'price'          => 85.00,
        'size'           => 'L',
        'item_condition' => 'like_new',
        'condition_label'=> 'Like New',
        'seller_id'      => 4,
        'seller_name'    => 'Kumasi Threads & Kicks',
        'image_path'     => 'images/products/oxford-shirt.svg',
        'description'    => 'Classic preppy blue and white striped Oxford cotton shirt with button-down collar and chest pocket. Crisp fabric, freshly pressed, all original buttons intact.',
        'measurements'   => [
            'chest_cm' => 56.0,
            'waist_cm' => 53.0,
            'hips_cm'  => null,
            'inseam_cm'=> null
        ],
        'is_featured'    => false
    ],
    6 => [
        'product_id'     => 6,
        'title'          => 'Vintage Colorblock Sport Windbreaker',
        'category_id'    => 1,
        'category_name'  => 'Jackets & Coats',
        'price'          => 180.00,
        'size'           => 'XL',
        'item_condition' => 'good',
        'condition_label'=> 'Good',
        'seller_id'      => 1,
        'seller_name'    => 'Accra Vintage Vault',
        'image_path'     => 'images/products/windbreaker.svg',
        'description'    => 'Bold 80s colorblock nylon windbreaker with teal, purple, and cream panels. Features elasticated cuffs, high zip collar, and breathable mesh lining. Excellent retro vibe.',
        'measurements'   => [
            'chest_cm' => 62.0,
            'waist_cm' => 58.0,
            'hips_cm'  => null,
            'inseam_cm'=> null
        ],
        'is_featured'    => false
    ],
    7 => [
        'product_id'     => 7,
        'title'          => 'Relaxed Multi-Pocket Khaki Cargo Trousers',
        'category_id'    => 3,
        'category_name'  => 'Trousers & Jeans',
        'price'          => 140.00,
        'size'           => '34',
        'item_condition' => 'good',
        'condition_label'=> 'Good',
        'seller_id'      => 4,
        'seller_name'    => 'Kumasi Threads & Kicks',
        'image_path'     => 'images/products/cargo-pants.svg',
        'description'    => 'Durable cotton twill utility cargo pants with 6 functional pockets, adjustable drawstring cuffs, and a comfortable relaxed skate fit.',
        'measurements'   => [
            'chest_cm' => null,
            'waist_cm' => 88.0,
            'hips_cm'  => 108.0,
            'inseam_cm'=> 78.0
        ],
        'is_featured'    => false
    ],
    8 => [
        'product_id'     => 8,
        'title'          => 'Chunky Knit Geometric Pattern Sweater',
        'category_id'    => 6,
        'category_name'  => 'Knitwear & Sweaters',
        'price'          => 125.00,
        'size'           => 'M',
        'item_condition' => 'fair',
        'condition_label'=> 'Fair',
        'seller_id'      => 3,
        'seller_name'    => 'Kantamanto Archive',
        'image_path'     => 'images/products/knit-sweater.svg',
        'description'    => 'Vintage wool-blend knit sweater with earth-tone geometric patterns. Normal slight surface fuzzing consistent with vintage knitwear. Warm, cozy, and distinctive.',
        'measurements'   => [
            'chest_cm' => 54.0,
            'waist_cm' => 50.0,
            'hips_cm'  => null,
            'inseam_cm'=> null
        ],
        'is_featured'    => false
    ]
];
