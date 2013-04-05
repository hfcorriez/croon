<?php

namespace Croon;

abstract class Adapter
{
    protected $options = array();

    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
    }

    abstract public function fetch();
}