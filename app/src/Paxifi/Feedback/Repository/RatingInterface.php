<?php namespace Paxifi\Feedback\Repository;

interface RatingInterface
{
    /**
     * Increment the thumbs up.
     *
     * @return $this
     */
    public function thumbsUp();

    /**
     * Increment the thumbs down.
     *
     * @return $this
     */
    public function thumbsDown();
}