<?php

namespace Croon\Adapter;

use Croon\Adapter;

/**
 * File Adapter
 */
class File extends Adapter
{
    public function fetch()
    {
        clearstatcache();

        if (!is_file($this->options['path'])) throw new \Exception("Non-exist task file \"{$this->options['path']}\"");

        $tasks = file($this->options['path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $tasks;
    }
}
