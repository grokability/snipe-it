<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\AssetModel;

class AssetModelObserver
{
    /**
     * Listen to the User created event.
     *
     * @return void
     */
    public function updating(AssetModel $model)
    {

        $changed = [];

        foreach ($model->getRawOriginal() as $key => $value) {
            // Check and see if the value changed
            if ($model->getRawOriginal()[$key] != $model->getAttributes()[$key]) {
                $changed[$key]['old'] = $model->getRawOriginal()[$key];
                $changed[$key]['new'] = $model->getAttributes()[$key];
            }
        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = AssetModel::class;
            $logAction->item_id = $model->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }

    }

    /**
     * Listen to the Location created event when
     * a new location is created.
     *
     * @return void
     */
    public function created(AssetModel $model)
    {
        $logAction = new Actionlog;
        $logAction->item_type = AssetModel::class;
        $logAction->item_id = $model->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        if ($model->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->logaction('create');
    }

    /**
     * Listen to the Location deleting event.
     *
     * @return void
     */
    public function deleting(AssetModel $model)
    {
        $logAction = new Actionlog;
        $logAction->item_type = AssetModel::class;
        $logAction->item_id = $model->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }

    public function restoring(AssetModel $model)
    {
        $logAction = new Actionlog;
        $logAction->item_type = AssetModel::class;
        $logAction->item_id = $model->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('restore');
    }
}
