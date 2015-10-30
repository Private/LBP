<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Config\Config as Config;

$config = new Config();

echo "Initializing database tables...\n";

$schemas = ['sde', 'cache', 'state'];

$capsule = new Capsule();

echo "\tInitializing connections...\n";

foreach ($schemas as $schema) {

    echo "\t\t$schema";
    
    $capsule->addConnection([
    
        'driver' => $config->database->driver,
        'host' => $config->database->host,
        
        'database' => $config->database->schemas->{$schema},
    
        'username' => $config->database->users->admin->username,
        'password' => $config->database->users->admin->password,

        'charset' => $config->database->charset,
        'collation' => $config->database->collation
    
    ], $schema);

    echo "\t\t[DONE]\n";

}

$capsule->setAsGlobal();

// -------------------------------------------------- //
// -------------------------------------------------- //
// -------------------------------------------------- //

function buildTablesSDE() {

    echo "\tBuilding tables for Static Data Export\n";
    
    $db = Capsule::connection('sde');
    
    $schema = $db->getSchemaBuilder();

    $schema->dropIfExists('contents');
    $schema->dropIfExists('types');
    $schema->dropIfExists('groups');
        
    // ---------------------------------------------- //

    echo "\t\tgroups";

    $schema->create('groups', function ($table) {

        $table->integer('id')->unsigned();        
        $table->string('name');

        $table
            ->primary('id');
    });
    

    echo "\t\t[OK]\n";

    // ---------------------------------------------- //

    echo "\t\ttypes";
    
    $schema->create('types', function ($table) {
        
        $table->integer('id')->unsigned();
        $table->integer('icon_id')->unsigned();
        $table->integer('group_id')->unsigned();

        $table->string('name');

        $table->float('volume');
        
        $table->integer('portion_size')->unsigned();

        $table
            ->foreign('group_id')
            ->references('id')->on('groups');
        
        $table
            ->primary('id');
        
        
    });

    echo "\t\t[OK]\n";

    // ---------------------------------------------- //

    echo "\t\tcontents";
    
    $schema->create('contents', function ($table) {
        
        $table->integer('id')->unsigned();
        
        $table->integer('type')->unsigned();
        $table->integer('quantity')->unsigned();

        $table
            ->foreign('id')
            ->references('id')->on('types');
        $table
            ->foreign('type')
            ->references('id')->on('types');
        
        $table
            ->primary('id');
    });
    
    echo "\t[OK]\n";

}

// -------------------------------------------------- //
// -------------------------------------------------- //
// -------------------------------------------------- //

function buildTablesCache() {

    echo "\tBuilding tables for Cache\n";
    
    $db = Capsule::connection('cache');
    
    $schema = $db->getSchemaBuilder();

    /*
      Cache responsibilities consists mainly of two parts:
       o EVE API Cache timers must be obeyed.
       o Querying eve-central.com for marketdata all the time is 
         fun, but bad for response times. 
     */
    
    $schema->dropIfExists('eve');
    $schema->dropIfExists('market');
    
    // ---------------------------------------------- //

    echo "\t\teve";
    
    $schema->create('eve', function ($table) {

        $table->timestamp('added');
        $table->integer('lifetime')->unsigned();

        $table->integer('key_id')->unsigned();
        $table->char('verification_code', 64);
        $table->char('page', 255);
        
        $table->text('response');

        $table
            ->index('added');
        
        $table
            ->primary(['key_id', 'verification_code', 'page']);
    });    

    echo "\t\t[OK]\n";

    // ---------------------------------------------- //

    echo "\t\tmarket";

    $schema->create('market', function ($table) {

        $table->timestamp('added');
        $table->integer('lifetime')->unsigned();

        $table->integer('type_id')->unsigned();
        
        $table->float('stuff');
        
    });    
    
    echo "\t\t[OK]\n";

}

// -------------------------------------------------- //
// -------------------------------------------------- //
// -------------------------------------------------- //

function buildTablesState() {

}

buildTablesSDE();
buildTablesCache();

?>