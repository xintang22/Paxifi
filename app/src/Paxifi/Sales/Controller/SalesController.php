<?php namespace Paxifi\Sales\Controller;

use Carbon\Carbon;
use GrahamCampbell\Flysystem\FlysystemManager;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Sales\Repository\SaleCollection;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\BaseApiController;
use Illuminate\Support\Collection;
use Paxifi\Support\SavePdf\PdfConverter;

class SalesController extends BaseApiController
{
    protected $flysystem;

    function __construct(FlysystemManager $flysystem)
    {
        parent::__construct();
        $this->flysystem = $flysystem;
    }

    /**
     * Display a listing of all sales.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(EloquentDriverRepository $driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        $from = ($from = (int)\Input::get('from')) ? Carbon::createFromTimestamp($from) : $driver->created_at;
        $to = ($to = (int)\Input::get('to')) ? Carbon::createFromTimestamp($to) : Carbon::now();

        $sales = new SaleCollection($driver->sales($from, $to));

        return $this->respond($sales->toArray());
    }

    /**
     * Response for paginated sales response.
     *
     * @param EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function histories(EloquentDriverRepository $driver = null) {

        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        $from = ($from = (int)\Input::get('from')) ? Carbon::createFromTimestamp($from) : $driver->created_at;
        $to = ($to = (int)\Input::get('to')) ? Carbon::createFromTimestamp($to) : Carbon::now();

        if (\Input::has('page')) {
            $paginator = ['page' => \Input::get('page'), 'per_page' => \Input::get('per_page', 6)];

            $sales = new SaleCollection($driver->sales($from, $to, $paginator)->toArray()['data']);
        } else {
            $sales = new SaleCollection($driver->sales($from, $to));
        }

        return $this->respond($sales->toArray());
    }

    public function report(EloquentDriverRepository $driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $email = \Input::get('email', $driver->email);
            $year = \Input::get('year', Carbon::now()->year);

            $report_path = \Input::get('pdf.reports', 'reports/pdf/') . $driver->id . '-' . $year . '-report' . '.pdf';
            $report_template = \Input::get('report.sales', 'report.sales');

            $salesIds = $driver->sales(Carbon::create($year, 1, 1, 0, 0), Carbon::create($year+1, 1, 1, 0, 0));

            // Total Sales
            $sales = new SaleCollection($salesIds);
            $statistics = $sales->toArray();

            $reports = [];

            // Monthly Reports.
            for($i = 1; $i <= 12; $i++) {
                $reports[$i][Carbon::create($year, $i)->format('F')] = [
                    "total_sales" => 0,
                    "total_tax" => 0,
                    "profit" => 0,
                    "commission" => 0,
                ];
                // Monthly Report.
                $sales->each(function($sale) use(&$reports, &$year, &$i) {
                    if ($sale->toArray()['created_at_year'] == $year && $sale->toArray()['created_at_month'] == $i) {
                        $reports[$i][Carbon::create($year, $i)->format('F')]['total_sales'] += $sale->toArray()['total_sales'];
                        $reports[$i][Carbon::create($year, $i)->format('F')]['total_tax'] += $sale->toArray()['total_tax'];
                        $reports[$i][Carbon::create($year, $i)->format('F')]['profit'] += $sale->toArray()['profit'];
                        $reports[$i][Carbon::create($year, $i)->format('F')]['commission'] += $sale->toArray()['commission'];
                    }
                });
            }

            $htmlTemplate = \View::make($report_template)->with(compact('year', 'statistics', 'driver', 'reports'));

            $converter = new PdfConverter();

            $converter->setPdfDirection('landscape');
            $converter->setPdfFilePath($report_path);
            $converter->setHtmlTemplate($htmlTemplate);

            $converter->saveHtmlToPdf();

            // Config email options for send sales report.
            $emailOptions = array(
                'template' => 'report.email',
                'context' => $this->translator->trans('email.report'),
                'to' => $email,
                'attach' => $this->flysystem->getAdapter()->getClient()->getObjectUrl(getenv('AWS_S3_BUCKET') , $report_path),
                'as' => 'Paxifi Sales Monthly Report -' . $year . '.pdf',
                'mime' => 'application/pdf',
                'data' => ['name' => $driver->name, 'year' => $year]
            );

            if (\Event::fire('paxifi.email', array($emailOptions))) {
                return $this->setStatusCode(200)->respond([
                    "success" => true
                ]);
            }
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Display the sales forecasts.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forecasts(EloquentDriverRepository $driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        $totalItems = 0;
        $totalSales = 0;
        $totalCosts = 0;

        $driver->products()->get()->each(function ($product) use (&$totalItems, &$totalSales, &$totalCosts) {
            $totalItems += $product->inventory;
            $totalSales += $product->unit_price * $product->inventory;
            $totalCosts += $product->average_cost * $product->inventory;
        });

        $commissionRate = \Config::get('paxifi.commission.rate', 0);
        $totalCommission = $commissionRate * $totalSales;

        $totalProfit = $totalSales - $totalCosts - $totalCommission;

        return $this->respond(array(
            'forecasts' => array(
                'sales' => $totalSales,
                'profit' => $totalProfit,
//                'commission' => $totalCommission,
                'items' => $totalItems,
            ),
            'date' => (string)Carbon::now(),
        ));
    }
}