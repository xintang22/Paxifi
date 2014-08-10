<?php namespace Paxifi\Issue\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreateIssueValidator extends Validator {
    protected $rules = [
        "email" => "required|email",
        "subject" => "required",
        "content" => "required",
        "issue_type_id" => "required"
    ];
} 