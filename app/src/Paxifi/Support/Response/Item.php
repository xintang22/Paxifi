<?php namespace Paxifi\Support\Response;

use League\Fractal\Resource\Item as FractalItem;

/**
 * Item resource.
 *
 * @package Paxifi\Support\Response
 */
class Item extends FractalItem
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