<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL|E_STRICT);
require_once ('/home/juliens/githuboktopus/Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/worker');
Oktopus\Engine::autoloader()->addPath('/home/juliens/net_gearman3');


while (true) {
try {
    $worker = new GearmanWorker(array('0.0.0.0:4730', 'dev01:7004'));
//    $worker->addAbility('Hello');
//    $worker->addAbility('Fail');
    //*
	$worker->addFunction('longtask', function ($arg1, $arg2,$job) {
		for ($i=0;$i<100;$i++) {
			$job->status ($i,100);
			sleep(2);
		}
    	return "reverse ".$arg1.'--'.$arg2;
    });
    
    $worker->addFunction('reverse_b', function ($job) {
    		return array("reverse_b");
    	});
    	
    	//*/
//    $worker->addAbility('SQL');
    $worker->beginWork();
} catch (Net_Gearman_Exception $e) {
    echo '----'.$e->getMessage() . "\n";
    flush();
}
}

exit;

while (true) {
try {
    $worker = new GearmanWorker(array('0.0.0.0:4730'));
//    $worker->addAbility('Hello');
//    $worker->addAbility('Fail');
    $worker->addFunction('reverse', function ($job) {
    		return "TEST";
    	});
    $worker->beginWork();
} catch (Net_Gearman_Exception $e) {
    echo '----'.$e->getMessage() . "\n";
    flush();
}
}
?>

?>
