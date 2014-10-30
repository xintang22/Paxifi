<?php namespace Paxifi\Support\Socket;

use Symfony\Component\Routing\Route;

class ChannelEventHandler extends Route
{

    /**
     * Different Parameters set by ratchet.
     *
     * @var array
     */
    protected $wsParameters;

    /**
     * Different Parameters from the actual request.
     *
     * @var array
     */
    protected $requestParameters;

    /**
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * @param array $requestParameters
     */
    public function setRequestParameters($requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    /**
     * @return array
     */
    public function getWsParameters()
    {
        return $this->wsParameters;
    }

    /**
     * @param array $wsParameters
     */
    public function setWsParameters($wsParameters)
    {
        $this->wsParameters = $wsParameters;
    }

    /**
     * Execute the handler
     *
     * @param string $event
     * @return void
     */
    public function run($event)
    {
        $this->callController($event);
    }

    /**
     * Call the registered controller
     * with the right event
     *
     * @param string $event (subscribe, publish, call, unsubscribe)
     * @return mixed
     */
    protected function callController($event)
    {
        $parameters = $this->getMergedParameters();

        return call_user_func_array(array($this->requestParameters['_controller'], $event), $parameters);
    }

    /**
     * Get merged parameters.
     *
     * @return array
     */
    protected function getMergedParameters()
    {
        $variables = $this->compile()->getVariables();

        $parameters = array();

        foreach ($variables as $variable) {
            $parameters[$variable] = $this->requestParameters[$variable];
        }

        return array_merge($this->wsParameters, $parameters);
    }


} 