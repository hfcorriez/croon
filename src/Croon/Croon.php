<?php

namespace Croon;

use Pagon\ChildProcess\ChildProcess;
use Pagon\EventEmitter;

class Croon extends EventEmitter
{
    protected $start_time;
    protected $options = array(
        'process' => array(),
        'source'  => array()
    );
    protected $process;

    /**
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (empty($this->options['source']['type'])) {
            throw new \InvalidArgumentException('Config "source" not correct');
        }

        $this->start_time = time();

        $this->process = new ChildProcess($this->options['process']);
    }

    /**
     * Start to run
     */
    public function run()
    {
        $this->emit('run');
        $this->log("croon start.");

        $type = ucfirst($this->options['source']['type']);

        $try_type = false;
        if (!class_exists($type)
            && ($try_type = __NAMESPACE__ . "\\Adapter\\" . $type)
            && !class_exists($try_type)
        ) {
            throw new \RuntimeException('Unknown adapter type of "' . $type . '"');
        }

        if ($try_type) $type = $try_type;
        $source = new $type($this->options['source']);


        while (true) {
            $this->emit('turn');
            // Load tasks every time.
            $tasks = $source->fetch();

            // record current time
            $micro_time = floor(microtime(true) * 1000000);

            // commands to run
            $command_hits = array();

            foreach ($tasks as $task) {
                // Parse task line
                if (!$parse = Utils::parseLine($task)) continue;

                // Extract rule and command
                list($rule, $command) = $parse;

                // Check rule if ok?
                if (Utils::checkRule($rule)) $command_hits[] = $command;
            }

            foreach ($command_hits as $command) {
                $this->dispatch($command);
            }

            // check sleep time and do sleep
            $current_time = microtime(true);
            $sleep_time = 1000000 - floor((microtime(true) - floor($current_time)) * 1000000);

            if ($sleep_time > 0) {
                usleep($sleep_time);
            }

            unset($sleep_time, $micro_time, $tasks, $command_hits, $current_time);
        }
    }

    /**
     * Dispatch command
     *
     * @param $command
     */
    protected function dispatch($command)
    {
        $this->log(posix_getpid() . ': dispatch "' . $command . '" ' . memory_get_usage());
        $this->emit('execute', $command);

        $that = $this;

        $this->process->parallel(function () use ($command, $that) {
                $status = Utils::exec($command, $stdout, $stderr);
                $that->emit('executed', $command, array($status, $stdout, $stderr));
            }
        );
    }

    /**
     * Log
     *
     * @param $text
     */
    public function log($text)
    {
        print date('Y-m-d H:i:s') . ' ' . $text . PHP_EOL;
    }
}
