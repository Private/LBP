<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Config\Config as Config;

$config = new Config();

echo "Initializing database user...\n";

echo "\tTo initialize the LBP user, a mysql admin is required:\n";

echo "\tusername: ";
$_user = trim(fgets(STDIN));

echo "\tpassword: ";
$_pass = trim(fgets(STDIN));


$capsule = new Capsule();
$capsule->addConnection([
    
    'driver' => $config->database->driver,
    'host' => $config->database->host,
    'database' => $config->database->schemas->admin,
    
    'username' => $_user,
    'password' => $_pass,

    'charset' => $config->database->charset,
    'collation' => $config->database->collation
    
]);

$capsule->setAsGlobal();

$connection = $capsule->getConnection();

echo "\n\tInitializing LBP databases...\n";

foreach (['sde', 'cache', 'state'] as $db) {

    echo "\t" . $db;
    echo " (" . $config->database->schemas->{$db} . ") ";

    $_db_name = $config->database->schemas->{$db};
    $_charset = $config->database->charset;
    $_collation = $config->database->collation;
    
    $sql = ("CREATE DATABASE IF NOT EXISTS $_db_name CHARACTER SET $_charset COLLATE $_collation;");
    $affected = $connection->unprepared($sql);
    
    echo "\t" . ($affected ? "[OK]" : "[FAILED]") . "\n";
}


echo "\n\tInitializing LBP users...\n";

function addUser($username, $password, $privileges) {

    global $connection, $config;

    echo "\tSetting up user: $username\n";
    
    foreach (['sde', 'cache', 'state'] as $db) {

        $db = $config->database->schemas->{$db};
        
        echo "\t\t\t$db ";
        
        $sql = "GRANT $privileges ON $db.* TO '$username'@'localhost' IDENTIFIED BY '$password';";
        $affected = $connection->unprepared($sql);
        
        echo "\t" . ($affected ? "[OK]" : "[FAILED]") . "\n";
    }
}

addUser($config->database->users->admin->username,
        $config->database->users->admin->password,
        "ALL");

addUser($config->database->users->base->username,
        $config->database->users->base->password,
        "SELECT, INSERT, UPDATE, DELETE");

echo "\n";

unset($_user);
unset($_pass);
unset($addUser);

?>