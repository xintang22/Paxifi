<?php namespace Paxifi\Settings\Controller;

use Paxifi\Settings\Repository\EloquentCountryRepository;
use Paxifi\Support\Controller\ApiController;

class SettingsController extends ApiController
{
    /**
     * Returns all active countries.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getCountries()
    {
        return EloquentCountryRepository::all();
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        // TODO: Implement getTransformer() method.
    }
}