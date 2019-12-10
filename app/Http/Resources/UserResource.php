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
      'email_verified_at' => (string) $this->email_verified_at,
      'created_at' => (string) $this->created_at,
      'updated_at' => (string) $this->updated_at,
      // Will not decrypt the users logins, but will display the owner only once for all logins.
      //'logins' => new LoginCollectionResource($this->logins),
      // Will decrypt the users logins, but will also display the owner in every login.
    //   'logins' => LoginResource::collection($this->logins),
    ];
    // return parent::toArray($request);
  }
}
