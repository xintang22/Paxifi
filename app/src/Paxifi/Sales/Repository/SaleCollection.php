<?php namespace Paxifi\Sales\Repository;

use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository;

class SaleCollection extends Collection
{
    /**
     * @var
     */
    protected $sales;

    /**
     * @var int
     */
    protected $per;

    /**
     * @var int
     */
    protected $page;

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

    function __construct(array $salesIds = array(), $page, $per)
    {
        $this->page = $page;
        $this->per = $per;

        $this->sales = new Collection();

        foreach ($salesIds as $index => $sale) {

            if (($index >= ($this->page -1) * $this->per) && ($index < ($this->page) * $this->per)) {
                $this->push(new SaleRepository(EloquentOrderRepository::find($sale->id)));
            }

            $this->sales->push(new SaleRepository(EloquentOrderRepository::find($sale->id)));
        }

        $this->calculateTotals($this->sales);
    }

    /**
     * Calculate totals
     */
    protected function calculateTotals($sales)
    {
        $sales->each(function ($item) {
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
            'sales' => parent::toArray(),
        );
    }
}