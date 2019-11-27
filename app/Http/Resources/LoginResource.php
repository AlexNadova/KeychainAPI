<?php

namespace App\Http\Resources;

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
        return parent::toArray($request);

        /* return [
            "id" => $this->id,
            "websiteName" => $this->websiteName,
            "websiteAddress" => $this->websiteAddress,
            'userName' => $this->userName
        ]; */
    }
}
