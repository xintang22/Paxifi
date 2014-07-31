<?php namespace Paxifi\Issue\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentIssueTypesRepository extends BaseModel {
    protected $table = "issue_types";

    protected $fillable = ['name', 'enabled', 'description', 'email'];
} 