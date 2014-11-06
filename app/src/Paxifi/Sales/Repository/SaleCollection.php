<?php namespace Paxifi\Sales\Repository;

use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository;

class SaleCollection extends Collection
{
    /**
     * @var float
     */
    protected $totalCosts;
    /**
     * @var int
     */
    protected $totalItems;
    /**
     * @var float
     */
    protected $totalProfit;
    /**
     * @var float
     */
    protected $totalCommission;
    /**
     * @var float
     */
    protected $totalSales;
    /**
     * @var float
     */
    protected $totalTax;

    function __construct(array $salesIds = array())
    {

        foreach ($salesIds as $sale) {

            $this->push(new SaleRepository(EloquentOrderRepository::find($sale->id)));
        }

        $this->calculateTotals();
    }

    /**
     * Calculate totals
     */
    protected function calculateTotals()
    {
        $this->each(function ($item) {
            $this->totalCosts += $item->getTotalCosts();
            $this->totalItems += $item->getTotalItems();
            $this->totalProfit += $item->getProfit();
            $this->totalCommission += $item->getCommission();
            $this->totalSales += $item->getTotalSales();
            $this->totalTax += $item->getTotalTax();
        });
    }

    /**
     * Get the collection of sales as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'totals' => array(
                'sales' => $this->totalSales,
                'costs' => $this->totalCosts,
                'profit' => $this->totalProfit,
                'commission' => $this->totalCommission,
                'tax' => $this->totalTax,
                'items' => $this->totalItems,
            ),
            'sales' => parent::toArray()
        );
    }
}