<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'total_likes' => $this->total_likes,
            'total_dislikes' => $this->total_dislikes,
            'total_replies' => $this->total_replies,
            'is_liked' => $this->is_liked,
            'is_disliked' => $this->is_disliked,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'author' => new AuthorResource($this->author),
            'likes' => ReactionResource::collection($this->likes),
            'dislikes' => ReactionResource::collection($this->dislikes),
            'replies' => CommentResource::collection($this->replies),
        ];
    }
}
