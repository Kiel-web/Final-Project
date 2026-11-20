<?php
namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['name', 'email', 'password_hash', 'role'];

    public static function findByEmail(string $email): ?array
    {
        return static::findBy('email', strtolower($email));
    }

    public static function register(string $name, string $email, string $password): int
    {
        return static::create([
            'name'          => $name,
            'email'         => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'customer',
        ]);
    }
}
