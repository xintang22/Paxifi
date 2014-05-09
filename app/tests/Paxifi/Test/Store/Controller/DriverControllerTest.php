<?php namespace Paxifi\Test\Store\Controller;

use Mockery as m;
use Paxifi\Store\Controller\DriverController;

class DriverControllerTest extends \TestCase
{
    public function testImplementsGetTransformer()
    {
//        $c = new DriverController(m::mock('Paxifi\Store\Repository\DriverRepositoryInterface'), m::mock('Paxifi\Store\Transformer\DriverTransformer'));
//
//        $this->assertInstanceOf('\Paxifi\Store\Transformer\DriverTransformer', $c->getTransformer());
    }

    public function testRegisterNewDriver()
    {
        $postData = ['foo', 'bar'];

        $response = $this->call('post', 'drivers', $postData);

        $this->assertResponseStatus(201);
    }

    public function tearDown()
    {
        m::close();
    }
}
 