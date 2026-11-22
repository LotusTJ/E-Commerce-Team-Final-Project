<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Document.php';

$page = tgh_url('views/seller/documents.php');

guardRequirePost();
$user = guardRequireLogin();
if ($user['role'] === 'admin') {
    guardDeny(403, 'Administrator accounts do not apply to sell.');
}
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    tgh_flash_set('error', 'That file is larger than the 5 MB limit.');
    tgh_redirect($page);
}
guardRequireCsrf($page);

$result = document_upload_ctr((int)$user['user_id'], $_FILES['document'] ?? null, tgh_input_string($_POST, 'doc_type'));

if ($result['ok']) {
    tgh_flash_set('success', 'Document uploaded.');
} else {
    tgh_flash_set('error', $result['error']);
}
tgh_redirect($page);
