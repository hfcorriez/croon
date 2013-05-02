<?php

use Croon\Utils;

class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $ret = Utils::parseLine('* * * * * * pwd');
        $this->assertEquals('* * * * * * ', $ret[0]);
        $this->assertEquals('pwd', $ret[1]);
    }

    public function testCompatibleFormat()
    {
        $ret = Utils::parseLine('* * * * * pwd');
        $this->assertEquals('0 * * * * * ', $ret[0]);
        $this->assertEquals('pwd', $ret[1]);
    }

    public function testRule()
    {
        $ret = Utils::checkRule('* * * * * *');

        $this->assertTrue($ret);
    }

    public function testRange()
    {
        $ret = Utils::checkIndexRange(0, 30);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(0, 61);
        $this->assertFalse($ret);

        $ret = Utils::checkIndexRange(1, 30);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(1, 61);
        $this->assertFalse($ret);

        $ret = Utils::checkIndexRange(2, 2);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(2, 25);
        $this->assertFalse($ret);

        $ret = Utils::checkIndexRange(3, 1);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(3, 32);
        $this->assertFalse($ret);

        $ret = Utils::checkIndexRange(4, 1);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(4, 13);
        $this->assertFalse($ret);

        $ret = Utils::checkIndexRange(5, 1);
        $this->assertTrue($ret);

        $ret = Utils::checkIndexRange(5, 8);
        $this->assertFalse($ret);
    }

    public function testConvertUnit()
    {
        $this->assertEquals('1 kb', Utils::convertUnit(1024));
        $this->assertEquals('1 kb', Utils::convertUnit(1025));
    }

    public function testExec()
    {
        $dir = getcwd();
        Utils::exec('pwd', $stdout, $stderr);
        $this->assertEquals($dir, trim($stdout));
    }
}
