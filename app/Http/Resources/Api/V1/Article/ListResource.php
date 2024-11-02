<?php

namespace App\Http\Resources\Api\V1\Article;

use App\Http\Resources\Api\V1\AuthorResource;
use App\Http\Resources\Api\V1\ReactionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at ? $this->submitted_at->format('Y-m-d H:i:s') : null,
            'approved_at' => $this->approved_at ? $this->approved_at->format('Y-m-d H:i:s') : null,
            'rejected_at' => $this->rejected_at ? $this->rejected_at->format('Y-m-d H:i:s') : null,
            'published_at' => $this->published_at ? $this->published_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'total_likes' => $this->total_likes,
            'total_dislikes' => $this->total_dislikes,
            'total_comments' => $this->total_comments,
            'is_liked' => $this->is_liked,
            'is_disliked' => $this->is_disliked,
            'author' => new AuthorResource($this->author),
            'likes' => ReactionResource::collection($this->likes),
            'dislikes' => ReactionResource::collection($this->dislikes),
        ];
    }
}
