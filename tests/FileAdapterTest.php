<?php

use Croon\Adapter\File;

class FileAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $file = new File(array('path' => __DIR__ . '/test.list'));
        $list = $file->fetch();
        $this->assertEquals(array('* * * * * * pwd'), $list);
    }

    public function testNonExistsFileFetch()
    {
        $file = new File(array('path' => __DIR__ . '/test1.list'));
        $this->setExpectedException('InvalidArgumentException');
        $file->fetch();
    }
}
