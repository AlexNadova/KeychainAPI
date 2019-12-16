<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginCollectionResource;
use Illuminate\Http\Request;
use App\Helpers\HttpStatus;
use App\Login;
use App\Http\Resources\LoginResource;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    { }

    /**
     * Display a listing of the logins.
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
		$authenticatedUser = Auth::user();

		$logins = Login::where([
			['user_id', '=', $authenticatedUser->id]
		])->paginate(5);
		if ($logins->total() === 0){
			return response()->json(['error' => 'User does not own any logins.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		return response()->json($logins, HttpStatus::STATUS_OK);
    }

    /**
     * Store a newly created login to DB.
     * @param  \Illuminate\Http\Request $request (string: websiteName, websiteAddress, username, password)
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        /**
         * Validate incoming request.
         * If the validation fails, an exception will be thrown 
         * and a proper error response/ message will be sent back to the user.
         */
        $request->validate([
            'websiteName'    => ['required', 'string', 'max:30'],
            'websiteAddress' => ['required', 'string', 'max:255', 'url'],
            'username'       => ['required', 'string', 'max:255'],
            'password'       => ['required', 'string', 'max:255']
        ]);

        // Create a new login.
        $login = Login::create([
            'user_id' => $request->user()->id,
            'websiteName' => $request['websiteName'],
            'websiteAddress' => $request['websiteAddress'],
            'username' => $request['username'],
            'password' => $request['password'],
		]);
		if($login){
			return response()->json(['data' => new LoginResource($login)], HttpStatus::STATUS_CREATED);
		}
		return response()->json(['error' => 'Login could not be saved.'], HttpStatus::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified login.
     * @param   int $id
     * @return  \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
		$login = Login::where([
			['id', '=', $id]
		])->first();
		if (!$login){
            return response()->json(['error' => 'Resource does not exist.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		$authenticatedUser = Auth::user();
        // Check if currently authenticated user is the owner of the login
        if (!$authenticatedUser || $authenticatedUser['id'] !== $login['user_id']) {
            return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_FORBIDDEN);
        }
		return response()->json(['data' => new LoginResource($login)], HttpStatus::STATUS_OK);
		
    }

    /**
     * Update the specified login in storage.
     * @param   int $id
     * @param   Request $request (string: websiteName, websiteAddress, username, password)
     * @return  \Illuminate\Http\JsonResponse
     */
    public function update(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
		$login = Login::where([
			['id', '=', $id]
		])->first();
		if (!$login){
            return response()->json(['error' => 'Resource does not exist.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		$authenticatedUser = Auth::user();
        // Check if currently authenticated user is the owner of the login
        if (!$authenticatedUser || $authenticatedUser['id'] !== $login['user_id']) {
            return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_FORBIDDEN);
        }
        $request->validate([
            'websiteName'    => ['string', 'max:30'],
            'websiteAddress' => ['string', 'max:255', 'url'],
            'username'       => ['string', 'max:255'],
            'password'       => ['string', 'max:255']
        ]);
        // Update the login
		$login->update($request->only(['websiteName', 'websiteAddress', 'username', 'password']));
		return response()->json([
			'message' => 'Login was updated.',
			'data' => new LoginResource($login)
		], HttpStatus::STATUS_OK);
    }

    /**
     * Remove the specified account from storage.
     * @param	int $id
     * @return  \Illuminate\Http\JsonResponse
     * @throws  \Exception
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
		$login = Login::where([
			['id', '=', $id]
		])->first();
		if (!$login){
            return response()->json(['error' => 'Resource does not exist.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		$authenticatedUser = Auth::user();
        // Check if currently authenticated user is the owner of the login
        if (!$authenticatedUser || $authenticatedUser['id'] !== $login->user_id) {
            return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_FORBIDDEN);
        }else{
			// Delete the login
			$login->delete();
			return response()->json(['success' => 'Login was deleted successfully.'], HttpStatus::STATUS_OK);
		}
    }
}
