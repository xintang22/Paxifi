<?php namespace Paxifi\Issue\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentIssueRepository extends BaseModel {
    protected $table = 'issues';

    protected $fillable = ['issue_type_id', 'email', 'subject', 'content'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('Paxifi\Issue\Repository\EloquentIssueTypesRepository', 'issue_type_id');
    }
} 