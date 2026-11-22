CREATE TABLE IF NOT EXISTS seller_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    doc_type ENUM('student_id','national_id','business_registration','other') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name CHAR(40) NOT NULL UNIQUE,
    mime_type VARCHAR(60) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_documents_application (application_id),
    INDEX idx_documents_user (user_id),
    FOREIGN KEY (application_id) REFERENCES seller_applications(application_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
