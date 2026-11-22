<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Document.php';

$page = tgh_url('views/seller/documents.php');

guardRequirePost();
$user = guardRequireLogin();
guardRequireCsrf($page);

$result = document_delete_ctr((int)$user['user_id'], (int)tgh_input_string($_POST, 'document_id'));

if ($result['ok']) {
    tgh_flash_set('success', 'Document removed.');
} else {
    tgh_flash_set('error', $result['error']);
}
tgh_redirect($page);
