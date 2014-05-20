<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;
use Paxifi\Support\Controller\BaseApiController;

class RatingController extends BaseApiController
{

    /**
     * @var \Paxifi\Store\Repository\Driver\DriverRepositoryInterface
     */
    protected $driver;

    function __construct(DriverRepositoryInterface $driver)
    {
        $this->driver = $driver;

        parent::__construct();
    }

    public function rating($id)
    {

        if ($driver = $this->driver->find($id)) {

            switch (\Input::get('type')) {
                case 'up':
                    $driver->thumbsUp();

                    return $this->respond(array('success' => true));

                case 'down':
                    $driver->thumbsDown();

                    return $this->respond(array('success' => true));

                default:
                    return $this->errorWrongArgs('Be more specific, idiot!');
            }

        }

        return $this->errorWrongArgs('Store does not exist.');
    }
}