<?php
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/../classes/SellerClass.php';
require_once __DIR__ . '/../core/auth.php';

function application_validate_post(array $post)
{
    $errors = [];
    $type = trim(tgh_input_string($post, 'seller_type'));
    $shopName = trim(tgh_input_string($post, 'shop_name'));
    $location = trim(tgh_input_string($post, 'location'));
    $details = trim(tgh_input_string($post, 'business_details'));
    $phone = trim(tgh_input_string($post, 'phone'));

    if (!array_key_exists($type, SellerClass::TYPES)) {
        $errors[] = 'Choose Individual or Business.';
    }
    $nameLength = mb_strlen($shopName);
    if ($nameLength < 2 || $nameLength > 120) {
        $errors[] = 'Shop name must be 2 to 120 characters.';
    }
    $locationLength = mb_strlen($location);
    if ($locationLength < 2 || $locationLength > 150) {
        $errors[] = 'Location must be 2 to 150 characters.';
    }
    $detailsLength = mb_strlen($details);
    if ($detailsLength < 10 || $detailsLength > 1000) {
        $errors[] = 'Tell us about your shop in 10 to 1000 characters.';
    }
    if ($phone !== '' && !preg_match('/^\+?[0-9][0-9 ()-]{6,18}$/', $phone)) {
        $errors[] = 'Enter a valid contact phone number or leave it blank.';
    }

    return [
        'errors' => $errors,
        'values' => [
            'seller_type' => $type,
            'shop_name' => $shopName,
            'location' => $location,
            'business_details' => $details,
            'phone' => $phone
        ]
    ];
}

function application_submit_ctr($userId, array $post)
{
    $checked = application_validate_post($post);
    if ($checked['errors']) {
        return ['ok' => false, 'errors' => $checked['errors']];
    }
    $values = $checked['values'];
    $seller = new SellerClass();
    if ($seller->shopNameTaken($values['shop_name'], $userId)) {
        return ['ok' => false, 'errors' => ['That shop name is already taken. Choose another.']];
    }
    $result = $seller->submitApplication(
        $userId,
        $values['seller_type'],
        $values['shop_name'],
        $values['location'],
        $values['business_details'],
        $values['phone']
    );
    return $result['ok'] ? ['ok' => true, 'errors' => []] : ['ok' => false, 'errors' => [$result['error']]];
}

function application_status_ctr($userId)
{
    return (new SellerClass())->getLatestApplication($userId);
}

function application_pending_ctr()
{
    return (new SellerClass())->getPendingApplications();
}

function application_recent_ctr($limit = 10)
{
    return (new SellerClass())->getRecentDecisions($limit);
}

function application_review_ctr($adminId, $applicationId, $decision)
{
    if (!in_array($decision, ['approve', 'reject'], true)) {
        return ['ok' => false, 'error' => 'Unknown decision.'];
    }
    if (!is_string($applicationId) || !preg_match('/^\d{1,10}$/', $applicationId) || (int)$applicationId < 1) {
        return ['ok' => false, 'error' => 'Invalid application.'];
    }
    return (new SellerClass())->review((int)$applicationId, $adminId, $decision);
}
