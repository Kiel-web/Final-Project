<?php
$mode = is_file(__DIR__ . '/mode.txt') ? trim(file_get_contents(__DIR__ . '/mode.txt')) : 'ok';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$breeds = [
    ['abys', 'Abyssinian', 'Egypt', 'Active, Energetic, Independent, Intelligent, Gentle', 'The Abyssinian is easy to care for, and a joy to have in your home.', '14 - 15', '3 - 5', 5, 5, 5, 5, 4, '#c9702e'],
    ['beng', 'Bengal', 'United States', 'Alert, Agile, Energetic, Demanding, Intelligent', 'Bengals are a lot of fun to live with, but they are not the cat for everyone.', '12 - 15', '3 - 7', 5, 5, 5, 4, 5, '#d9a441'],
    ['birm', 'Birman', 'France', 'Affectionate, Active, Gentle, Social', 'The Birman is a docile, quiet cat who loves people and will follow them from room to room.', '14 - 15', '3 - 7', 5, 3, 3, 5, 5, '#b89b82'],
    ['bsho', 'British Shorthair', 'United Kingdom', 'Affectionate, Easy Going, Gentle, Loyal, Patient, Calm', 'The British Shorthair is a very pleasant cat to have as a companion.', '12 - 17', '4 - 8', 4, 2, 3, 4, 3, '#8a9bb0'],
    ['mcoo', 'Maine Coon', 'United States', 'Adaptable, Intelligent, Loving, Gentle, Social', 'The Maine Coon is the most massive breed of domestic cat.', '12 - 15', '4 - 8', 5, 3, 5, 5, 5, '#8b5e3c'],
    ['pers', 'Persian', 'Iran (Persia)', 'Affectionate, Docile, Quiet, Gentle, Loyal', 'Persians are sweet, gentle cats that need daily grooming.', '14 - 15', '3 - 5', 5, 1, 3, 4, 2, '#e8e0d5'],
    ['ragd', 'Ragdoll', 'United States', 'Affectionate, Friendly, Gentle, Quiet, Easygoing', 'Ragdolls love their people, greeting them at the door and following them around.', '12 - 17', '5 - 10', 5, 1, 3, 5, 4, '#9fb3cc'],
    ['rblu', 'Russian Blue', 'Russia', 'Active, Dependent, Easy Going, Gentle, Intelligent, Loyal', 'Russian Blues are very loving and reserved, they form strong bonds with their owner.', '15 - 20', '3 - 5', 3, 3, 3, 3, 3, '#7d8a96'],
    ['sfol', 'Scottish Fold', 'United Kingdom', 'Affectionate, Intelligent, Loyal, Playful, Social, Sweet', 'The Scottish Fold is a sweet, even-tempered cat with the signature folded ears.', '11 - 14', '2 - 6', 4, 2, 3, 5, 5, '#e6cfa8'],
    ['siam', 'Siamese', 'Thailand', 'Active, Agile, Clever, Sociable, Loving, Energetic', 'While Siamese cats are extremely fond of their people, they will follow you around and supervise your activities.', '12 - 15', '3 - 5', 5, 3, 5, 5, 4, '#cdb79e'],
    ['sibe', 'Siberian', 'Russia', 'Curious, Intelligent, Loyal, Sweet, Agile, Playful, Affectionate', 'The Siberians dog like personality and affection makes them a great companion.', '12 - 15', '3 - 7', 5, 3, 4, 5, 5, '#c98b4b'],
    ['sphy', 'Sphynx', 'Egypt', 'Loyal, Inquisitive, Friendly, Quiet, Gentle', 'The Sphynx is an intelligent, inquisitive and extremely friendly cat.', '12 - 14', '3 - 6', 5, 3, 5, 4, 5, '#e9b8a6'],
];

function respond($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function breed_json(array $b, string $host): array
{
    return [
        'weight' => ['imperial' => '7 - 10', 'metric' => $b[6]],
        'id' => $b[0], 'name' => $b[1], 'origin' => $b[2], 'temperament' => $b[3], 'description' => $b[4],
        'life_span' => $b[5], 'affection_level' => $b[7], 'child_friendly' => $b[11], 'dog_friendly' => $b[10],
        'energy_level' => $b[8], 'intelligence' => $b[9], 'reference_image_id' => $b[0] . '1',
        'image' => ['id' => $b[0] . '1', 'url' => "http://$host/img/{$b[0]}.svg"],
    ];
}

if (preg_match('#^/img/(\w+)\.svg$#', $path, $m)) {
    $color = '#c9a27e';
    foreach ($breeds as $b) {
        if ($b[0] === $m[1]) { $color = $b[12]; }
    }
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"><rect width="400" height="300" fill="' . $color . '"/>'
       . '<text x="200" y="170" font-size="110" text-anchor="middle">🐱</text>'
       . '<text x="200" y="260" font-size="22" text-anchor="middle" fill="#fff" font-family="sans-serif">' . htmlspecialchars($m[1]) . ' (mock photo)</text></svg>';
    exit;
}

if (strpos($path, '/v1/') === 0 && $mode !== 'ok') {
    respond(['message' => "simulated error $mode"], (int) $mode);
}

$host = $_SERVER['HTTP_HOST'];

if ($path === '/v1/breeds') {
    respond(array_map(function ($b) use ($host) { return breed_json($b, $host); }, $breeds));
}

if (preg_match('#^/v1/breeds/(\w+)$#', $path, $m)) {
    foreach ($breeds as $b) {
        if ($b[0] === $m[1]) { respond(breed_json($b, $host)); }
    }
    respond(['message' => 'Breed not found'], 404);
}

if ($path === '/v1/images/search') {
    $wanted = $_GET['breed_ids'] ?? null;
    $pick = $breeds[array_rand($breeds)];
    if ($wanted) {
        $pick = null;
        foreach ($breeds as $b) {
            if ($b[0] === $wanted) { $pick = $b; }
        }
        if ($pick === null) { respond([]); }
    }
    respond([[
        'breeds' => $wanted ? [breed_json($pick, $host)] : [],
        'id' => $pick[0] . 'img', 'url' => "http://$host/img/{$pick[0]}.svg", 'width' => 400, 'height' => 300,
    ]]);
}

respond(['message' => 'Not found'], 404);
