<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * Class StickerServiceProvider
 *
 * @package Paxifi\Provider
 */
class StickerServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStickerRepository();

        $this->registerEvent();
    }

    /**
     * Register Sticker Repository
     */
    public function registerStickerRepository()
    {
        $this->app->bind('paxifi.repository.sticker', 'Paxifi\Sticker\Repository\EloquentStickerRepository');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.sticker');
    }

    public function boot()
    {
        $this->app['config']->set('stickers.template.name', 'sticker_template.png');

        $config = [
            'images.stickers.img' => 'stickers/img/',
            'images.stickers.logo' => 'stickers/logo/',
            'images.stickers.template' => 'images/stickers/template/',
            'pdf.stickers' => 'stickers/pdf/'
        ];

        array_walk($config, function ($value, $key) {
            $this->app['config']->set($key, $value);

            $path = str_replace('/', DIRECTORY_SEPARATOR, public_path($value));

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0777, true);
            }
        });
    }

    /**
     * Register order events.
     */
    public function registerEvent()
    {
        \Event::listen('email.sticker', function ($emailOptions) {
            try {
                \Queue::push('Paxifi\Sticker\Queue\StickerQueues@email', $emailOptions);

                return true;
            } catch(\Exception $e) {
                return false;
            }
        });
        
        \Event::listen('update.sticker', 'Paxifi\Sticker\Controller\StickerController@update');
    }
}