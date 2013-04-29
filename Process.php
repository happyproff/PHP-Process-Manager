<?php



namespace PHPProcessManager;



class Process {



    public $resource;
    public $pipes;
    public $script;
    public $max_execution_time;
    public $start_time;



    public function __construct (&$executable, &$root, $script, $max_execution_time) {

        $this->script = $script;
        $this->max_execution_time = $max_execution_time;
        $descriptorspec = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];
        $this->resource = proc_open($executable . ' ' . $root . $this->script, $descriptorspec, $this->pipes, null, $_ENV);
        $this->start_time = time();

    }



    // is still running?
    public function isRunning () {

        $status = proc_get_status($this->resource);

        return $status['running'];

    }



    // long execution time, proccess is going to be killer
    public function isOverExecuted () {

        if ($this->start_time+$this->max_execution_time<time()) {
            return true;
        } else {
            return false;
        }

    }



}
