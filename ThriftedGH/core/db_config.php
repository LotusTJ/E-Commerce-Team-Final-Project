<?php
// An existing private db_cred.php remains supported; new installs can use environment variables.
if (is_file(__DIR__.'/db_cred.php')) require_once __DIR__.'/db_cred.php';
$defaults=[
    'DB_HOST'=>getenv('DB_HOST') ?: 'localhost',
    'DB_USER'=>getenv('DB_USER') ?: 'root',
    'DB_PASS'=>getenv('DB_PASS') === false ? '' : getenv('DB_PASS'),
    'DB_NAME'=>getenv('DB_NAME') ?: 'thriftedgh',
    'DB_PORT'=>(int)(getenv('DB_PORT') ?: 3306),
    'DB_SOCKET'=>getenv('DB_SOCKET') ?: null,
];
foreach($defaults as $name=>$value) if(!defined($name)) define($name,$value);
unset($defaults,$name,$value);
