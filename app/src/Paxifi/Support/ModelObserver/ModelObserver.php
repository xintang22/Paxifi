<?php namespace Paxifi\Support\ModelObserver;

use Cache;

class ModelObserver
{

    /**
     * Flush the cache tags.
     *
     * @param $tags
     */
    protected function clearCacheTags($tags)
    {
        if (Cache::getDefaultDriver() != "file" && Cache::getDefaultDriver() != "database") {
            Cache::tags($tags)->flush();
        }
    }

    /**
     * Clear / Update cache while model saved.
     *
     * @param $model
     */
    public function saved($model)
    {
        $this->clearCacheTags($model->getTable());
    }

    /**
     * Clear cache while model created.
     *
     * @param $model
     */
    public function created($model)
    {
        $this->clearCacheTags($model->getTable());
    }

    /**
     * Clear cache while model deleted.
     *
     * @param $model
     */
    public function deleted($model)
    {
        $this->clearCacheTags($model->getTable());
    }
}