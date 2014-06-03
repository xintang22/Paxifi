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
        $this->updateProduct($cost->product);
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
        $this->updateProduct($cost->product);
    }

    /**
     * Update the Product average cost and inventory.
     *
     * @param \Paxifi\Store\Repository\Product\EloquentProductRepository $product
     *
     * @return void
     */
    private function updateProduct($product)
    {
        $count = $product->costs->count();

        if ($count === 0) {

            $product->average_cost = 0;
            $product->inventory = 0;

        } else {

            $totalCost = 0;
            $totalInventory = 0;

            foreach ($product->costs as $cost) {

                $totalCost += $cost->unit_cost;
                $totalInventory += $cost->inventory;

            }

            $product->average_cost = round($totalCost / $count, 2);
            $product->inventory = $totalInventory;
        }

        $product->save();
    }
} 