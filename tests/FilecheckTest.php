<?php

//namespace FileCheck;

//use FileCheck;
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

class FilecheckTest extends \PHPUnit_Framework_TestCase 
{
    private $check = null;

    public function setUp()
    {
        echo 'Begining test....';
		$this->check = new \FileCheck\FileCheck();
    }

    /**
     * Test the getter/setter for the debug status
     * 
     * @covers \FileCheck\FileCheck::setDebug
     * @covers \FileCheck\FileCheck::getDebug
     */
    public function testGetSetDebug()
    {

        $this->check->setDebug(true);
        $result = $this->check->getDebug();
        $this->assertEquals($result, $filter);
    }
}