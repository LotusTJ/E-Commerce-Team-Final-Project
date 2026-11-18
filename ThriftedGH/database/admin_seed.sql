INSERT INTO users (full_name,email,password_hash,phone,role)
VALUES ('ThriftedGH Admin','admin@demo.com','$2y$10$pbXy.39qLajcXX60wZfzruscP7v9dcu3PowUI1TQZr8ROacrm/smC','0200000000','admin')
ON DUPLICATE KEY UPDATE role='admin', password_hash=VALUES(password_hash);

INSERT IGNORE INTO users (full_name,email,password_hash,phone,role)
VALUES ('Demo Applicant','applicant@demo.com','$2y$10$pbXy.39qLajcXX60wZfzruscP7v9dcu3PowUI1TQZr8ROacrm/smC','0200000001','buyer');

INSERT INTO seller_applications (user_id,seller_type,shop_name,location,business_details,status)
SELECT u.user_id,'individual','Demo Thrift Corner','Accra','Student seller applying to join ThriftedGH. Curated tees and denim.','pending'
FROM users u
WHERE u.email='applicant@demo.com'
AND NOT EXISTS (SELECT 1 FROM seller_applications a WHERE a.user_id=u.user_id);
