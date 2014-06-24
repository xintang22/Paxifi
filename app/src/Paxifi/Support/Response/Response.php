<?php namespace Paxifi\Support\Response;

use League\Fractal\Manager;

/**
 * The Response class helper
 * @package Paxifi\Support\Response
 */
class Response
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var \League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * @var string|array|object
     */
    protected $content;

    /**
     * @var \Paxifi\Support\Response\Paginator
     */
    protected $paginator;

    /**
     * @var \Paxifi\Support\Response\Cursor
     */
    protected $cursor;

    /**
     * @var \Paxifi\Support\Response\Item
     */
    protected $item;

    /**
     * @var \Paxifi\Support\Response\Collection
     */
    protected $collection;

    /**
     * @var \League\Fractal\Manager;
     */
    protected $fractal;

    /**
     * Create the Response instance.
     *
     * @param Manager $fractal
     * @param Item $item
     * @param Collection $collection
     * @param Paginator $paginator
     * @param Cursor $cursor
     */
    function __construct(Manager $fractal, Item $item, Collection $collection, Paginator $paginator, Cursor $cursor)
    {
        $this->fractal = $fractal;
        $this->item = $item;
        $this->collection = $collection;
        $this->paginator = $paginator;
        $this->cursor = $cursor;
    }

    /**
     * Sets the collection resource.
     *
     * @param \Paxifi\Support\Response\Collection $collection
     *
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Retrieves the collection resource.
     *
     * @return \Paxifi\Support\Response\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Sets the response content.
     *
     * @param array|object|string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Retrieves the response content.
     *
     * @return array|object|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the cursor instance.
     *
     * @param \Paxifi\Support\Response\Cursor $cursor
     *
     * @return $this
     */
    public function setCursor($cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }

    /**
     * Retrieves the cursor instance.
     *
     * @return \Paxifi\Support\Response\Cursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Sets the response headers.
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Retrieves the response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the item resource.
     *
     * @param \Paxifi\Support\Response\Item $item
     *
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Retrieves the item resource.
     *
     * @return \Paxifi\Support\Response\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the paginator instance.
     *
     * @param \Paxifi\Support\Response\Paginator $paginator
     *
     * @return $this
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Retrieves the paginator instance.
     *
     * @return \Paxifi\Support\Response\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Sets the response status code.
     *
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Retrieves the response status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the transformer instance.
     *
     * @param \League\Fractal\TransformerAbstract $transformer
     *
     * @return $this
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Retrieves the transformer instance.
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Responds with single resource.
     *
     * @return array
     */
    public function withItem()
    {
        $this->item->setData($this->content);
        $this->item->setTransformer($this->transformer);

        return $this->fractal->createData($this->item)->toArray();
    }

    /**
     * Responds with collection.
     *
     * @param bool $paginationEnabled
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function withCollection($paginationEnabled = false)
    {
        $this->collection->setData($this->content);
        $this->collection->setTransformer($this->transformer);

        $this->paginate($paginationEnabled);

        return $this->fractal->createData($this->collection)->toArray();
    }

    /**
     * Paginates the collection response.
     *
     * @param $paginationEnabled
     *
     * @throws \InvalidArgumentException
     */
    protected function paginate($paginationEnabled)
    {
        if ($paginationEnabled) {

            if (!$this->content instanceof Paginator) {
                throw new \InvalidArgumentException();
            }

            $this->paginator->setupPaginator($this->content);

            $this->collection->setPaginator($this->paginator);
        }
    }

    /**
     * Sets the scopes to be embedded in the response.
     *
     * @param array $scopes
     *
     * @return $this
     */
    public function setRequestedScopes(array $scopes)
    {
        $this->fractal->setRequestedScopes($scopes);

        return $this;
    }

} 