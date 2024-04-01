<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "comments" => $this->comments,
            "recommend_clinic" => $this->recommend_clinic,
            "rating" => $this->rating,
            "description" => $this->description,
            "created_at" => $this->created_at,
            "clinic" => $this->clinic,
            "booking" => $this->booking,
            "user" => $this->user,
        ];
    }
}
