<?php
namespace App\Controllers;

use App\Models\Inquiry;
use App\Models\Pet;
use App\Services\CatApiService;
use Core\Auth;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Validator;

class AdoptionController extends Controller
{
    public function index(Request $request): Response
    {
        $term   = trim((string) $request->query('q', ''));
        $gender = (string) $request->query('gender', '');
        $status = (string) $request->query('status', '');

        $result = Pet::search($term, $gender, $status, (int) $request->query('page', 1), 12);

        return $this->view('adopt/index', [
            'title'  => 'Adopt a cat',
            'pets'   => $result['data'],
            'paging' => $result,
            'term'   => $term,
            'gender' => $gender,
            'status' => $status,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $pet = Pet::find((int) $id);
        if ($pet === null) {
            $this->abort(404, 'That cat was not found.');
        }

        $api   = new CatApiService();
        $breed = $api->breed($pet['breed_id']);
        $apiProblem = $breed === null ? $api->lastError() : null;

        if (empty($pet['image_url'])) {
            $photo = $api->photoForBreed($pet['breed_id']);
            if ($photo !== null) {
                Pet::update($pet['id'], ['image_url' => $photo['url']]);
                $pet['image_url'] = $photo['url'];
            }
        }

        return $this->view('adopt/show', [
            'title'      => $pet['name'] . ' - ' . $pet['breed_name'],
            'pet'        => $pet,
            'breed'      => $breed,
            'apiProblem' => $apiProblem,
            'user'       => Auth::user(),
        ]);
    }

    public function inquire(Request $request, string $id): Response
    {
        $pet = Pet::find((int) $id);
        if ($pet === null) {
            $this->abort(404, 'That cat was not found.');
        }
        if ($pet['status'] !== 'available') {
            $this->flash('error', $pet['name'] . ' is no longer available for adoption.');
            return $this->redirect('/adopt/' . $pet['id']);
        }

        $v = Validator::make($request->all(), [
            'name'    => 'required|min:2|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|max:30',
            'message' => 'required|min:10|max:1000',
        ]);
        if ($v->fails()) {
            return $this->backWithErrors($request, $v->errors(), '/adopt/' . $pet['id']);
        }

        $data = $v->validated();
        $data['pet_id']  = $pet['id'];
        $data['user_id'] = Auth::id();
        $data['status']  = 'new';
        Inquiry::create($data);

        $this->flash('success', 'Thank you! Your inquiry about ' . $pet['name'] . ' was sent. We will contact you soon.');
        return $this->redirect('/adopt/' . $pet['id']);
    }
}
