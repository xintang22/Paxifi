<?php namespace Paxifi\Support\Response;

use League\Fractal\Resource\Collection as FractalCollection;

/**
 * Collection resource.
 *
 * @package Paxifi\Support\Response
 */
class Collection extends FractalCollection
{
    /**
     * Initialize with empty data.
     */
    function __construct()
    {
        parent::__construct(array(), function () {});
    }

    /**
     * Set a collection of data.
     *
     * @param array|\ArrayIterator $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the data transformer.
     *
     * @param callable|string $transformer
     *
     * @return $this
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }
} 