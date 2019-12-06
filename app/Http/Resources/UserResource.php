<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
      'name' => $this->name,
      'surname' => $this->surname,
      'email' => $this->email,
      'email_verified_at' => $this->email_verified_at,
      'created_at' => (string) $this->created_at,
      'updated_at' => (string) $this->updated_at,
      'logins' => new LoginCollectionResource($this->logins),
    ];
    // return parent::toArray($request);
  }
}
