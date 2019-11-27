<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginCollectionResource;
use Illuminate\Http\Request;
use App\Login;
use App\Http\Resources\LoginResource;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * INDEX
     * Display a listing of the logins.
     *
     * @return  LoginCollectionResource  [return description]
     */
    public function index(): LoginCollectionResource
    {
        return new LoginCollectionResource(Login::paginate());
    }

    /**
     * STORE
     * Store a newly created login in storage.
     *
     * @param  \Illuminate\Http\Request $request  [$request description]
     * @return  App\Http\Resources\LoginResource $login
     */
    public function store(Request $request)
    {
        // Validation: Required fields.
        $request->validate([
            'websiteName'    => ['required', 'string', 'max:255'],
            'websiteAddress' => ['required', 'string', 'max:255'],
            'userName'       => ['required', 'string', 'max:255'],
            'password'       => ['required', 'string', 'max:255']
        ]);

        // Hash or encrypt password!

        // Create a new login.
        $login = Login::create($request->all());
        /* $login = Login::create([
            'websiteName' => $request['websiteName'],
            'websiteAddress' => $request['websiteAddress'],
            'userName' => $request['userName'],
            'password' => HASH::make($request['password'])
        ]); */

        return new LoginResource($login);
    }

    /**
     * SHOW
     * Display the specified login.
     *
     * @param   Login $login  [$login description]
     * @return  LoginResource [return description]
     */
    public function show(Login $login): LoginResource
    {
        // Validation, if there is no login for given id

        return new LoginResource($login);
    }

    /**
     * UPDATE
     * Update the specified login in storage.
     *
     * @param   Login   $login    [$login description]
     * @param   Request $request  [$request description]
     * @return  LoginResource     [return description]
     */
    public function update(Login $login, Request $request): LoginResource
    {
        // Validation her. (Assuming that the user is passing something as a string)

        // Update the login
        $login->update($request->all());

        // Return the updated login.
        return new LoginResource($login);

        /* return (new LoginResource($login))
        ->response()
        ->setStatusCode(202); */
    }

    /**
     * DESTROY
     * Remove the specified account from storage.
     *
     * @param   Login  $login  [$login description]
     * @return  \Illuminate\Http\JsonResponse    [return description]
     * @throws \Exception
     */
    public function destroy(Login $login)
    {
        // Validation

        // Delete the login
        $login->delete();

        // Return an empty array
        return response()->json();
    }
}
