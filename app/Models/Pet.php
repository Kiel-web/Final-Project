<?php
namespace App\Models;

use Core\Model;

class Pet extends Model
{
    protected static string $table = 'pets';
    protected static array $fillable = [
        'name', 'breed_id', 'breed_name', 'age_months', 'gender', 'color',
        'description', 'adoption_fee', 'status', 'image_url',
    ];

    public static function search(?string $term, ?string $gender, ?string $status, int $page, int $perPage): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($term !== null && $term !== '') {
            $like     = '%' . addcslashes($term, '%_\\') . '%';
            $where[]  = '(name LIKE ? OR breed_name LIKE ? OR color LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (in_array($gender, ['male', 'female'], true)) {
            $where[]  = 'gender = ?';
            $params[] = $gender;
        }
        if (in_array($status, ['available', 'pending', 'adopted'], true)) {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        return static::paginate(
            $page,
            $perPage,
            implode(' AND ', $where),
            $params,
            "FIELD(status, 'available', 'pending', 'adopted'), id DESC"
        );
    }

    public static function latestAvailable(int $limit = 4): array
    {
        return static::db()->fetchAll(
            "SELECT * FROM pets WHERE status = 'available' ORDER BY id DESC LIMIT " . (int) $limit
        );
    }

    public static function countByStatus(): array
    {
        $rows = static::db()->fetchAll('SELECT status, COUNT(*) AS total FROM pets GROUP BY status');
        $counts = ['available' => 0, 'pending' => 0, 'adopted' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }
}
