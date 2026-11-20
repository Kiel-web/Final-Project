<?php
namespace Core;

class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('auth_user', [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function user(): ?array
    {
        return Session::get('auth_user');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'admin';
    }
}
