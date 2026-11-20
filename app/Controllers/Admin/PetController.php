<?php
namespace App\Controllers\Admin;

use App\Models\Pet;
use App\Services\CatApiService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Validator;

class PetController extends Controller
{
    private const RULES = [
        'name'         => 'required|min:2|max:80',
        'breed_id'     => 'required|max:10',
        'age_months'   => 'required|integer|min:0|max:300',
        'gender'       => 'required|in:male,female',
        'color'        => 'required|min:2|max:50',
        'description'  => 'required|min:10|max:2000',
        'adoption_fee' => 'required|numeric|min:0|max:10000',
        'status'       => 'required|in:available,pending,adopted',
        'image_url'    => 'nullable|url|max:255',
    ];

    public function index(Request $request): Response
    {
        return $this->view('admin/pets/index', [
            'title' => 'Adoption listings',
            'pets'  => Pet::all('id DESC'),
        ], 'layouts/admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/pets/form', [
            'title'  => 'Add cat',
            'pet'    => null,
            'breeds' => (new CatApiService())->breedOptions(),
        ], 'layouts/admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->validatedData($request);
        if ($data instanceof Response) {
            return $data;
        }

        Pet::create($data);
        $this->flash('success', $data['name'] . ' was added to the adoption list.');
        return $this->redirect('/admin/pets');
    }

    public function edit(Request $request, string $id): Response
    {
        $pet = Pet::findOrFail((int) $id);
        $breeds = (new CatApiService())->breedOptions();
        if (!isset($breeds[$pet['breed_id']])) {
            $breeds[$pet['breed_id']] = $pet['breed_name'];
        }

        return $this->view('admin/pets/form', [
            'title'  => 'Edit ' . $pet['name'],
            'pet'    => $pet,
            'breeds' => $breeds,
        ], 'layouts/admin');
    }

    public function update(Request $request, string $id): Response
    {
        $pet = Pet::findOrFail((int) $id);

        $data = $this->validatedData($request, '/admin/pets/' . (int) $id . '/edit', $pet);
        if ($data instanceof Response) {
            return $data;
        }

        if ($data['image_url'] === null && $data['breed_id'] === $pet['breed_id']) {
            $data['image_url'] = $pet['image_url'];
        }

        Pet::update((int) $id, $data);
        $this->flash('success', $data['name'] . ' was updated.');
        return $this->redirect('/admin/pets');
    }

    public function destroy(Request $request, string $id): Response
    {
        $pet = Pet::findOrFail((int) $id);
        Pet::delete((int) $id);

        $this->flash('success', $pet['name'] . ' was removed.');
        return $this->redirect('/admin/pets');
    }

    private function validatedData(Request $request, string $fallback = '/admin/pets/create', ?array $existing = null)
    {
        $v = Validator::make($request->all(), self::RULES);
        $errors = $v->errors();

        $breeds  = (new CatApiService())->breedOptions();
        $breedId = (string) $request->input('breed_id');

        $unchanged = $existing !== null && $existing['breed_id'] === $breedId;
        if (!isset($errors['breed_id']) && !isset($breeds[$breedId]) && !$unchanged) {
            $errors['breed_id'] = 'Please choose a breed from the list.';
        }
        if (!empty($errors)) {
            return $this->backWithErrors($request, $errors, $fallback);
        }

        $data = $v->validated();
        $data['breed_name'] = $breeds[$breedId] ?? $existing['breed_name'];
        $data['image_url']  = $data['image_url'] === '' ? null : $data['image_url'];
        return $data;
    }
}
