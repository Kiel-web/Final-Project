<?php
namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Validator;

class ProductController extends Controller
{
    private const RULES = [
        'category_id' => 'required|integer',
        'name'        => 'required|min:2|max:150',
        'description' => 'required|min:5|max:2000',
        'price'       => 'required|numeric|min:0|max:100000',
        'stock'       => 'required|integer|min:0|max:100000',
        'image_url'   => 'nullable|url|max:255',
    ];

    public function index(Request $request): Response
    {
        return $this->view('admin/products/index', [
            'title'    => 'Products',
            'products' => Product::adminList(),
        ], 'layouts/admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/products/form', [
            'title'      => 'Add product',
            'product'    => null,
            'categories' => Category::all('name ASC'),
        ], 'layouts/admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->validatedData($request);
        if ($data instanceof Response) {
            return $data;
        }

        Product::create($data);
        $this->flash('success', 'Product "' . $data['name'] . '" was added.');
        return $this->redirect('/admin/products');
    }

    public function edit(Request $request, string $id): Response
    {
        $product = Product::findOrFail((int) $id);

        return $this->view('admin/products/form', [
            'title'      => 'Edit product',
            'product'    => $product,
            'categories' => Category::all('name ASC'),
        ], 'layouts/admin');
    }

    public function update(Request $request, string $id): Response
    {
        Product::findOrFail((int) $id);

        $data = $this->validatedData($request, '/admin/products/' . (int) $id . '/edit');
        if ($data instanceof Response) {
            return $data;
        }

        Product::update((int) $id, $data);
        $this->flash('success', 'Product "' . $data['name'] . '" was updated.');
        return $this->redirect('/admin/products');
    }

    public function destroy(Request $request, string $id): Response
    {
        $product = Product::findOrFail((int) $id);
        Product::delete((int) $id);

        $this->flash('success', 'Product "' . $product['name'] . '" was deleted.');
        return $this->redirect('/admin/products');
    }

    private function validatedData(Request $request, string $fallback = '/admin/products/create')
    {
        $v = Validator::make($request->all(), self::RULES);
        $errors = $v->errors();

        if (!isset($errors['category_id']) && Category::find((int) $request->input('category_id')) === null) {
            $errors['category_id'] = 'Please choose a valid category.';
        }
        if (!empty($errors)) {
            return $this->backWithErrors($request, $errors, $fallback);
        }

        $data = $v->validated();
        $data['image_url']   = $data['image_url'] === '' ? null : $data['image_url'];
        $data['is_featured'] = $request->input('is_featured') ? 1 : 0;
        return $data;
    }
}
