<?php

namespace Croon\Adapter;

use Croon\Adapter;

/**
 * Pdo Adapter
 */
class Pdo extends Adapter
{
    protected $options = array(
        'dsn'      => 'mysql:host=localhost;dbname=croon',
        'username' => 'root',
        'password' => '',
        'options'  => array(),
        'table'    => 'croon',
        'fields'   => array('time', 'command')
    );

    protected $pdo;

    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    public function fetch()
    {
        // sub-child process will release the pdo resource, so need reconnect
        if (!is_resource($this->pdo)) {
            $this->connect();
        }

        $query = $this->pdo->query('SELECT * FROM ' . $this->options['table']);
        $rows = $query->fetchAll(\PDO::FETCH_OBJ);
        $fields = $this->options['fields'];
        $tasks = array();

        foreach ($rows as $row) {
            $columns = array();
            foreach ($fields as $field) {
                $columns[$field] = $row->$field;
            }
            $tasks[] = join(' ', $columns);
        }

        return $tasks;
    }

    protected function connect()
    {
        $this->pdo = new \PDO(
            $this->options['dsn'],
            $this->options['username'],
            $this->options['password'],
            $this->options['options']
        );
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}
