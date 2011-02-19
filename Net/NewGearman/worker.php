<?php

class Net_Gearman_Job_Callback extends Net_Gearman_Job_Common
{
	
	public function __construct($conn, $handle, array $initParams=array())
	{
		$this->_callback = $initParams['_callback'];
		if (!is_callable($this->_callback)) {
			throw new Exception("Not a valid callbak");
		}
		parent::__construct($conn, $handle, $initParams);
	}
	
    /**
     * Run job
     *
     * @access      public
     * @param       array       $arg
     * @return      array
     */
    public function run($pArgs)
    {
    	$pArgs[] = $this;
		return call_user_func_array($this->_callback, $pArgs);
    }
}


class GearmanJob extends Net_Gearman_Job {
	static public function factory($job, $conn, $handle, $initParams=array()) {
		if (isset ($initParams['_type']) && $initParams['_type']==GearmanWorker::JOBTYPE_CALLBACK) {
			$job = 'callback';
		}
		
		return parent::factory($job, $conn, $handle, $initParams);
	}
}

class GearmanWorker extends Net_Gearman_Worker {
	
	const JOBTYPE_CALLBACK = 0;
	
	/**
	 * Constructor
	 *
	 * @param array $servers List of servers to connect to
	 *
	 * @return void
	 * @throws Net_Gearman_Exception
	 * @see Net_Gearman_Connection
	 */
	public function __construct($servers = null)
	{
		$this->id = "pid_".getmypid()."_".uniqid();;
		if ($servers != null) {
			$this->addServers ($servers);
		}
	}

	public function addServers ($servers = null) {
		if (!is_array($servers) && strlen($servers)) {
			$servers = array($servers);
		} elseif (is_array($servers) && !count($servers)) {
			throw new Net_Gearman_Exception('Invalid servers specified');
		}
		
		foreach ($servers as $s) {
			$this->addServer ($s);
		}
	}
	
	public function addServer ($s = null) {
		if ($s==null) {
			$s='localhost';
		}

		try {
			$conn = Net_Gearman_Connection::connect($s);
			Net_Gearman_Connection::send($conn, "set_client_id", array("client_id" => $this->id));
			$this->conn[$s] = $conn;
		} catch (Net_Gearman_Exception $e) {
			$this->retryConn[$s] = time();
		}
	}

	/**
	 * Begin working
	 *
	 * This starts the worker on its journey of actually working. The first
	 * argument is a PHP callback to a function that can be used to monitor
	 * the worker. If no callback is provided then the worker works until it
	 * is killed. The monitor is passed two arguments; whether or not the
	 * worker is idle and when the last job was ran.
	 *
	 * @param callback $monitor Function to monitor work
	 *
	 * @return void
	 * @see Net_Gearman_Connection::send(), Net_Gearman_Connection::connect()
	 * @see Net_Gearman_Worker::doWork(), Net_Gearman_Worker::addAbility()
	 */
	public function beginWork($monitor = null)
	{
		if (empty($this->conn)) {
			throw new Net_Gearman_Exception(
                "Couldn't connect to any available servers"
                );
		}
		parent::beginWork($monitor);
	}
	
	public function addFunction ($pName, $pCallBack, $pInitParams = array (), $timeout = null) {
		if (!is_callable($pCallBack)) {
			throw new Net_Gearman_Exception("Not a valid callback");
		}
		if (!is_array($pInitParams)) {
			$pInitParams = array ($pInitParams);
		}
		$pInitParams['_type'] = self::JOBTYPE_CALLBACK;
		$pInitParams['_callback'] = $pCallBack;
		$this->addAbility($pName, $timeout, $pInitParams);
	}
	
	/**
     * Listen on the socket for work
     *
     * Sends the 'grab_job' command and then listens for either the 'noop' or
     * the 'no_job' command to come back. If the 'job_assign' comes down the
     * pipe then we run that job.
     *
     * @param resource $socket The socket to work on
     *
     * @return boolean Returns true if work was done, false if not
     * @throws Net_Gearman_Exception
     * @see Net_Gearman_Connection::send()
     */
    protected function doWork($socket)
    {
        Net_Gearman_Connection::send($socket, 'grab_job');

        $resp = array('function' => 'noop');
        while (count($resp) && $resp['function'] == 'noop') {
            $resp = Net_Gearman_Connection::blockingRead($socket);
        }

        if (in_array($resp['function'], array('noop', 'no_job'))) {
            return false;
        }

        if ($resp['function'] != 'job_assign') {
            throw new Net_Gearman_Exception('Holy Cow! What are you doing?!');
        }

        $name   = $resp['data']['func'];
        $handle = $resp['data']['handle'];
        $arg    = array();

        if (isset($resp['data']['arg']) &&
            Net_Gearman_Connection::stringLength($resp['data']['arg'])) {
            $arg = json_decode($resp['data']['arg'], true);
            if($arg === null){
                $arg = $resp['data']['arg'];
            }
        }

        $job = GearmanJob::factory(
            $name, $socket, $handle, $this->initParams[$name]
        );
        try {
            $this->start($handle, $name, $arg);
            $res = $job->run($arg);

            $job->complete($res);
            $this->complete($handle, $name, $res);
        } catch (Net_Gearman_Job_Exception $e) {
            $job->fail();
            $this->fail($handle, $name, $e);
        }

        // Force the job's destructor to run
        $job = null;

        return true;
    }
	
}