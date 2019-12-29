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
     * @param  \Illuminate\Http\Request $request (string: website_name, website_address, username, password)
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
            'website_name'    => ['required', 'string', 'max:30'],
            'website_address' => ['required', 'string', 'max:255', 'url'],
            'username'       => ['required', 'string', 'max:45'],
            'password'       => ['required', 'string', 'max:45']
        ]);
		$authenticatedUser = Auth::user();
		$domain = $this->parse_url_all($request['website_address']);
        // Create a new login.
        $login = Login::create([
            'user_id' => $authenticatedUser['id'],
            'website_name' => $request['website_name'],
			'website_address' => $request['website_address'],
			'domain' => $domain['domain'],
            'username' => $request['username'],
            'password' => $request['password'],
		]);
		if($login){
			return response()->json(['success' => 'Login was created.'], HttpStatus::STATUS_CREATED);
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
     * @param   Request $request (string: website_name, website_address, username, password)
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
            'website_name'    => ['string', 'max:30'],
            'website_address' => ['string', 'max:255', 'url'],
            'username'       => ['string', 'max:45'],
            'password'       => ['string', 'max:45']
		]);
		if(isset($request['website_address'])){
			$domain = $this->parse_url_all($request['website_address']);
			$login->domain = $domain['domain'];
			$login->save();
		}
        // Update the login
		$login->update($request->only(['website_name', 'website_address', 'username', 'password']));
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
	
	/**
	 * https://stackoverflow.com/a/45044051
	 */
	function parse_url_all($url){
		$url = substr($url,0,4)=='http'? $url: 'http://'.$url;
		$d = parse_url($url);
		$tmp = explode('.',$d['host']);
		$n = count($tmp);
		if ($n>=2){
			if ($n==4 || ($n==3 && strlen($tmp[($n-2)])<=3)){
				$d['domain'] = $tmp[($n-3)].".".$tmp[($n-2)].".".$tmp[($n-1)];
				$d['domainX'] = $tmp[($n-3)];
			} else {
				$d['domain'] = $tmp[($n-2)].".".$tmp[($n-1)];
				$d['domainX'] = $tmp[($n-2)];
			}
		}
		return $d;
	}
}
