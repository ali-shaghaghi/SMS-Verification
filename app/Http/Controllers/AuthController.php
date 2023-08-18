<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register()
    {
        return view('auth.register');
    }
    public function doRegister(Request $request)
    {
        $data = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone' => 'nullable|numeric',
        ]);
        $data['password'] = bcrypt($request->password);
        $user = User::create($data);
        auth()->login($user);
        return redirect()->route('index');
    }




    public function loginPhone()
    {
        return view('auth.login-phone');
    }
    public function doLoginPhone(Request $request)
    {
        $data = $request->all();
        $this->validate($request, [
            'phone' => 'required|exists:users',
        ]);
        $user = User::where('phone', $request->input('phone'))->first();
        $token = Token::create([
            'user_id' => $user->id
        ]);
        $rememberMe  = ( !empty( $request->remember_me ) )? TRUE : FALSE;
        if ($token->sendCode()) {
            session()->put("code_id", $token->id);
            session()->put("user_id", $user->id);
            session()->put("remember", $rememberMe);
            return redirect()->route('verify');
        }
        $token->delete();
        return redirect()->route('login')->withErrors([
            "Unable to send verification code"
        ]);
    }




    public function loginEmail()
    {
        return view('auth.login-email');
    }
    public function doLoginEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $data = $request->only('email', 'password');
        $rememberMe = $request->input('remember_me');
        if (auth()->attempt($data, $rememberMe))
            return redirect()->route('index');
        else
            return redirect()->back()->withErrors('Either email or password is wrong.');
    }



    public function verify() {
        return view('auth.verify');
    }
    public function doVerify(Request $request) {
        $this->validate($request, [
            'code' => 'required|numeric'
        ]);
        if (!session()->has('code_id') || !session()->has('user_id'))
            redirect()->route('loginPhone');
        $token = Token::where('user_id', session()->get('user_id'))->find(session()->get('code_id'));
        if (!$token || empty($token->id))
            redirect()->route('loginPhone');
        if (!$token->isValid())
            redirect()->back()->withErrors('The code is either expired or used.');
        if ($token->code !== $request->input('code'))
            redirect()->back()->withErrors('The code is wrong.');
        $token->update([
            'used' => true
        ]);
        $user = User::find(session()->get('user_id'));
        $rememberMe = session()->get('remember');
        auth()->login($user, $rememberMe);
        return redirect()->route('index');
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->back();
    }

}
