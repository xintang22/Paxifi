<?php namespace Paxifi\Test\Support\Response;

use Mockery as m;
use Paxifi\Support\Response\Item;

class ItemTest extends \PHPUnit_Framework_TestCase {

    public function testConstructWithEmptyData()
    {
        $item = new Item();

        $this->assertEmpty($item->getData());
        $this->assertEquals(function () {}, $item->getTransformer());
    }

    public function testSetData()
    {
        $item = m::mock('Paxifi\Support\Response\Item')->makePartial();
        $item->setData(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $item->getData());
    }

    public function testSetTransformer()
    {
        $item = m::mock('Paxifi\Support\Response\Item')->makePartial();
        $item->setTransformer('foo');
        $this->assertEquals('foo', $item->getTransformer());
    }

    public function tearDown()
    {
        m::close();
    }
}
 