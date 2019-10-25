<?php 

require __DIR__.'/../vendor/autoload.php';

$worker = new Net_Gearman_Worker('192.168.100.211:4730');
$worker->enDebug();

class Reverse_String extends Net_Gearman_Job_Common {

    public function run($workload) {
        $result = strrev($workload);
        return $result;
    }
}


$worker->registerFunction('Reverse_String','Reverse_String');
$worker->beginWork();

// $function = function($payload) {
//     return str_replace('java', 'php', $payload);
// };

// $worker = new \MHlavac\Gearman\Worker();
// $worker->addServer('192.168.100.211','4730');
// $worker->addFunction('replace', $function);

// $worker->work();
