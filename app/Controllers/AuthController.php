<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->view('auth/login', ['title' => '登录']);
    }

    public function login(): void
    {
        Csrf::verify();
        $userModel = new User();
        $user = $userModel->findByUsername(trim($_POST['username'] ?? ''));

        if (!$user || $user['status'] !== 'active' || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            set_flash('error', '用户名或密码不正确。');
            $this->redirect('/login');
        }

        Auth::login($user);
        $userModel->touchLastLogin((int) $user['id']);
        $this->redirect('/');
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();
        header('Location: /login');
        exit;
    }
}

