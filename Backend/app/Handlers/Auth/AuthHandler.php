<?php

declare(strict_types=1);

final class AuthHandler extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('');
        }

        header('Location: http://localhost:3000/login');
        exit;
    }

    public function login(): void
    {
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $username = trim((string) Request::post('username', ''));
        $password = (string) Request::post('password', '');

        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Username wajib diisi';
        }
        if ($password === '') {
            $errors['password'] = 'Password wajib diisi';
        }

        if ($errors !== []) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = ['username' => $username];
            $this->redirect('auth/login');
        }

        if (!Auth::login($username, $password)) {
            Helper::flashSet('error', 'Username atau password salah');
            $_SESSION['form_old'] = ['username' => $username];
            $this->redirect('auth/login');
        }

        $this->redirect('');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('auth/login');
    }
}