<?php

namespace App\Http\Services\Api\V1;

use App\Enums\ArticleStatus;
use App\Enums\ReactionType;
use App\Http\Filters\Api\V1\ByContent;
use App\Http\Filters\Api\V1\ByRangeApprovedAt;
use App\Http\Filters\Api\V1\ByRangeCreatedAt;
use App\Http\Filters\Api\V1\ByRangePublishedAt;
use App\Http\Filters\Api\V1\ByRangeRejectedAt;
use App\Http\Filters\Api\V1\ByRangeSubmittedAt;
use App\Http\Filters\Api\V1\ByStatus;
use App\Http\Filters\Api\V1\ByTitle;
use App\Http\Filters\Api\V1\ByUserId;
use App\Http\Filters\Api\V1\OrderBy;
use App\Http\Resources\Api\V1\Article\DetailResource;
use App\Http\Resources\Api\V1\Article\ListResource;
use App\Models\Article;
use Facades\App\Http\Services\Api\V1\ReactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticleService extends BaseResponse
{
    /**
     * Get list of articles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $query = Article::query();
            $piplines = [
                ByUserId::class,
                ByTitle::class,
                ByContent::class,
                ByStatus::class,
                ByRangeSubmittedAt::class,
                ByRangeApprovedAt::class,
                ByRangeRejectedAt::class,
                ByRangePublishedAt::class,
                ByRangeCreatedAt::class,
                OrderBy::class,
            ];

            $data = $this->filterPagination($query, $piplines, $request);

            return ListResource::collection($data);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->responseError(__('Failed get articles'), 500, $th->getMessage());
        }
    }

    /**
     * Store a newly created article in storage.
     *
     * @param  array  $data
     * @return BaseResponse::response
     */
    public function store($data)
    {
        try {
            $data['user_id'] = auth()->id();
            $data['status'] = ArticleStatus::DRAFT;
            $article = (new Article)->createWithLog($data);

            $resource = new DetailResource($article);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to create article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been created successfully.', 201, $resource);
    }

    /**
     * Get the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function getById($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if (! $user->hasRole('admin') && $article->user_id != $user->id) {
            return $this->responseError('You are not authorized to get this article.', 403);
        }

        $resource = new DetailResource($article);

        return $this->responseSuccess('Article found.', 200, $resource);
    }

    /**
     * Get the specified article that has been published.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function getPublishedById($id)
    {
        $article = Article::where('status', ArticleStatus::PUBLISHED)->find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $resource = new DetailResource($article);

        return $this->responseSuccess('Article found.', 200, $resource);
    }

    /**
     * Update the specified article in storage.
     *
     * @param  int  $id
     * @param  array  $data
     * @return BaseResponse::response
     */
    public function update($id, $data)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if (! $user->hasRole('admin') && $article->user_id != $user->id) {
            return $this->responseError('You are not authorized to update this article.', 403);
        }

        try {
            $article = (new Article)->updateWithLog($article->id, $data);
            $resource = new DetailResource($article);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to update article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been updated successfully.', 200, $resource);
    }

    /**
     * Remove the specified article from storage.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function delete($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if (! $user->hasRole('admin') && $article->user_id != $user->id) {
            return $this->responseError('You are not authorized to delete this article.', 403);
        }

        try {
            $article->delete();
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to delete article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been deleted successfully.');
    }

    /**
     * Request approval for the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function requestApproval($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if ($article->user_id != $user->id) {
            return $this->responseError('You are not authorized to request approval for this article.', 403);
        }

        if ($article->status != ArticleStatus::DRAFT) {
            return $this->responseError('Article status is not draft.', 400);
        }

        try {
            $data = [
                'status' => ArticleStatus::PENDING,
                'submitted_at' => now(),
            ];
            (new Article)->updateWithLog($article->id, $data);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to request approval.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been requested for approval.');
    }

    /**
     * Approve the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function approve($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        if ($article->status != ArticleStatus::PENDING) {
            return $this->responseError('Article status is not pending.', 400);
        }

        try {
            $data = [
                'status' => ArticleStatus::APPROVED,
                'approved_at' => now(),
            ];
            (new Article)->updateWithLog($article->id, $data);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to approve article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been approved.');
    }

    /**
     * Reject the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function reject($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        if ($article->status != ArticleStatus::PENDING) {
            return $this->responseError('Article status is not pending.', 400);
        }

        try {
            $data = [
                'status' => ArticleStatus::REJECTED,
                'rejected_at' => now(),
            ];
            (new Article)->updateWithLog($article->id, $data);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to reject article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been rejected.');
    }

    /**
     * Publish the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function publish($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if (! $user->hasRole('admin') && $article->user_id != $user->id) {
            return $this->responseError('You are not authorized to publish this article.', 403);
        }

        if ($article->status != ArticleStatus::APPROVED) {
            return $this->responseError('Article status is not approved.', 400);
        }

        try {
            $data = [
                'status' => ArticleStatus::PUBLISHED,
                'published_at' => now(),
            ];
            (new Article)->updateWithLog($article->id, $data);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to publish article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been published.');
    }

    /**
     * Unpublish the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function unpublish($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $user = auth()->user();
        if (! $user->hasRole('admin') && $article->user_id != $user->id) {
            return $this->responseError('You are not authorized to unpublish this article.', 403);
        }

        if ($article->status != ArticleStatus::PUBLISHED) {
            return $this->responseError('Article status is not published.', 400);
        }

        try {
            $data = [
                'status' => ArticleStatus::APPROVED,
                'published_at' => null,
            ];
            (new Article)->updateWithLog($article->id, $data);
        } catch (\Exception $th) {
            Log::error($th);

            return $this->responseError('Failed to unpublish article.', 500, $th->getMessage());
        }

        return $this->responseSuccess('Article has been unpublished.');
    }

    /**
     * Toggle like the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function toggleLike($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $toggleResult = ReactionService::toggleReaction($article, ReactionType::LIKE, auth()->id());
        if (! $toggleResult['status']) {
            return $this->responseError($toggleResult['message'], $toggleResult['statusCode'], $toggleResult['data']);
        }

        return $this->responseSuccess($toggleResult['message']);
    }

    /**
     * Toggle dislike the specified article.
     *
     * @param  int  $id
     * @return BaseResponse::response
     */
    public function toggleDislike($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return $this->responseError('Article not found.', 404);
        }

        $toggleResult = ReactionService::toggleReaction($article, ReactionType::DISLIKE, auth()->id());
        if (! $toggleResult['status']) {
            return $this->responseError($toggleResult['message'], $toggleResult['code'], $toggleResult['error']);
        }

        return $this->responseSuccess($toggleResult['message']);
    }
}
