<?php namespace Paxifi\Feedback\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Feedback\Repository\EloquentFeedbackRepository;

class FeedbackTransformer extends TransformerAbstract
{
    public function transform(EloquentFeedbackRepository $feedback)
    {
        return $feedback;
    }
} 