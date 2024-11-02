<?php

namespace App\Http\Controllers\Api\V1;

use App\Cores\ApiResponse;
use App\Enums\ArticleStatus;
use Facades\App\Http\Services\Api\V1\ArticleService;
use Facades\App\Http\Services\Api\V1\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *       path="/api/v1/dashboard",
     *       summary="Get list articles for dashboard",
     *       description="Endpoint to get list articles for dashboard",
     *       tags={"Dashboard"},
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
     *          description="Get list articles for dashboard successfully",
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
     *          description="Get list articles for dashboard failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Get list articles for dashboard failed"),
     *          )
     *      ),
     * )
     */
    public function index(Request $request)
    {
        $request->merge([
            'status' => ArticleStatus::PUBLISHED->value,
        ]);

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
            __('Get list articles for dashboard successfully'),
            $data,
            $data['statusCode'],
            [$request->sort_by, $request->sort]
        );
    }

    /**
     * @OA\Get(
     *       path="/api/v1/dashboard/{id}",
     *       summary="Get detail article for dashboard",
     *       description="Endpoint to get detail article for dashboard",
     *       tags={"Dashboard"},
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
     *          description="Get article for dashboard successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Get article for dashboard successfully"),
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
        $data = ArticleService::getPublishedById($id);

        return $this->responseJson(
            $data['status'] ? 'success' : 'error',
            $data['message'],
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @OA\Get(
     *       path="/api/v1/dashboard/{id}/comments",
     *       summary="Get article's comments for dashboard",
     *       description="Endpoint to get article's comments for dashboard",
     *       tags={"Dashboard"},
     *
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           description="Article ID",
     *           required=true,
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
     *          description="Get list article's comments successfully",
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
     *          description="Get list article's comments failed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Get list article's comments failed"),
     *          )
     *      ),
     * )
     */
    public function comments($id, Request $request)
    {
        $request->merge([
            'article_id' => $id,
            'parent_id' => null,
        ]);

        Log::info('DashboardController@comments', $request->all());

        $data = CommentService::list($request);
        if (isset($data['status']) && ! $data['status']) {
            return $this->responseJson('error', $data['message'], $data['data'], $data['statusCode']);
        }

        return $this->responseJson(
            'pagination',
            __('Get list article\'s comments successfully'),
            $data,
            $data['statusCode'],
            [$request->sort_by, $request->sort]
        );
    }
}
