<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\SighupRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthController extends Controller{
    use HttpResponse;
    public function redirectToAuth(): JsonResponse  {
        return response()->json([
            'url' => Socialite::driver('google')
                         ->stateless()
                         ->redirect()
                         ->getTargetUrl(),
        ]);
    }
    public function handleAuthCallback(): JsonResponse  {
        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')->stateless()->user();
        } catch (ClientException $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        /** @var User $user */
        $user = User::query()
        ->firstOrCreate(
                [
                    'email' => $socialiteUser->email,
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteUser->name,
                    'google_id' => $socialiteUser->id,
                    'avatar' => $socialiteUser->avatar,
                    'password' => $socialiteUser->password,
                ]
            );

        return response()->json([
            'users' => $user,
            'token' => $user->createToken('google-token'.$user->name)->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function sighup(SighupRequest $request) {

        // if ($request->file('profileImage')->isValid()) {
            // $file= $request->file('image');
            // $filename= date('YmdHi').$file->getClientOriginalName();
            // $file-> move(public_path('public/Image'), $filename);
        // }
        $filename = $request->profileImage;
        $request->validated($request->all());
        $folderPath = "public/profileImage/";
        $fileName =  uniqid().  '.png';
        $file = $folderPath . $fileName;
        Storage::put($file,$filename);
        
        $user =  User::create([
            'name' => $request->name,
            'email' => $request->email,
            "profileImage"=>$file,
            'price'=>$request->price,
            'password' => bcrypt($request->password)
        ]);
        $token = $user->createToken("API TOKEN".$user->name)->plainTextToken;
        return response()->json([
            "users" => $user,
            "token" =>$token
        ]);
    }
    public function login(LoginRequest $request){
        $request->validated($request->all());
        if(!Auth::attempt($request->only(['email','password']))){
            return $this->error('', "inviad users or worng password", 422);
        }
        $user = User::where("email", $request->email)->first();
        return $this->success([
            "users"=> $user,
            "token"=> $user->createToken("Api token for ".$user->name)->plainTextToken
        ]);
    }

    public function logout(Request $request)   {
        /** @var User $user */
        // $user ->$request->user();
        // $user->currentAccessToken()->delete();

        // return response(204); 
        $request->user()->tokens()->delete();
        return  response()->json([
            'status' => 200,
            'message' => 'u have logout '
        ]);
    }
}
