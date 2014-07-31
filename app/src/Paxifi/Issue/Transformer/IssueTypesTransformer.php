<?php namespace Paxifi\Issue\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Issue\Repository\EloquentIssueTypesRepository;

class IssueTypesTransformer extends TransformerAbstract {
    public function transform(EloquentIssueTypesRepository $issue_type)
    {
        return $issue_type;
    }
}