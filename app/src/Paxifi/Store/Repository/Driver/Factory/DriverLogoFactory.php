<?php namespace Paxifi\Store\Repository\Driver\Factory;

use Paxifi\Support\SavePdf\PdfConverter;

class DriverLogoFactory
{
    /**
     * @var
     */
    protected $imageManager;

    /**
     * @var
     */
    protected $driver;

    /**
     * The intervention driver logo.
     *
     * @var
     */
    protected $driverLogo;

    /**
     * Driver logo name
     *
     * @var
     */
    protected $driverLogoName;

    /**
     * @var
     */
    protected $driverLogoFolder;

    /**
     * @var string
     */
    protected $driverUploadFolder = 'uploads/';

    /**
     * @var int
     */
    protected $circleWidth = 96;

    /**
     * @var int
     */
    protected $circleHeight = 96;

    /**
     * @var int
     */
    protected $driverLogoWidth = 96;

    /**
     * @var int
     */
    protected $driverLogoHeight = 96;

    /**
     * @var string
     */
    protected $driverLogoCircleCover;

    /**
     * @var
     */
    protected $secure = false;


    /**
     * @internal param $driver
     */
    public function __construct()
    {
        $this->imageManager = \App::make('paxifi.image');

        $this->secure = \Config::get('app.secure');

        $this->driverLogoFolder = \Config::get('images.drivers.logo');
        $this->driverLogoCircleCover = \Config::get('images.drivers.template') . 'driver_logo_bg.png';
        $this->driverLogoDefaultTemplate = \Config::get('images.drivers.template') . 'driver_logo.png';
    }

    /**
     * Set the driver object.
     *
     * @param $driver
     *
     * @return $this
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return string
     */
    public function getDriverLogoCircleCoverPath()
    {
        return public_path($this->driverLogoCircleCover);
    }

    /**
     * @return string
     */
    public function getDriverLogoCircleCoverUrl()
    {
        return cloudfront_asset($this->driverLogoCircleCover);
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function getDriverLogoImageName()
    {
        $this->driverLogoName = empty($this->driver->photo) ? $this->driver->seller_id . '.jpg' : basename($this->driver->photo);

        return $this->driverLogoName;
    }

    /**
     * @return string
     */
    public function getDriverDefaultLogo()
    {
        return public_path($this->driverLogoDefaultTemplate);
    }

    /**
     * @return string
     */
    public function getDriverDefaultLogoUrl()
    {
        return url($this->driverLogoDefaultTemplate, null, $this->secure);
    }

    /**
     * Get driver logo path.
     *
     * @return string
     */
    public function getDriverLogoImagePath()
    {
        if (empty($this->driver->photo)) {
            return $this->getDriverDefaultLogo();
        }

        return $this->driver->photo;
    }

    /**
     * Get driver logo url.
     *
     * @return string
     */
    public function getDriverLogoImageUrl()
    {
        if (empty($this->driver->photo)) {
            return $this->getDriverDefaultLogoUrl();
        }

        return $this->driver->photo;
    }

    /**
     * Get the intervention image instance of driver store logo
     *
     * @return mixed
     */
    public function getInterventionLogo()
    {
        return $this->imageManager->make($this->getDriverLogoImagePath());
    }

    /**
     * use the uploaded driver image to generate driver circle image.
     *
     * @throws \Exception
     * @return $this
     */
    public function setDriveLogoInterventionCanvas()
    {
        $file = !empty($this->driver->photo) ? $this->getDriverLogoImagePath() : $this->getDriverDefaultLogo();

        if (!file_exists($file) || empty($file)) {
            throw new \RuntimeException('Driver logo image is not exist');
        }

        $this->driverLogo = \Image::make($file);

        return $this;
    }

    /**
     * @param null $width
     * @param null $height
     *
     * @return $this
     */
    public function resizeDriverLogo($width = null, $height = null)
    {

        $this->driverLogo->resize($width, $height)->save($this->getDriverLogoImagePath());

        return $this;
    }

    /**
     * @return $this
     */
    public function insertDriverCircleTemplate()
    {
        $this->driverLogo->insert(
            $this->getDriverLogoCircleCoverPath(),
            '',
            0,
            0,
            'center'
        )->save($this->getDriverLogoImagePath());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDriverLogoTransparentCircleInterventionTemplate()
    {
        return \Image::make($this->getDriverLogoCircleCoverPath());
    }

    /**
     * Build driver logo image according to driver "seller_id".
     */
    public function buildDriverLogo()
    {
        $this->resizeCircleTemplate();

        $this->setDriveLogoInterventionCanvas()
            ->resizeDriverLogo($this->driverLogoWidth, $this->driverLogoHeight)
            ->insertDriverCircleTemplate();


        return array(
            "logo_path" => $this->getDriverLogoImagePath(),
            "logo_url" => $this->getDriverLogoImageUrl()
        );
    }

    /**
     * Resize the circle template.
     */
    public function resizeCircleTemplate($x = null, $y = null)
    {
        $this->circleWidth = $x ? : $this->circleWidth;
        $this->circleHeight = $y ? : $this->circleHeight;

        $circle = $this->getDriverLogoTransparentCircleInterventionTemplate();

        $circle->resize($this->circleWidth, $this->circleHeight)->save($this->getDriverLogoCircleCoverPath());
    }
} 