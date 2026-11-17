<?php
if(getenv('DB_NAME')!=='thriftedgh_member4_test') exit(1);
require_once __DIR__.'/../core/db_class.php';
$db=Database::getInstance();if(DB_NAME!=='thriftedgh_member4_test' || $db->query('SELECT DATABASE() n')->fetch_assoc()['n']!=='thriftedgh_member4_test') exit(1);$hash=password_hash('Member4-test-fixture',PASSWORD_DEFAULT);
$s=$db->prepare('UPDATE users SET password_hash=?');$s->bind_param('s',$hash);$s->execute();
