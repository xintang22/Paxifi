<?php namespace Paxifi\Support\SavePdf;

class PdfConverter
{
    /**
     * @var
     */
    protected $template = '';

    /**
     * The pdf file path want to save.
     *
     * @var
     */
    protected $pdfFilePath = 'pdf/';

    /**
     * The pdf size from a0 to a8
     *
     * @var
     */
    protected $size = 'a4';

    /**
     * @var string
     */
    protected $direction = 'portrait';

    /**
     * @param $template
     *
     * @return $this
     */
    public function setHtmlTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlTemplate()
    {
        return $this->template;
    }

    /**
     * @param $path
     *
     * @return $this
     */
    public function setPdfFilePath($path)
    {
        $this->pdfFilePath = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPdfFilePath()
    {
        return $this->pdfFilePath;
    }

    /**
     * @param $size
     *
     * @return $this
     */
    public function setPdfSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getPdfSize()
    {
        return $this->size;
    }

    /**
     * @param $direction
     *
     * @return $this
     */
    public function setPdfDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return string
     */
    public function getPdfDirection()
    {
        return $this->direction;
    }

    public function setDPI($dpi = null)
    {
        \Config::set('pdf::DOMPDF_DPI', $dpi);

        return $this;
    }

    /**
     * @return bool
     */
    public function saveHtmlToPdf()
    {
        if (
        \File::put(
            $this->getPdfFilePath(),
            \PDF::load(
                $this->getHtmlTemplate(),
                $this->getPdfSize(),
                $this->getPdfDirection()
            )->output()
        )
        ) {

            return true;
        }

        return false;
    }
}