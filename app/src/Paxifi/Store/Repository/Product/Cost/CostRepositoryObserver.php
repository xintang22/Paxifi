<?php namespace Paxifi\Store\Repository\Product\Cost;

class CostRepositoryObserver
{

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  $cost
     *
     * @return void
     */
    public function created($cost)
    {
        // TODO: Update the product average cost and inventory
    }

    /**
     * Register an updated model event with the dispatcher.
     *
     * @param  $cost
     *
     * @return void
     */
    public function updated($cost)
    {
        // TODO: Update the product average cost and inventory
    }

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param  $cost
     *
     * @return void
     */
    public function deleted($cost)
    {
        // TODO: Update the product average cost and inventory
    }
} 