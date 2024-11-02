<?php

namespace App\Http\Services\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Support\Facades\Log;

class ProfileService extends BaseResponse
{
    public function update($data): array
    {
        try {
            $message = __('Update profile successfully');

            $user = auth()->user();

            if (! empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }

            $user = $user->updateById($user->id, $data, true);

            $resource = new UserResource($user);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->responseError(__('Update profile failed'), 500, $th->getMessage());
        }

        return $this->responseSuccess($message, 200, $resource);
    }
}
