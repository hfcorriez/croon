<?php

namespace Croon;

use Pagon\ChildProcess;
use Pagon\EventEmitter;
use Pagon\Logger;

class Croon extends EventEmitter
{
    protected $is_run = false;
    protected $start_time;
    protected $options = array(
        'source'  => array(),
        'process' => array(),
        'log'     => array(
            'auto_write' => true,
            'file'       => 'croon.log'
        )
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
        $this->logger = new Logger($this->options['log']);
    }

    /**
     * Start to run
     */
    public function run()
    {
        if ($this->is_run) {
            throw new \RuntimeException("Already running!");
        }

        $this->is_run = true;
        $this->emit('run');

        $this->logger->info("Start! (memory: %s)", Utils::convertUnit(memory_get_usage()));

        $type = ucfirst($this->options['source']['type']);

        if (!class_exists($try_type = __NAMESPACE__ . "\\Adapter\\" . $type)
            && !class_exists($try_type = $type)
        ) {
            throw new \RuntimeException('Unknown adapter type of "' . $try_type . '"');
        }

        $source = new $try_type($this->options['source']);

        while (true) {
            $this->logger->info('Croon...!!! (memory: %s)', Utils::convertUnit(memory_get_usage()));

            $this->emit('tick');
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
        $this->logger->info('Execute (%s)', $command);
        $this->emit('execute', $command);

        $that = $this;

        $this->process->parallel(function () use ($command, $that) {
                $status = Utils::exec($command, $stdout, $stderr);

                $that->logger->info('Finish (%s)[%d]', $command, (int)$status);

                $that->emit('executed', $command, array($status, $stdout, $stderr));
            }
        );
    }
}
