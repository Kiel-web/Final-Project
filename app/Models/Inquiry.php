<?php
namespace App\Models;

use Core\Model;

class Inquiry extends Model
{
    protected static string $table = 'inquiries';
    protected static array $fillable = ['pet_id', 'user_id', 'name', 'email', 'phone', 'message', 'status'];

    public static function adminList(?string $status = null): array
    {
        $sql = 'SELECT i.*, p.name AS pet_name, p.breed_name
                FROM inquiries i JOIN pets p ON p.id = i.pet_id';
        $params = [];
        if (in_array($status, ['new', 'contacted', 'closed'], true)) {
            $sql .= ' WHERE i.status = ?';
            $params[] = $status;
        }
        return static::db()->fetchAll($sql . ' ORDER BY i.id DESC', $params);
    }
}
