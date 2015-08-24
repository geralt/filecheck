<?php

class FilecheckTest extends \PHPUnit_Framework_TestCase 
{
    private $check = null;

    public function setUp()
    {
		$this->check = new \FileCheck\FileCheck( __DIR__,__DIR__,'');
    }

    /**
     * Test the getter/setter for the debug status
     * 
     * @covers \FileCheck\FileCheck::setDebug
     * @covers \FileCheck\FileCheck::getDebug
     */
    public function testGetSetDebug()
    {
        $value = true;
        $this->check->setDebug($value);
        $result = $this->check->getDebug();
        $this->assertEquals($result, $value);
    }
}