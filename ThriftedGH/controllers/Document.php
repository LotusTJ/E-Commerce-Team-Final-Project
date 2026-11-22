<?php
require_once __DIR__ . '/Seller.php';
require_once __DIR__ . '/../classes/DocumentClass.php';

const DOCUMENT_MAX_BYTES = 5242880;
const DOCUMENT_MIME_TYPES = [
    'application/pdf' => 'PDF',
    'image/jpeg' => 'JPG',
    'image/png' => 'PNG'
];

function document_storage_dir()
{
    $configured = getenv('DOC_STORAGE_DIR');
    if (is_string($configured) && $configured !== '') {
        return rtrim($configured, '/\\');
    }
    $outside = dirname(tgh_root_dir()) . '/thriftedgh_storage/documents';
    if (is_dir($outside) || is_writable(dirname(dirname($outside)))) {
        return $outside;
    }
    return tgh_root_dir() . '/storage/documents';
}

function document_prepare_dir($dir)
{
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }
    $parent = dirname($dir);
    if (!is_file($parent . '/.htaccess')) {
        @file_put_contents($parent . '/.htaccess', "Require all denied\n");
    }
    if (!is_file($parent . '/.gitignore')) {
        @file_put_contents($parent . '/.gitignore', "*\n");
    }
    return is_writable($dir);
}

function document_display_name($name)
{
    $name = basename(str_replace('\\', '/', (string)$name));
    $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name);
    $name = trim((string)$name);
    if ($name === '') {
        $name = 'document';
    }
    return mb_substr($name, 0, 120);
}

function document_upload_error_message($code)
{
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'That file is larger than the 5 MB limit.',
        UPLOAD_ERR_FORM_SIZE => 'That file is larger than the 5 MB limit.',
        UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE => 'Choose a file to upload.'
    ];
    return $messages[$code] ?? 'The file could not be uploaded. Please try again.';
}

function document_validate_upload($file, $type)
{
    if (!array_key_exists($type, DocumentClass::TYPES)) {
        return ['ok' => false, 'error' => 'Choose what kind of document this is.'];
    }
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'Choose a file to upload.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => document_upload_error_message($file['error'])];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => 'The file could not be uploaded. Please try again.'];
    }
    $size = (int)$file['size'];
    if ($size < 1 || $size > DOCUMENT_MAX_BYTES) {
        return ['ok' => false, 'error' => 'Files must be between 1 byte and 5 MB.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    if (!array_key_exists($mime, DOCUMENT_MIME_TYPES)) {
        return ['ok' => false, 'error' => 'Only PDF, JPG and PNG files are accepted.'];
    }
    return [
        'ok' => true,
        'tmp' => $file['tmp_name'],
        'mime' => $mime,
        'size' => $size,
        'name' => document_display_name($file['name'] ?? '')
    ];
}

function document_open_application($userId)
{
    $application = application_status_ctr($userId);
    return ($application && $application['status'] === 'pending') ? $application : null;
}

function document_upload_ctr($userId, $file, $type)
{
    $application = document_open_application($userId);
    if (!$application) {
        return ['ok' => false, 'error' => 'You can add documents while a seller application is waiting for review.'];
    }
    $model = new DocumentClass();
    if ($model->countForApplication($application['application_id']) >= DocumentClass::MAX_PER_APPLICATION) {
        return ['ok' => false, 'error' => 'You can attach up to ' . DocumentClass::MAX_PER_APPLICATION . ' documents. Delete one to add another.'];
    }
    $checked = document_validate_upload($file, $type);
    if (!$checked['ok']) {
        return $checked;
    }
    $dir = document_storage_dir();
    if (!document_prepare_dir($dir)) {
        error_log('Document storage directory is not writable: ' . $dir);
        return ['ok' => false, 'error' => 'Uploads are unavailable right now. Please try again later.'];
    }
    $stored = bin2hex(random_bytes(20));
    if (!move_uploaded_file($checked['tmp'], $dir . '/' . $stored)) {
        error_log('Document move failed for ' . $dir . '/' . $stored);
        return ['ok' => false, 'error' => 'The file could not be saved. Please try again.'];
    }
    try {
        $model->add($application['application_id'], $userId, $type, $checked['name'], $stored, $checked['mime'], $checked['size']);
    } catch (Throwable $e) {
        @unlink($dir . '/' . $stored);
        error_log('Document record failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'The file could not be saved. Please try again.'];
    }
    return ['ok' => true];
}

function document_delete_ctr($userId, $documentId)
{
    $model = new DocumentClass();
    $document = $model->find($documentId);
    if (!$document || (int)$document['user_id'] !== (int)$userId) {
        return ['ok' => false, 'error' => 'Document not found.'];
    }
    $application = document_open_application($userId);
    if (!$application || (int)$application['application_id'] !== (int)$document['application_id']) {
        return ['ok' => false, 'error' => 'Documents can only be removed while your application is waiting for review.'];
    }
    $model->remove($documentId);
    $path = document_storage_dir() . '/' . $document['stored_name'];
    if (is_file($path)) {
        @unlink($path);
    }
    return ['ok' => true];
}

function document_for_download_ctr($user, $documentId)
{
    $document = (new DocumentClass())->find($documentId);
    if (!$document) {
        return null;
    }
    $isOwner = (int)$document['user_id'] === (int)$user['user_id'];
    if (!$isOwner && $user['role'] !== 'admin') {
        return null;
    }
    $path = document_storage_dir() . '/' . $document['stored_name'];
    if (!is_file($path)) {
        return null;
    }
    $document['path'] = $path;
    return $document;
}
