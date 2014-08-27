<?php namespace Paxifi\Subscription\Repository;

interface SubscriptionRepositoryInterface {
    public function active();

    public function expired();

    public function canceled();
} 