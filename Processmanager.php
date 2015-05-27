<?php
class Processmanager {
	public $executable       = "php";	//the system command to call
	public $root             = "";		//the root path
	public $sleep_time       = 2;		//time between processes
	public $show_output      = false;	//where to show the output or not
	
	private $running          = array();//the list of scripts currently running
	private $scripts          = array();//the list of scripts - populated by addScript
	private $processesRunning = 0;		//count of processes running	

	
	function addScript($script, $key, $max_execution_time = 0)
	{
		$this->scripts[$key] = array("script_name" => $script, "max_execution_time" => $max_execution_time);
	}

	function exec($add_work_func,$end_work_func)
	{
		for(;;)
		{
			
			$app_rows = $add_work_func(count($this->scripts));
			
			
			if(is_array($app_rows) && count($app_rows)>0){
				for ($w=0; $w < count($app_rows); $w++) {
					$app_row = $app_rows[$w]; 
					$this->addScript($app_row["script_name"], $app_row["script_key"]);
				}
			}
			
			
			foreach ($this->scripts as $key => $value) {
				$this->running[$key] =& new Process($this->executable, $this->root, $this->scripts[$key]["script_name"], $this->scripts[$key]["max_execution_time"]);
				$this->processesRunning++;
			}

			// Check if done
			if (($this->processesRunning==0) && (count($this->scripts)==0)) {
				
				echo "no work!";
				
				sleep(3);
				continue;
			}

			// sleep, this duration depends on your script execution time, the longer execution time, the longer sleep time
			sleep($this->sleep_time);

			// check what is done
			foreach ($this->running as $key => $val)
			{

				if (!$val->isRunning() || $val->isOverExecuted())
				{
					proc_close($val->resource);
					unset($this->running[$key]);
					unset($this->scripts[$key]);
					$this->processesRunning--;
					$end_work_func($key);
				}
			}
		}
	}
}

class Process {
	public $resource;
	public $pipes;
	public $script;
	public $max_execution_time;
	public $start_time;

	function __construct(&$executable, &$root, $script, $max_execution_time)
	{
		$this->script = $script;
		$this->max_execution_time = $max_execution_time;
		$descriptorspec    = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
			//2 => array("file", "testPipe.log", "a")
		);
		$this->resource    = proc_open($executable." ".$root.$this->script . " >/dev/null 2>&1", $descriptorspec, $this->pipes, null, $_ENV);
		
		$this->start_time = time();
	}

	// is still running?
	function isRunning()
	{
		$status = proc_get_status($this->resource);
		return $status["running"];
	}

	// long execution time, proccess is going to be killer
	function isOverExecuted()
	{
		if($this->max_execution_time==0) return false;
		if ($this->start_time+$this->max_execution_time<time()) return true;
		else return false;
	}
}
