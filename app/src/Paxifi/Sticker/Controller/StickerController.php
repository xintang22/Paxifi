<?php namespace Paxifi\Sticker\Controller;

use GrahamCampbell\Flysystem\FlysystemManager;
use Paxifi\Sticker\Repository\Factory\StickerFactory;
use Paxifi\Sticker\Repository\Validation\CreateStickerValidator;
use Paxifi\Sticker\Transformer\StickerTransformer;
use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class StickerController extends ApiController
{
    protected $flysystem;

    public function __construct(FlysystemManager $flysystem)
    {
        parent::__construct();

        $this->flysystem = $flysystem;
    }

    /**
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            if ($sticker = $driver->sticker) {
                return $this->setStatusCode(200)->respondWithItem($sticker);
            }

            return $this->setStatusCode(404)->respondWithError('Sticker is not exist.');
        } catch (\Exception $e) {
            return $this->errorInternalError('Internal error.');
        }
    }

    /**
     * Generate sticker
     *
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DriverRepositoryInterface $driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        // If sticker exist, fire update sticker event and return the updated sticker information.
        if ($driver->sticker) {
            $response = \Event::fire('update.sticker', [$driver]);

            return $response[0];
        }

        try {
            \DB::beginTransaction();

            $sticker_factory = with(new StickerFactory($this->flysystem))
                               ->setDriver($driver);

            $sticker_factory->buildSticker();

            $sticker_factory->saveStickerToPdf();

            // Get sticker information
            $stickerInfo = array_merge(["driver_id" => $driver->id], $sticker_factory->getStickerFilesPath());

            with(new CreateStickerValidator())->validate($stickerInfo);

            if ($sticker = $driver->sticker()->create($sticker_factory->getStickerFilesPath())) {
                \DB::commit();

                return $this->setStatusCode(201)->respondWithItem($sticker);
            }
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Update the sticker image and pdf file when user update the personal information.
     *
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            \DB::beginTransaction();

            $sticker_factory = with(new StickerFactory($this->flysystem))
                               ->setDriver($driver);

            $sticker_factory->buildSticker();

            $sticker_factory->saveStickerToPdf();

            if ($sticker = $driver->sticker->update($sticker_factory->getStickerFilesPath())) {

                \DB::commit();

                return $this->setStatusCode(201)->respondWithItem($driver->sticker);
            }
        } catch (\Exception $e) {
            return $this->errorWrongArgs($e->getMessage());
        }
    }

    /**
     * Push sticker to email pdf queue.
     */
    public function email($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $email = \Input::get('email', $driver->email);

            $sticker_factory = with(new StickerFactory($this->flysystem))
                ->setDriver($driver);

            $pdf = $this->flysystem->connection('awss3')->read($sticker_factory->getStickerPdfFilePath());
            $this->flysystem->connection('local')->put('sticker.pdf', $pdf);

            // Config email options
            $emailOptions = array(
                'template' => 'sticker.email',
                'context' => $this->translator->trans('email.sticker'),
                'to' => $email,
                'attach' => $this->flysystem->connection('local')->getAdapter()->getPathPrefix() . 'sticker.pdf',
                'as' => 'sticker_' . $driver->seller_id . '.pdf',
                'mime' => 'application/pdf',
                'data' => ['name' => $driver->name]
            );

            if (\Event::fire('paxifi.email', array($emailOptions))) {
                return $this->setStatusCode(200)->respond([
                    "success" => true
                ]);
            }

        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new StickerTransformer();
    }
}