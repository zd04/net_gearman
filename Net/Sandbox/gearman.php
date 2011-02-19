<?php
ini_set ('display_errors', 1);
error_reporting (E_ALL);
require_once ('/home/juliens/githuboktopus/Oktopus/Engine.php');
Oktopus\Engine::start('/tmp/');
Oktopus\Engine::autoloader()->addPath('/home/juliens/net_gearman3');

/*
$test = new test();
$test->testfunc(function ($test) {echo $test;});
$test->testfunc(array($test,2));

class GMC {
	const MODE_SYNC = 1;
	const MODE_ASYNC = 2;
	
	private $mode = self::MODE_SYNC;
	
	public function __invoke ($pMode = null) {
		if ($pMode == null) {
			return $this;
		} else {
			return new mode_call($this,$pMode);
		}
	}
	
	public function __call ($funcName, $params) {
		if ($this->mode==self::MODE_SYNC) {
			$this->__syncCall ($funcName, $params);
		} else {
			$this->__asyncCall ($funcName, $params);
		}
	}
	
	public function __syncCall ($funcName, $params) {
		echo "SYNC $funcName<br />";
	}
	
	public function __asyncCall ($funcName, $params) {
		echo "ASYNC $funcName<br />";
	}
	
}


class mode_call {
	
	private $client = null;
	
	public function __construct (GMC $client, $pMode) {
		$this->client = $client;
		$this->mode = $pMode;
	}
	public function __call ($funcName, $params) {
		if ($this->mode == GMC::MODE_ASYNC) {
			$this->client->__asyncCall ($funcName, $params);
		} else {
			$this->client->__syncCall ($funcName, $params);
		}
	}
}

$client = new GMC ();

$client->test ();
$client(GMC::MODE_ASYNC)->test();
$client->test ();
$client(GMC::MODE_ASYNC)->test();
$client(GMC::MODE_SYNC)->test();
$client(GMC::MODE_ASYNC)->test();
$client()->test();




exit;

//*/

function status () {
	var_dump(func_get_args());
	
}
$client = new Net_Gearman_Client('localhost:4730');
var_dump($client->getStatus('H:juliens-desktop:12'));
exit;

$task = $client->longtask(array ('test', 'test3'));
echo $task->handle;

