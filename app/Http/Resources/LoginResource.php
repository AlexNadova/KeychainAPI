<?php

namespace App\Http\Resources;

use App\Http\Resources\v2\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            "id" => $this->id,
            "websiteName" => $this->websiteName,
            "websiteAddress" => $this->websiteAddress,
            'userName' => $this->userName,
            'password' => $this->password,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,

            'user' => $this->user,
        ];
    }
}
