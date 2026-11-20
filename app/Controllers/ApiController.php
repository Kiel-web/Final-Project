<?php
namespace App\Controllers;

use App\Models\Pet;
use App\Services\CatApiService;
use Core\Controller;
use Core\Request;
use Core\Response;

class ApiController extends Controller
{
    public function catOfTheDay(Request $request): Response
    {
        $api = new CatApiService();
        $cat = $api->catOfTheDay();

        if ($cat === null) {
            return $this->json(['ok' => false, 'message' => $api->lastError() ?? 'No photo available.'], 503);
        }
        return $this->json(['ok' => true, 'url' => $cat['url'], 'breed' => $cat['breed'] ?? null]);
    }

    public function petPhoto(Request $request, string $id): Response
    {
        $pet = Pet::find((int) $id);
        if ($pet === null) {
            return $this->json(['ok' => false, 'message' => 'Pet not found.'], 404);
        }

        if (!empty($pet['image_url'])) {
            return $this->json(['ok' => true, 'url' => $pet['image_url']]);
        }

        $api   = new CatApiService();
        $photo = $api->photoForBreed($pet['breed_id']);
        if ($photo === null) {
            return $this->json(['ok' => false, 'message' => $api->lastError() ?? 'No photo available.'], 503);
        }

        Pet::update($pet['id'], ['image_url' => $photo['url']]);
        return $this->json(['ok' => true, 'url' => $photo['url']]);
    }
}
