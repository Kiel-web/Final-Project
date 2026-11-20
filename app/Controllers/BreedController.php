<?php
namespace App\Controllers;

use App\Services\CatApiService;
use Core\Controller;
use Core\Request;
use Core\Response;

class BreedController extends Controller
{
    public function index(Request $request): Response
    {
        $term = trim((string) $request->query('q', ''));

        $api    = new CatApiService();
        $breeds = $api->breeds();
        $total  = count($breeds);

        if ($term !== '') {
            $breeds = array_values(array_filter($breeds, function ($b) use ($term) {
                $haystack = ($b['name'] ?? '') . ' ' . ($b['origin'] ?? '') . ' ' . ($b['temperament'] ?? '');
                return stripos($haystack, $term) !== false;
            }));
        }

        return $this->view('breeds/index', [
            'title'      => 'Cat breed guide',
            'breeds'     => $breeds,
            'total'      => $total,
            'term'       => $term,
            'apiProblem' => $total === 0 ? ($api->lastError() ?? 'No breed data available.') : null,
        ]);
    }
}
