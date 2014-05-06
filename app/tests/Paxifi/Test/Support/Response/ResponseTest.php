<?php namespace Paxifi\Test\Support\Response;

use Mockery as m;
use Paxifi\Support\Response\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testRespondWithItem()
    {
        $response = new Response(
            $fractal = m::mock('League\Fractal\Manager'),
            $item = m::mock('Paxifi\Support\Response\Item'),
            m::mock('Paxifi\Support\Response\Collection'),
            m::mock('Paxifi\Support\Response\Paginator'),
            m::mock('Paxifi\Support\Response\Cursor')
        );

        $item->shouldReceive('setData')->andReturn('foo');
        $item->shouldReceive('setTransformer');

        $fractal->shouldReceive('toArray');
        $fractal->shouldReceive('createData')->with($item)->once()->andReturn($fractal);

        $response->withItem();
    }

    public function testRespondWithCollection()
    {
        $response = new Response(
            $fractal = m::mock('League\Fractal\Manager'),
            m::mock('Paxifi\Support\Response\Item'),
            $collection = m::mock('Paxifi\Support\Response\Collection'),
            m::mock('Paxifi\Support\Response\Paginator'),
            m::mock('Paxifi\Support\Response\Cursor')
        );

        $response->setContent('foo');
        $response->setTransformer($transformer = m::mock('League\Fractal\TransformerAbstract'));

        $fractal->shouldReceive('toArray');
        $fractal->shouldReceive('createData')->with($collection)->once()->andReturn($fractal);

        $collection->shouldReceive('setData')->once()->with('foo');
        $collection->shouldReceive('setTransformer')->once()->with($transformer);

        $response->withCollection();
    }

    public function testRespondWithPaginatedCollection()
    {
        $response = new Response(
            $fractal = m::mock('League\Fractal\Manager'),
            m::mock('Paxifi\Support\Response\Item'),
            $collection = m::mock('Paxifi\Support\Response\Collection'),
            $paginator = m::mock('Paxifi\Support\Response\Paginator'),
            m::mock('Paxifi\Support\Response\Cursor')
        );

        $response->setContent($paginatedContent = m::mock('Paxifi\Support\Response\Paginator'));
        $response->setTransformer($transformer = m::mock('League\Fractal\TransformerAbstract'));

        $fractal->shouldReceive('toArray');
        $fractal->shouldReceive('createData')->with($collection)->once()->andReturn($fractal);

        $collection->shouldReceive('setData')->once()->with($paginatedContent)->andReturn('foo');
        $collection->shouldReceive('setTransformer')->once()->with($transformer);
        $collection->shouldReceive('setPaginator')->once()->with($paginator);

        $paginator->shouldReceive('setupPaginator')->once()->with($paginatedContent);

        $response->withCollection(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowExceptionIfRespondWithPaginatedCollectionButContentIsNotPaginatorInterface()
    {
        $response = new Response(
            $fractal = m::mock('League\Fractal\Manager'),
            m::mock('Paxifi\Support\Response\Item'),
            $collection = m::mock('Paxifi\Support\Response\Collection'),
            $paginator = m::mock('Paxifi\Support\Response\Paginator'),
            m::mock('Paxifi\Support\Response\Cursor')
        );

        $response->setContent('foo');
        $response->setTransformer($transformer = m::mock('League\Fractal\TransformerAbstract'));

        $collection->shouldReceive('setData')->once()->with('foo');
        $collection->shouldReceive('setTransformer')->once()->with($transformer);

        $response->withCollection(true);
    }

    public function tearDown()
    {
        m::close();
    }
}
 