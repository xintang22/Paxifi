<?php namespace Paxifi\Issue\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Issue\Repository\IssueRepository;

class IssueTransformer extends TransformerAbstract
{
    public function transform(IssueRepository $issue)
    {
        return $issue;
    }
} 