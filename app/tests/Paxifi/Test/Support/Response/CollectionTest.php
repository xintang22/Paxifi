<?php namespace Paxifi\Test\Support\Response;

use Mockery as m;
use Paxifi\Support\Response\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructWithEmptyData()
    {
        $collection = new Collection();

        $this->assertEmpty($collection->getData());
        $this->assertEquals(function () {}, $collection->getTransformer());
    }

    public function testSetData()
    {
        $collection = m::mock('Paxifi\Support\Response\Collection')->makePartial();
        $collection->setData(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $collection->getData());
    }

    public function testSetTransformer()
    {
        $collection = m::mock('Paxifi\Support\Response\Collection')->makePartial();
        $collection->setTransformer('foo');
        $this->assertEquals('foo', $collection->getTransformer());
    }

    public function tearDown()
    {
        m::close();
    }
}
 