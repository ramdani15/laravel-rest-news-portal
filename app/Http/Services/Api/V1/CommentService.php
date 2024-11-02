<?php

namespace App\Http\Services\Api\V1;

use App\Enums\CommentType;
use App\Enums\ReactionType;
use App\Http\Filters\Api\V1\ByArticleId;
use App\Http\Filters\Api\V1\ByContent;
use App\Http\Filters\Api\V1\ByParentId;
use App\Http\Filters\Api\V1\ByRangeCreatedAt;
use App\Http\Filters\Api\V1\OrderBy;
use App\Http\Resources\Api\V1\Article\DetailResource;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Facades\App\Http\Services\Api\V1\ReactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentService extends BaseResponse
{
    /**
     * Get list of comments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            if (! ($request->has('sort_by') && $request->has('sort'))) {
                $request->merge([
                    'sort_by' => 'created_at',
                    'sort' => -1,
                ]);
            }
            $query = Comment::query();
            $piplines = [
                ByArticleId::class,
                ByParentId::class,
                ByContent::class,
                ByRangeCreatedAt::class,
                OrderBy::class,
            ];

            $data = $this->filterPagination($query, $piplines, $request);

            return CommentResource::collection($data);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->responseError(__('Failed get comments'), 500, $th->getMessage());
        }
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param  int  $articleId
     * @param  array  $data
     * @return BaseResponse::response
     */
    public function store($id, $data, CommentType $type = CommentType::ADD)
    {
        if ($type == CommentType::REPLY) {
            $parent = Comment::find($id);
            if (! $parent) {
                return $this->responseError('Parent comment not found.', 404);
            }
            $data['article_id'] = $parent->article_id;
            $data['parent_id'] = $parent->id;
        } else {
            $article = Article::find($id);
            if (! $article) {
                return $this->responseError('Article not found.', 404);
            }
            $data['article_id'] = $article->id;
        }

        try {
            $data['user_id'] = auth()->id();
            $comment = (new Comment)->createWithLog($data);

            $resource = new CommentResource($comment);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to create comment.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Comment has been created successfully.', 201, $resource);
    }

    /**
     * Get the specified comment.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function getById($id)
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return $this->responseError('Comment not found.', 404);
        }

        $resource = new CommentResource($comment);

        return $this->responseSuccess('Comment found.', 200, $resource);
    }

    /**
     * Update the specified comment in storage.
     *
     * @param  int  $id
     * @param  array  $data
     * @return BaseResponse::response
     */
    public function update($id, $data)
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return $this->responseError('Comment not found.', 404);
        }

        try {
            $comment = (new Comment)->updateWithLog($comment->id, $data);
            $resource = new DetailResource($comment);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to update comment.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Comment has been updated successfully.', 200, $resource);
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function delete($id)
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return $this->responseError('Comment not found.', 404);
        }

        try {
            $comment->delete();
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to delete comment.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Comment has been deleted successfully.');
    }

    /**
     * Toggle like the specified comment.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function toggleLike($id)
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return $this->responseError('Comment not found.', 404);
        }

        $toggleResult = ReactionService::toggleReaction($comment, ReactionType::LIKE, auth()->id());
        if (! $toggleResult['status']) {
            return $this->responseError($toggleResult['message'], $toggleResult['code'], $toggleResult['error']);
        }

        return $this->responseSuccess($toggleResult['message']);
    }

    /**
     * Toggle dislike the specified comment.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function toggleDislike($id)
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return $this->responseError('Comment not found.', 404);
        }

        $toggleResult = ReactionService::toggleReaction($comment, ReactionType::DISLIKE, auth()->id());
        if (! $toggleResult['status']) {
            return $this->responseError($toggleResult['message'], $toggleResult['code'], $toggleResult['error']);
        }

        return $this->responseSuccess($toggleResult['message']);
    }
}
