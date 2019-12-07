<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginCollectionResource;
use Illuminate\Http\Request;
use App\Login;
use App\Http\Resources\LoginResource;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * INDEX
     * Display a listing of the logins.
     *
     * @return  LoginCollectionResource  [return description]
     */
    public function index(): LoginCollectionResource
    {
        return new LoginCollectionResource(Login::paginate());
        //return LoginResource::collection(Login::paginate());
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
        /**
         * Validate incoming request.
         * If the validation fails, an exception will be thrown 
         * and a proper error response/ message will be sent back to the user.
         */
        $request->validate([
            'websiteName'    => ['unique:logins', 'required', 'string', 'max:255'],
            'websiteAddress' => ['required', 'string', 'max:255', 'url'],
            'userName'       => ['required', 'string', 'max:255'],
            'password'       => ['required', 'string', 'max:255']
        ]);

        // Create a new login.
        $login = Login::create([
            'user_id' => $request->user()->id,
            'websiteName' => $request['websiteName'],
            'websiteAddress' => $request['websiteAddress'],
            'userName' => $request['userName'],
            'password' => $request['password'],
        ]);

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
    public function update(Login $login, Request $request) //: LoginResource
    {
        // Check if currently authenticated user is the owner of the login
        if ($request->user()->id !== $login->user_id) {
            return response()->json(['error' => 'You can only edit your own login.'], 403);
        }

        /**
         * Validate incoming request.
         * If the validation fails, an exception will be thrown 
         * and a proper error response/ message will be sent back to the user.
         */
        $request->validate([
            'websiteName'    => ['unique:logins', 'string', 'max:255'],
            'websiteAddress' => ['string', 'max:255', 'url'],
            'userName'       => ['string', 'max:255'],
            'password'       => ['string', 'max:255']
        ]);

        // Update the login
        $login->update($request->only(['websiteName', 'websiteName', 'websiteAddress', 'userName', 'password']));

        // Return the updated login.
        return new LoginResource($login);
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

        return response()->json(null . 204);
    }
}