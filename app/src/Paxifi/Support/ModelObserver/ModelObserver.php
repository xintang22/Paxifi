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
     * Update / Clear cache.
     *
     * @param $model
     */
    protected function updateOrClearCache($model)
    {
        if ($this->hasIdKey($model)) {
            if (Cache::tags($model->getTable())->get($model->id)) {
                Cache::tags($model->getTable())->put($model->id, $model, 10);
            }
        } else {
            $this->clearCacheTags($model->getTable());
        }
    }

    /**
     * Check if model primary key is id.
     *
     * @param $model
     * @return mixed
     */
    private function hasIdKey($model)
    {
        return $model->getKeyName() == 'id';
    }

    /**
     * Clear / Update cache while model saved.
     *
     * @param $model
     */
    public function saved($model)
    {
        $this->updateOrClearCache($model);
    }

    /**
     * Clear cache while model deleted.
     *
     * @param $model
     */
    public function deleted($model)
    {
        if ($this->hasIdKey($model)) {
            if (Cache::tags($model->getTable())->get($model->id)) {
                Cache::tags($model->getTable())->forget($model->id);
            }
        } else {
            $this->clearCacheTags($model->getTable());
        }
    }
}