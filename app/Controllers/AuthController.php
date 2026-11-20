<?php
namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login', ['title' => 'Log in']);
    }

    public function login(Request $request): Response
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        if ($v->fails()) {
            return $this->backWithErrors($request, $v->errors(), '/login');
        }

        $user = User::findByEmail((string) $request->input('email'));

        if ($user === null || !password_verify((string) $request->input('password'), $user['password_hash'])) {
            return $this->backWithErrors($request, ['email' => 'Invalid email or password.'], '/login');
        }

        $intended = Session::get('intended');
        Auth::login($user);
        Session::forget('intended');
        $this->flash('success', 'Welcome back, ' . $user['name'] . '!');

        if ($user['role'] === 'admin' && !$intended) {
            return $this->redirect('/admin');
        }
        return $this->redirect($intended ?: '/');
    }

    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register', ['title' => 'Create account']);
    }

    public function register(Request $request): Response
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'password' => 'required|min:6|max:72|confirmed',
        ]);
        if ($v->fails()) {
            return $this->backWithErrors($request, $v->errors(), '/register');
        }

        $data = $v->validated();
        $id   = User::register($data['name'], $data['email'], $data['password']);

        Auth::login(User::find($id));
        $this->flash('success', 'Your account was created. Welcome to WhiskerHub!');
        return $this->redirect('/');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        Session::start();
        $this->flash('info', 'You have been logged out.');
        return $this->redirect('/');
    }
}
