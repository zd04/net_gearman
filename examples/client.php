<?php 
require __DIR__.'/../vendor/autoload.php';

$client = new Net_Gearman_Client("192.168.100.211:4730");
$client->enDebug();
$timeout = 10;

$set = new Net_Gearman_Set();
$task = new Net_Gearman_Task("Reverse_String", "Hello  world");
$task->attachCallback(
    function($func, $handle, $result){
        print_r($result);
    }
);
$set->addTask($task);
$client->runSet($set, $timeout);


// $client = new \MHlavac\Gearman\Client();
// $client->addServer('192.168.100.211','4730');

// $result = $client->doNormal('replace', 'PHP is best programming language!');
//$client->doBackground('long_task', 'PHP rules... PHP rules...');