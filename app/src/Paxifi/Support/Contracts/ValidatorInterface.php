<?php namespace Paxifi\Support\Contracts;

interface ValidatorInterface
{
    /**
     * Returns validation rules
     *
     * @return array
     */
    public function getRules();

    /**
     * Validates the $data against the rules
     *
     * @param $data
     *
     * @return bool
     */
    public function validate($data);

    /**
     * Returns a list of errors.
     *
     * @return array
     */
    public function getValidationErrors();
}