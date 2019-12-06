<?php

namespace App\Http\Resources;

use App\Http\Resources\v2\LoginResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LoginCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            //'data' => LoginResource::collection($this->collection),
            'data' => $this->collection,
            'meta' => ['login_count' => $this->collection->count()]
        ];
    }
}
