<?php namespace Paxifi\Payment\Repository\Factory;

set_time_limit(0);

use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Store\Repository\Driver\Factory\DriverLogoFactory;
use Paxifi\Support\SavePdf\PdfConverter;

/**
 * Class OrderInvoiceFactory
 * @package Paxifi\Order\Repository\Factory
 */
class PaymentInvoiceFactory extends DriverLogoFactory
{

    /**
     * @var \Paxifi\Order\Repository\EloquentOrderRepository
     */
    protected $order;

    /**
     * @var string
     */
    protected $invoicePdfSize = 'a4';

    /**
     * @var int
     */
    protected $invoicePDI = 74;

    /**
     * @var mixed
     */
    protected $invoicePdfFilePath;

    /**
     * @var mixed
     */
    protected $logoTempFilePath;

    /**
     * @var int
     */
    protected $logoTempWidth = 90;

    /**
     * @var int
     */
    protected $logoTempHeight = 90;

    /**
     * @var int
     */
    protected $logoWidth = 74;

    /**
     * @var int
     */
    protected $logoHeight = 74;

    /**
     * @var mixed
     */
    protected $driverLogoCircleCover;

    /**
     * @var
     */
    protected $translation;

    /**
     * @var string
     */
    protected $defaultDriverLogoName = "driver_logo.png";

    /**
     * @var string
     */
    protected $paxifiLogoName = "logo.png";

    /**
     * The construct of invoice pdf factory
     */
    public function __construct(EloquentOrderRepository $order, $translation)
    {
        //init the parent construct
        parent::__construct();

        $this->translation = $translation;

        $this->order = $order;

        // invoice pdf path.
        $this->invoicePdfFilePath = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('pdf.invoices') . $order->id . '.pdf'));
        $this->invoicePdfUrlPath = str_replace('/', DIRECTORY_SEPARATOR, url(\Config::get('pdf.invoices') . $order->id . '.pdf'));

        // template files.
        $this->driverLogoCircleCover = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('images.invoices.template') . 'driver_logo_bg.png'));
        $this->paxifiLogoFilePath = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('images.invoices.template') . $this->paxifiLogoName));
        $this->paxifiLogoUrlPath = str_replace('/', DIRECTORY_SEPARATOR, url(\Config::get('images.invoices.template') . $this->paxifiLogoName));
        $this->defaultDriverLogoFilePath = str_replace('/', DIRECTORY_SEPARATOR, url(\Config::get('images.invoices.template') . $this->defaultDriverLogoName));
        $this->defaultDriverLogoUrlPath = str_replace('/', DIRECTORY_SEPARATOR, url(\Config::get('images.invoices.template') . $this->defaultDriverLogoName));

        $this->setDriver($this->getOrderDriver());
    }

    /**
     * Get the products which list in the order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getOrderProducts()
    {
        return $this->order->products()->get();
    }

    /**
     * Get the driver information which the order belongs to.
     *
     * @return mixed
     */
    private function getOrderDriver()
    {
        return $this->order->products()->first()->driver()->get()->first();
    }


    /**
     * Get the invoice pdf file path.
     *
     * @return string
     */
    public function getPdfFilePath()
    {
        return $this->invoicePdfFilePath;
    }

    /**
     * Get the invoice pdf url path.
     *
     * @return string
     */
    public function getPdfUrlPath()
    {
        return $this->invoicePdfUrlPath;
    }

    /**
     * Get the intervention circle cover template.
     *
     * @return mixed
     */
    public function getInterventionInvoiceLogoCircleCoverTemplate()
    {
        return \Image::make($this->driverLogoCircleCover);
    }

    /**
     * Get the intervention logo template.
     *
     * @return mixed
     */
    public function getInterventionLogoTemplate()
    {
        return \Image::make($this->logoTempFilePath);
    }

    /**
     * @internal param $data
     * @return mixed
     */
    public function getInvoiceTemplate()
    {
        return \View::make('invoice.invoice')->with($this->getInvoiceData());
    }

    /**
     * Get all the images file path and url path for generate the invoice pdf and invoice email.
     *
     * @return array
     */
    public function getInvoiceData()
    {
        $invoice = [];

        $invoice['order'] = $this->order->toArray();
        $invoice['products'] = $this->order->products->toArray();
        $invoice['driver'] = $this->getOrderDriver()->toArray();
        $invoice['content'] = $this->translation;
        $invoice['template'] = [
            "email_logo" => $this->paxifiLogoUrlPath,
            "pdf_logo" => $this->paxifiLogoFilePath,
            "email_driver_logo" => $this->getDriverLogoImageUrl(),
            "pdf_driver_logo" => $this->getDriverLogoImagePath()
        ];

        return $invoice;
    }

    /**
     * Build the invoice pdf.
     */
    public function build()
    {
        $this->saveInvoiceToPdf();
    }

    /**
     *  Save created sticker image to pdf
     */
    public function saveInvoiceToPdf()
    {

        $converter = new PdfConverter();

        $converter->setPdfSize('a4')
            ->setPdfFilePath($this->invoicePdfFilePath)
            ->setHtmlTemplate($this->getInvoiceTemplate())
            ->setDPI(74);

        $converter->saveHtmlToPdf();
    }
} 