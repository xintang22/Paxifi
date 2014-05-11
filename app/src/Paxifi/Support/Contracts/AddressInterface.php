<?php namespace Paxifi\Support\Contracts;

interface AddressInterface
{
    public function getCity();

    public function getCountry();

    public function getPostcode();

    public function getStreet();
} 