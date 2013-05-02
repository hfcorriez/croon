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

        if (!$path = stream_resolve_include_path($this->options['path'])) {
            throw new \InvalidArgumentException("Non-exist cron list file \"{$this->options['path']}\"");
        }

        $tasks = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $tasks;
    }
}
