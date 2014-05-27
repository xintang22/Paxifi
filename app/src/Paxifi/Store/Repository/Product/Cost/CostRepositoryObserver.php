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
    public function saved($cost)
    {
        $this->updateProductAverageCost($cost->product);
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
        $this->updateProductAverageCost($cost->product);
    }

    /**
     * Update the Product average cost.
     *
     * @param \Paxifi\Store\Repository\Product\EloquentProductRepository $product
     *
     * @return void
     */
    private function updateProductAverageCost($product)
    {
        $count = $product->costs->count();

        if ($count === 0) {

            $product->average_cost = 0;

        } else {

            $total = 0;

            foreach ($product->costs as $cost) {

                $total += $cost->cost;

            }

            $product->average_cost = $total / $count;
        }

        $product->save();
    }
} 