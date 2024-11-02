<?php

namespace App\Http\Services\Api\V1;

use App\Enums\ReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ReactionService extends BaseResponse
{
    /**
     * Toggle the like or dislike reaction.
     *
     * @param  Model  $model  The model instance (Article or Comment)
     * @param  string  $type  The type of reaction ('like' or 'dislike')
     * @param  int  $userId  The ID of the user reacting
     * @return BaseResponse::response
     */
    public function toggleReaction(Model $model, ReactionType $type, int $userId)
    {
        try {
            // Check if the reaction already exists
            $reaction = $model->reactions()
                ->where('user_id', $userId)
                ->where('type', $type)
                ->first();

            if ($reaction) {
                // Reaction exists, so remove it (unlike or undislike)
                $reaction->delete();
                $result = $type == ReactionType::LIKE ? 'unliked' : 'undisliked';
            } else {
                // Reaction doesn't exist, so create it (like or dislike)
                $model->reactions()->create([
                    'user_id' => $userId,
                    'type' => $type,
                ]);
                $result = $type == ReactionType::LIKE ? 'liked' : 'disliked';
            }

            return $this->responseSuccess(__('Successfully toggle reaction to ').$result);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->responseError(__('Failed toggle reaction'), 500, $th->getMessage());
        }
    }
}
