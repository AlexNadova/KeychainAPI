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
            // Don't display id and user_id in deployment.
            "id" => $this->id,
            "user_id" => $this->user_id,
            "websiteName" => $this->websiteName,
            "websiteAddress" => $this->websiteAddress,
            'username' => $this->username,
            'password' => $this->password,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,

            // Display the user/ owner for every login. 
            //'user' => $this->user,
        ];
    }
}
