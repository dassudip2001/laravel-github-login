<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthOController extends Controller
{
    public function redirect($provider){
        return Socialite::driver($provider)->redirect();
    }
    public function callback($provider){
//        $socalUser = Socialite::driver($provider)->user();
        // dd($socalUser);

        try {
            $socalUser = Socialite::driver($provider)->user();
            if(User::where('email',$socalUser->getEmail())->exists()){
                return redirect('/dashboard')->withErrors(['email'=>'User already exists']);
            }
            // dd($socalUser);
            $user=User::where([
                'provider_id' => $socalUser->id,
                'provider'=>$provider,
            ])->first();
            // dd($user);
            if(!$user){
                $user=new User();
                $user->name=$socalUser->getName();
                $user->email=$socalUser->getEmail();
                $user->username=User::checkUserName($socalUser->getNickname());
                $user->provider=$provider;
                $user->provider_id=$socalUser->getId();
                $user->provider_token=$socalUser->token;
                $user->email_verified_at=now();
                try {
                    // dd($user);
                    //code...
                    $user->save();
                } catch (\Throwable $th) {
                    throw $th;
                }
                // dd($user);
                // $user= User::updateOrCreate([
                //     'name' => $socalUser->getName(),
                //     'email' => $socalUser->getEmail(),
                //     'username'=>User::checkUserName($socalUser->getNickname()),
                //     'provider'=>$provider,
                //     'provider_id' => $socalUser->getId(),
                //     'provider_token'=>$socalUser->token,
                //     'email_verified_at'=>now()
                // ]);
            }
        //    dd($user);
//            $dd($user);
            Auth::login($user);

            return redirect('/dashboard');

        } catch (\Throwable $th) {
            //throw $th;
            return redirect('/login');

        }
    }
}