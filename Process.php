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



    public function __destruct () {

        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        $pid = proc_get_status($this->resource)['pid'];
        self::killProcessWithChilds($pid, 9);
        proc_close($this->resource);

    }



    protected static function killProcessWithChilds ($pid,$signal) {

        exec("ps -ef| awk '\$3 == '$pid' { print  \$2 }'", $output, $ret);
        if ($ret) die('not exists ps or grep or awk');

        while(list(,$t) = each($output)) {
            if ( $t != $pid ) self::killProcessWithChilds($t, $signal);
        }

        posix_kill($pid, 9);

    }



    // is still running?
    public function isRunning () {

        $status = proc_get_status($this->resource);

        return $status['running'];

    }



    // long execution time, proccess is going to be killer
    public function isOverExecuted () {

        if (($this->start_time + $this->max_execution_time) < time()) {
            return true;
        } else {
            return false;
        }

    }



}
