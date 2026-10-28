<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'posted_by' => $this->user->name,
            'posted_at' => date('F j, Y', strtotime($this->created_at)),
            'comment' => $this->comment,
            'rating' => $this->rating,
            'user_photo' => $this->user->avatar ?? asset('images/unknown.png')
        ];
    }
}
