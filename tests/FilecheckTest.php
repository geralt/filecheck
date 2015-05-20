<?php

namespace FileCheck;

class FilecheckTest extends \PHPUnit_Framework_TestCase 
{
    private $check = null;

    public function setUp()
    {
        $this->check = new FileCheck();
    }

    /**
     * Test the getter/setter for the filter data in collection
     * 
     * @covers \FileCheck\FileCheck::getLogFolder
     * @covers \FileCheck\FileCheck::setLogFolder
     */
    public function testGetSetLogFolder()
    {
        $data = array(
            array('folder' => dirname(__FILE__))
        );
        $this->check->setLogFolder($data['folder']);
        $this->assertEquals($data['folder'], $this->check->getLogFolder());
    }
}