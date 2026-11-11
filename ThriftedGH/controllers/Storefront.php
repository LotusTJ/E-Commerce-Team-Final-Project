<?php

require_once __DIR__ . '/../classes/StorefrontClass.php';
require_once __DIR__ . '/../classes/ProductClass.php';

const STOREFRONT_PAGE_SIZE = 24;

function storefront_page_ctr($sellerId, $page)
{
    if (!is_scalar($sellerId) || !preg_match('/^\d{1,10}$/', (string)$sellerId) || (int)$sellerId < 1) {
        return null;
    }
    $sellerId = (int)$sellerId;
    if (is_array($page)) {
        $page = 1;
    }

    $seller = (new StorefrontClass())->getApprovedSellerById($sellerId);
    if (!$seller) {
        return null;
    }

    $products = new ProductClass();
    $total = $products->countAvailableBySeller($sellerId);
    $pages = max(1, (int)ceil($total / STOREFRONT_PAGE_SIZE));
    $page = min(max(1, (int)$page), $pages);

    return [
        'seller' => $seller,
        'products' => $products->getAvailableBySeller($sellerId, STOREFRONT_PAGE_SIZE, ($page - 1) * STOREFRONT_PAGE_SIZE),
        'total' => $total,
        'page' => $page,
        'pages' => $pages
    ];
}
