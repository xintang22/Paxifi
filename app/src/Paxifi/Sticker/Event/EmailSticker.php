<?php
namespace Paxifi\Sticker\Event;

use GrahamCampbell\Flysystem\FlysystemManager;
use Paxifi\Sticker\Repository\Factory\StickerFactory;

class EmailSticker {

    protected $flysystem;

    protected $translator;

    public function __construct(FlysystemManager $flysystem)
    {
        $this->flysystem = $flysystem;
        $this->translator = \App::make('translator');
    }

    /**
     * Handler for send email
     *
     * @param $driver
     * @param $email
     */
    function handle ($driver, $email = null) {
        if (is_null($email)) {
            $email = $driver->email;
        }

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

        \Event::fire('paxifi.email', array($emailOptions));
    }
} 