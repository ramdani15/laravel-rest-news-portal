<?php

namespace App\Http\Controllers\Api\V1;

use App\Cores\ApiResponse;
use App\Enums\CommentType;
use App\Enums\ReactionType;
use App\Http\Requests\Api\V1\Comment\ReplyRequest;
use App\Http\Requests\Api\V1\Comment\StoreRequest;
use App\Http\Requests\Api\V1\ReactionRequest;
use Facades\App\Http\Services\Api\V1\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *       path="/api/v1/comments",
     *       summary="Get list comments ",
     *       description="Endpoint to get list comments ",
     *       tags={"Comment"},
     *       security={
     *           {"token": {}}
     *       },
     *
     *       @OA\Parameter(
     *           name="article_id",
     *           in="query",
     *           description="Article ID"
     *       ),
     *       @OA\Parameter(
     *           name="content",
     *           in="query",
     *           description="Content"
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
     *          description="Get list comments successfully",
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
     *          description="Get list comments failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Get list comments failed"),
     *          )
     *      ),
     * )
     */
    public function index(Request $request)
    {
        checkPerm('api-comments-index', true);

        $data = CommentService::list($request);
        if (isset($data['status']) && ! $data['status']) {
            return $this->responseJson('error', $data['message'], $data['data'], $data['statusCode']);
        }

        return $this->responseJson(
            'pagination',
            __('Get list comments successfully'),
            $data,
            $data['statusCode'],
            [$request->sort_by, $request->sort]
        );
    }

    /**
     * @OA\Post(
     *      path="/api/v1/comments",
     *      summary="Create a new comment",
     *      description="Create a new comment",
     *      tags={"Comments"},
     *      security={
     *          {"token": {}}
     *      },
     *
     *      @OA\RequestBody(
     *
     *          required=true,
     *          description="Data that needed to create a new comment",
     *
     *          @OA\JsonContent(
     *              required={"article_id", "content"},
     *
     *              @OA\Property(property="article_id", type="number", example=1),
     *              @OA\Property(property="content", type="string", example="comment"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Create a new comment successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Create a new comment successfully"),
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
     *          description="Create a new comment failed",
     *      ),
     * )
     */
    public function store(StoreRequest $request)
    {
        checkPerm('api-comments-store', true);

        $data = $request->validated();
        $data = CommentService::store($data['article_id'], $data, CommentType::ADD);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *      path="/api/v1/comments/{id}/reply",
     *      summary="Reply to comment",
     *      description="Reply to comment",
     *      tags={"Comments"},
     *      security={
     *          {"token": {}}
     *      },
     *
     *      @OA\RequestBody(
     *
     *          required=true,
     *          description="Data that needed to create a new reply",
     *
     *          @OA\JsonContent(
     *              required={"parent_id", "content"},
     *
     *              @OA\Property(property="parent_id", type="number", example=1),
     *              @OA\Property(property="content", type="string", example="comment"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Create a new reply successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Create a new reply successfully"),
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
     *          description="Create a new reply failed",
     *      ),
     * )
     */
    public function reply(ReplyRequest $request)
    {
        checkPerm('api-comments-store', true);

        $data = $request->validated();
        $data = CommentService::store($data['parent_id'], $data, CommentType::REPLY);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Post(
     *       path="/api/v1/comments/{id}/toggle-reaction",
     *       summary="Add reaction to comment",
     *       description="Endpoint to add reaction to comment",
     *       tags={"Comments"},
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
        checkPerm('api-comments-toggle-reaction', true);

        $data = $request->validated();
        if ($data['type'] === ReactionType::LIKE->value) {
            $data = CommentService::toggleLike($id);
        } else {
            $data = CommentService::toggleDislike($id);
        }

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }
}
