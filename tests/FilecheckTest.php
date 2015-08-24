<?php

namespace FileCheck;

use FileCheck;

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
     * @covers \Expose\FilterCollection::getFilterData
     * @covers \Expose\FilterCollection::setFilterData
     */
    public function testGetSetDebug()
    {
        $data = array(
            array('id' => 1234)
        );

        $this->check->setDebug(true);
        $result = $this->check->getDebug();
        $this->assertEquals($result, $filter);
    }
}