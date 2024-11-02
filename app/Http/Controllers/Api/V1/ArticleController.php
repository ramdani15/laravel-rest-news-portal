<?php

namespace App\Http\Controllers\Api\V1;

use App\Cores\ApiResponse;
use App\Enums\ReactionType;
use App\Http\Requests\Api\V1\Article\StoreRequest;
use App\Http\Requests\Api\V1\Article\UpdateRequest;
use App\Http\Requests\Api\V1\ReactionRequest;
use Facades\App\Http\Services\Api\V1\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *       path="/api/v1/articles",
     *       summary="Get list articles ",
     *       description="Endpoint to get list articles ",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="user_id",
     *           in="query",
     *           description="User ID"
     *       ),
     *       @OA\Parameter(
     *           name="title",
     *           in="query",
     *           description="Title"
     *       ),
     *       @OA\Parameter(
     *           name="content",
     *           in="query",
     *           description="Content"
     *       ),
     *       @OA\Parameter(
     *           name="status",
     *           in="query",
     *           description="Status (draft, pending, approved, rejected, published)"
     *       ),
     *       @OA\Parameter(
     *           name="start_submitted",
     *           in="query",
     *           description="Start of submitted date"
     *       ),
     *       @OA\Parameter(
     *           name="end_submitted",
     *           in="query",
     *           description="End of submitted date"
     *       ),
     *       @OA\Parameter(
     *           name="start_approved",
     *           in="query",
     *           description="Start of approved date"
     *       ),
     *       @OA\Parameter(
     *           name="end_approved",
     *           in="query",
     *           description="End of approved date"
     *       ),
     *       @OA\Parameter(
     *           name="start_rejected",
     *           in="query",
     *           description="Start of rejected date"
     *       ),
     *       @OA\Parameter(
     *           name="end_rejected",
     *           in="query",
     *           description="End of rejected date"
     *       ),
     *       @OA\Parameter(
     *           name="start_published",
     *           in="query",
     *           description="Start of published date"
     *       ),
     *       @OA\Parameter(
     *           name="end_published",
     *           in="query",
     *           description="End of published date"
     *       ),
     *       @OA\Parameter(
     *           name="start_created",
     *           in="query",
     *           description="Start of created date"
     *       ),
     *       @OA\Parameter(
     *           name="end_created",
     *           in="query",
     *           description="End of created date"
     *       ),
     *       @OA\Parameter(
     *           name="sort",
     *           in="query",
     *           description="1 for Ascending -1 for Descending"
     *       ),
     *       @OA\Parameter(
     *           name="sort_by",
     *           in="query",
     *           description="Field to sort"
     *       ),
     *       @OA\Parameter(
     *           name="limit",
     *           in="query",
     *           description="Limit (Default 10)"
     *       ),
     *       @OA\Parameter(
     *           name="page",
     *           in="query",
     *           description="Num Of Page"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Get list articles successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="object", example={}),
     *              @OA\Property(property="pagination", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Get list articles failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Get list articles failed"),
     *          )
     *      ),
     * )
     */
    public function index(Request $request)
    {
        checkPerm('api-articles-index', true);

        $user = auth()->user();
        if (! $user->hasRole('admin')) {
            $request->merge([
                'user_id' => $user->id,
            ]);
        }

        if (! ($request->has('sort_by') && $request->has('sort'))) {
            $request->merge([
                'sort_by' => 'created_at',
                'sort' => -1,
            ]);
        }

        $data = ArticleService::list($request);
        if (isset($data['status']) && ! $data['status']) {
            return $this->responseJson('error', $data['message'], $data['data'], $data['statusCode']);
        }

        return $this->responseJson(
            'pagination',
            __('Get list articles successfully'),
            $data,
            $data['statusCode'],
            [$request->sort_by, $request->sort]
        );
    }

    /**
     * @OA\Post(
     *      path="/api/v1/articles",
     *      summary="Create a new article",
     *      description="Create a new article",
     *      tags={"Articles"},
     *      security={
     *          {"token": {}}
     *      },
     *
     *      @OA\RequestBody(
     *
     *          required=true,
     *          description="Data that needed to create a new article",
     *
     *          @OA\JsonContent(
     *              required={"title", "content"},
     *
     *              @OA\Property(property="title", type="string", example="Title"),
     *              @OA\Property(property="content", type="string", example="Content"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Create a new article successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Create a new article successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Wrong credentials response",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Create a new articles failed",
     *      ),
     * )
     */
    public function store(StoreRequest $request)
    {
        checkPerm('api-articles-store', true);

        $data = $request->validated();
        $data = ArticleService::store($data);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Get(
     *       path="/api/v1/articles/{id}",
     *       summary="Get detail article",
     *       description="Endpoint to get detail article",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID",
     *           required=true,
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Get article successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Get article successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Article not found",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Article not found"),
     *          )
     *      ),
     * )
     */
    public function show($id)
    {
        checkPerm('api-articles-show', true);

        $data = ArticleService::getById($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Put(
     *       path="/api/v1/articles/{id}",
     *       summary="Update articles",
     *       description="Endpoint to update articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *      @OA\RequestBody(
     *
     *          required=true,
     *          description="Data that needed to update a article",
     *
     *          @OA\JsonContent(
     *              required={"title", "content"},
     *
     *              @OA\Property(property="title", type="string", example="Title"),
     *              @OA\Property(property="content", type="string", example="Content"),
     *          ),
     *      ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Update articles successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Update article successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Update articles failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Update articles failed"),
     *          )
     *      ),
     * )
     */
    public function update($id, UpdateRequest $request)
    {
        checkPerm('api-articles-update', true);

        $data = $request->validated();
        $data = ArticleService::update($id, $data);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Delete(
     *       path="/api/v1/articles/{id}",
     *       summary="Delete articles",
     *       description="Endpoint to delete articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Delete articles successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Delete articles successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Article not found",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Article not found"),
     *          )
     *      ),
     * )
     */
    public function destroy($id)
    {
        checkPerm('api-articles-destroy', true);

        $data = ArticleService::delete($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/request-approval",
     *       summary="Request approval articles",
     *       description="Endpoint to request approval articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Request approval successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Request approval successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Request approval failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Request approval failed"),
     *          )
     *      ),
     * )
     */
    public function requestApproval($id)
    {
        checkPerm('api-articles-request-approval', true);

        $data = ArticleService::requestApproval($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/approve",
     *       summary="Approve articles",
     *       description="Endpoint to request approve articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Approve successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Approve successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Approve failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Approve failed"),
     *          )
     *      ),
     * )
     */
    public function approve($id)
    {
        checkPerm('api-articles-approve', true);

        $data = ArticleService::approve($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/reject",
     *       summary="Reject articles",
     *       description="Endpoint to request reject articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Reject successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Reject successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Reject failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Reject failed"),
     *          )
     *      ),
     * )
     */
    public function reject($id)
    {
        checkPerm('api-articles-reject', true);

        $data = ArticleService::reject($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/publish",
     *       summary="Publish articles",
     *       description="Endpoint to request publish articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Publish successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Publish successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Publish failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Publish failed"),
     *          )
     *      ),
     * )
     */
    public function publish($id)
    {
        checkPerm('api-articles-publish', true);

        $data = ArticleService::publish($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/unpublish",
     *       summary="Unpublish articles",
     *       description="Endpoint to request unpublish articles",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Unpublish successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Unpublish successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Unpublish failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unpublish failed"),
     *          )
     *      ),
     * )
     */
    public function unpublish($id)
    {
        checkPerm('api-articles-unpublish', true);

        $data = ArticleService::unpublish($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/articles/{id}/toggle-reaction",
     *       summary="Add reaction to article",
     *       description="Endpoint to add reaction to article",
     *       tags={"Articles"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="ID"
     *       ),
     *
     *      @OA\RequestBody(
     *
     *          required=true,
     *          description="Data that needed to add reaction",
     *
     *          @OA\JsonContent(
     *              required={"type"},
     *
     *              @OA\Property(property="type", type="string", example="like"),
     *          ),
     *      ),
     *
     *       @OA\Response(
     *          response=200,
     *          description="Add reaction successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Add reaction successfully"),
     *              @OA\Property(property="data", type="object", example={}),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Add reaction failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Add reaction failed"),
     *          )
     *      ),
     * )
     */
    public function toggleReaction($id, ReactionRequest $request)
    {
        checkPerm('api-articles-toggle-reaction', true);

        $data = $request->validated();
        if ($data['type'] === ReactionType::LIKE->value) {
            $data = ArticleService::toggleLike($id);
        } else {
            $data = ArticleService::toggleDislike($id);
        }

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }
}
