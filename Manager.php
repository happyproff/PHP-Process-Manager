<?php



namespace PHPProcessManager;



require_once 'Process.php';



class Manager {



    public $executable = 'php'; //the system command to call
    public $root = ''; //the root path
    public $processes = 3; //max concurrent processes
    public $sleep_time = 2; //time between processes
    public $show_output = false; //where to show the output or not

    protected $running = []; //the list of scripts currently running
    protected $scripts = []; //the list of scripts - populated by addScript
    protected $processesRunning = 0; //count of processes running
    protected $start = 0;



    public function addScript ($script, $max_execution_time = 300) {

        $this->scripts[] = [
            'script_name' => $script,
            'max_execution_time' => $max_execution_time
        ];

    }

    
    
    public function exec () {

        $this->start = time();

        $this->log('starting with ' . count($this->scripts) . ' tasks');
        
        $i = 0;
        for(;;) {

            // Check if done
            if (($this->processesRunning == 0) and ($i >= count($this->scripts))) {
                break;
            }

            // sleep, this duration depends on your script execution time, the longer execution time, the longer sleep time
            sleep($this->sleep_time);

            // check what is done
            foreach ($this->running as $key => $process) {
                /** @var Process $process */
                $isRunning = $process->isRunning();
                $isOverExecuted = $process->isOverExecuted();
                if (!$isRunning or $isOverExecuted) {
                    $script = $process->script;
                    $this->log(($isOverExecuted ? 'killed' : 'done  ') . ' #' . $key . ' (' . $script . ')');
                    unset($process);
                    unset($this->running[$key]);
                    $this->processesRunning--;
                }
            }

            // Fill up the slots
            while (($this->processesRunning < $this->processes) and ($i < count($this->scripts))) {
                $this->log('added  #' . $i . ' (' . $this->scripts[$i]['script_name'] . ')');
                $this->running[] = new Process(
                    $this->executable,
                    $this->root,
                    $this->scripts[$i]['script_name'],
                    $this->scripts[$i]['max_execution_time']
                );
                $this->processesRunning++;
                $i++;
            }

        }

    }



    protected function log ($message) {

        if ($this->show_output) {
            $message .= ' at +' . (time() - $this->start) . ' sec';
            $message .= PHP_EOL;
            echo 'cli' == PHP_SAPI ? $message : nl2br($message);
        }

    }



}
