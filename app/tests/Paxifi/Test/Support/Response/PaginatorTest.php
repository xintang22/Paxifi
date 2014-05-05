<?php namespace Paxifi\Test\Support\Response;

use Illuminate\Pagination\Paginator as IlluminatePaginator;
use Mockery as m;
use Paxifi\Support\Response\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginationContextIsSetupCorrectly()
    {
        $p = new Paginator();
        $environment = m::mock('Illuminate\Pagination\Environment');
        $ip = new IlluminatePaginator($environment, array('foo', 'bar', 'baz'), 3, 2);
        $environment->shouldReceive('getCurrentPage')->once()->andReturn(1);

        $p->setupPaginator($ip);

        $this->assertEquals(2, $p->getLastPage());
        $this->assertEquals(1, $p->getCurrentPage());
    }

    public function tearDown()
    {
        m::close();
    }
}
 