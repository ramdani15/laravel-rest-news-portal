<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait MainModel
{
    /**
     * Created with Log
     *
     * @param  array  $data
     * @param  bool  $log
     */
    public function createWithLog($data = []): object
    {
        $model = $this->create($data);
        $user = auth()->user();
        if ($user) {
            $model->logs()->create([
                'user_id' => $user->id,
                'log_type' => 'create',
                'log_data' => json_encode($data),
            ]);
        }

        return $model;
    }

    /**
     * Update Row by ID with Log
     *
     * @param  int  $id
     * @param  array  $data
     */
    public function updateWithLog($id, $data = []): object
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        $changes = $model->getChanges();

        /* Insert the changes into ActivityLog */
        if (! empty($changes)) {
            $user = auth()->user();
            if ($user) {
                $model->logs()->create([
                    'user_id' => $user->id,
                    'log_type' => 'update',
                    'log_data' => json_encode($changes),
                ]);
            }
        }

        return $model;
    }

    /**
     * Delete Row by ID with Log
     *
     * @param  int  $id
     */
    public function deleteById($id, $log = false): bool
    {
        $model = $this->findOrFail($id);
        /* Insert the changes into ActivityLog */
        if ($log) {
            $user = auth()->user();
            if ($user) {
                $model->logs()->create([
                    'user_id' => $user->id,
                    'log_type' => 'delete',
                    'log_data' => json_encode($model),
                ]);
            }
        }

        return $model->delete();
    }

    /**
     * First or Create with Log
     *
     * @param  array  $data
     * @param  bool  $log
     */
    public function firstOrCreateWithLog($data = [], $log = false): object
    {
        $model = $this->firstOrCreate($data);
        if ($log) {
            $user = auth()->user();
            if ($user) {
                $model->logs()->create([
                    'user_id' => $user->id,
                    'log_type' => 'create',
                    'log_data' => json_encode($data),
                ]);
            }
        }

        return $model;
    }

    /**
     * Get all of the table's logs
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'logable');
    }
}
