<?php namespace Paxifi\Sticker\Repository\Factory;

use Paxifi\Store\Repository\Driver\Factory\DriverLogoFactory;
use Paxifi\Support\SavePdf\PdfConverter;

/**
 * Class StickerFactory
 * @package Paxifi\Sticker\Repository\Factory
 *
 * Extends from
 */
class StickerFactory extends DriverLogoFactory
{
    /**
     * File and asserts dirs
     *
     * @var
     */
    protected $stickerDir;

    /**
     * @var
     */
    protected $stickerPdfDir;

    /**
     * @var
     */
    protected $stickerLogoDir;

    /**
     * @var
     */
    protected $stickerTemplateDir;

    /**
     * @var
     */
    protected $stickerTemplateName;

    /**
     * @var string
     */
    protected $stickerLogo = '';

    /**
     * @var string
     */
    protected $stickerFileExt = '.jpg';

    /**
     * @var
     */
    protected $sticker;

    /**
     * @var string
     */
    protected $stickerFontColor = '#f89520';

    /**
     * @var int
     */
    protected $stickerFontSize = 130;

    /**
     * @var int
     */
    protected $canvasWidth = 2479;

    /**
     * @var int
     */
    protected $canvasHeight = 1750;

    /**
     * @var int
     */
    protected $logoWidth = 640;

    /**
     * @var int
     */
    protected $logoHeight = 640;

    /**
     * The x position for store seller id
     *
     * @var int
     */
    protected $textXPosition = 1690;

    /**
     * The Y position for store seller id
     *
     * @var int
     */
    protected $textYPosition = 1130;

    /**
     * The x position for store logo image
     *
     * @var int
     */
    protected $logoXPosition = 1350;

    /**
     * The Y position for store logo image
     *
     * @var int
     */
    protected $logoYPosition = 475;

    /**
     * @var string
     */
    protected $stickerFontAlign = "center";

    /**
     * @var string
     */
    protected $stickerFontValign = "top";

    /**
     * @var string
     */
    protected $stickerFontFile = 'fonts/HelveticaNeue-CondensedBold.otf';

    /**
     * Font options used to create sticker
     *
     * @var array
     */
    protected $fontOptions = [];

    /**
     * @internal param \Paxifi\Sticker\Repository\Factory\EloquentDriverRepository $driver
     */
    public function __construct()
    {
        parent::__construct();
        $this->stickerDir = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('images.stickers.img')));
        $this->stickerLogoDir = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('images.stickers.logo')));
        $this->stickerTemplateDir = str_replace('/', DIRECTORY_SEPARATOR, public_path(\Config::get('images.stickers.template')));
        $this->stickerPdfDir = \Config::get('pdf.stickers');
        $this->stickerTemplateName = \Config::get('stickers.template.name');
        $this->setDefaultFontOptions();
        $this->sticker = $this->getStickerInterventionCanvas();
    }

    /**
     *
     */
    public function setDefaultFontOptions()
    {
        $this->fontOptions = [
            'file' => public_path($this->stickerFontFile),
            'color' => $this->stickerFontColor,
            'size' => $this->stickerFontSize,
            'align' => $this->stickerFontAlign,
            'valign' => $this->stickerFontValign
        ];
    }

    /**
     * Set the font options
     */
    public function setFontOptions($options = [])
    {
        $this->fontOptions = [
            'file' => isset($options['file']) ? $options['file'] : public_path($this->fontOptions['file']),
            'color' => isset($options['color']) ? $options['color'] : $this->fontOptions['color'],
            'size' => isset($options['size']) ? $options['size'] : $this->fontOptions['size'],
            'align' => isset($options['align']) ? $options['align'] : $this->fontOptions['align'],
            'valign' => isset($options['valign']) ? $options['valign'] : $this->fontOptions['valign']
        ];
    }

    /**
     * Get the font settings for intervention image generate
     *
     * @return array
     */
    private function getFontOptions()
    {
        return $this->fontOptions;
    }

    /**
     * Get driver seller id
     *
     * @return mixed
     */
    public function getSellerId()
    {
        return $this->driver->seller_id;
    }

    /**
     * Get driver store logo
     *
     * @return mixed
     */
    public function getLogoImage()
    {
        return ($this->driver->photo) ? : $this->getDefaultLogo();
    }


    /**
     * Get the intervention image instance of driver store logo
     *
     * @return mixed
     */
    public function getInterventionLogo()
    {
        return \Image::make($this->getDriverLogoImagePath());
    }

    /**
     * Get the intervention image instance of sticker canvas template
     *
     * @return mixed
     */
    public function getStickerInterventionCanvas()
    {
        return \Image::canvas($this->canvasWidth, $this->canvasHeight, '#ffffff');
    }

    /**
     * Return the sticker image src path
     *
     * @return string
     */
    public function getStickerFileUrl()
    {
        return url(\Config::get('images.stickers.img') . $this->getDriverLogoImageName());
    }

    /**
     * Get the
     *
     * @return string
     */
    public function getStickerFilePath()
    {
        return $this->stickerDir . $this->getDriverLogoImageName();
    }

    /**
     * @return mixed
     */
    public function getStickerPdfFilePath()
    {
        return str_replace('/', DIRECTORY_SEPARATOR, public_path($this->stickerPdfDir . $this->getSellerId() . '.pdf'));
    }

    /**
     * @return string
     */
    public function getStickerPdfUrl()
    {
        return url($this->stickerPdfDir . $this->getSellerId() . '.pdf');
    }

    /**
     * Resize the driver logo image
     */
    public function resizeLogoImage()
    {
        $this->getInterventionLogo()->resize($this->logoWidth, $this->logoHeight)->save($this->stickerLogoDir . $this->getDriverLogoImageName());

        return $this;
    }

    /**
     * Insert the resized driver store logo into sticker intervention canvas
     */
    public function insertStickerLogo()
    {
        $this->sticker->insert(
            $this->stickerLogoDir . $this->getDriverLogoImageName(),
            '',
            $this->logoXPosition,
            $this->logoYPosition,
            'center'
        );

        return $this;
    }

    /**
     * Insert the sticker template into the sticker canvas
     */
    public function insertStickerTemplate()
    {
        $this->sticker->insert($this->stickerTemplateDir . $this->stickerTemplateName);

        return $this;
    }

    /**
     * Insert driver seller id text into sticker canvas
     */
    public function insertSellerId()
    {
        $font_options = $this->getFontOptions();

        $this->sticker->text('ID: ' . $this->getSellerId(), $this->textXPosition, $this->textYPosition, function ($font) use ($font_options) {
            $font->file($font_options['file']);
            $font->color($font_options['color']);
            $font->size($font_options['size']);
            $font->align($font_options['align']);
            $font->valign($font_options['valign']);
        });

        return $this;
    }

    /**
     * Save the generated sticker to image format
     */
    public function saveStickerToImage()
    {
        $this->sticker->save($this->stickerDir . $this->getDriverLogoImageName());
    }

    /**
     * Build the sticker image.
     */
    public function buildSticker()
    {
        $this->resizeLogoImage()
            ->insertStickerLogo()
            ->insertStickerTemplate()
            ->insertSellerId()
            ->saveStickerToImage();
    }

    /**
     * Delete sticker image
     */
    public function deleteSticker()
    {
        unlink($this->getStickerFilePath());
    }

    /**
     * delete resized sticker logo image.
     */
    public function deleteStickerLogo()
    {
        unlink($this->stickerLogoDir . $this->getDriverLogoImageName());
    }

    /**
     * check whether the sticker exist
     */
    public function checkStickerExist()
    {
        if (!file_exists($this->stickerDir . $this->getDriverLogoImageName())) {
            return false;
        }

        return true;
    }

    /**
     *  Save created sticker image to pdf
     */
    public function saveStickerToPdf()
    {
        $converter = new PdfConverter();

        $converter->setPdfFilePath($this->getStickerPdfFilePath());
        $converter->setHtmlTemplate($this->getStickerPdfTemplate());

        $converter->saveHtmlToPdf();
    }

    /**
     * Get sticker pdf template
     *
     * @return string
     */
    public function getStickerPdfTemplate()
    {
        $sticker = $this->getStickerFilePath();

        $html = '<html><head><style>html,body {padding: 0; margin: 0;} hr {padding: 2px 0; margin: 0;}</style></head>'
            . '<body>'
            . '<div>'
            . '<img src="' . $sticker . '"/>'
            . '<hr>'
            . '<img src="' . $sticker . '"/>'
            . '</div>'
            . '</body>'
            . '</html>';

        return $html;
    }

    /**
     * Get default logo image while driver haven't upload his store logo
     *
     * @return string
     */
    public function getDefaultLogo()
    {
        return $this->stickerTemplateDir . 'default_logo.png';
    }

    /**
     * Get all the file path and url path for sticker image and sticker pdf.
     *
     * @return array
     */
    public function getStickerFilesPath()
    {
        return [
            "image" => $this->getStickerFileUrl(),
            "image_path" => $this->getStickerFilePath(),
            "pdf" => $this->getStickerPdfUrl(),
            "pdf_path" => $this->getStickerPdfFilePath()
        ];
    }
}