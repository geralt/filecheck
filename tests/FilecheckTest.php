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
     * @covers \Expose\FilterCollection::getFilterData
     * @covers \Expose\FilterCollection::setFilterData
     */
    public function testGetSetFilterData()
    {
        $data = array(
            array('id' => 1234)
        );

        $filter = new \Expose\Filter();
        $filter->setId(1234);

        $this->check->setFilterData($data);

        $result = $this->check->getFilterData();
        $this->assertEquals($result[0], $filter);
    }
}