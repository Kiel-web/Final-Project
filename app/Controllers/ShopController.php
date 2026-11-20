<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use Core\Controller;
use Core\Request;
use Core\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $term       = trim((string) $request->query('q', ''));
        $categoryId = (int) $request->query('category', 0) ?: null;
        $page       = (int) $request->query('page', 1);

        $result = Product::search($term, $categoryId, $page, (int) config('app.per_page', 9));

        return $this->view('shop/index', [
            'title'      => 'Shop',
            'products'   => $result['data'],
            'paging'     => $result,
            'categories' => Category::withProductCounts(),
            'term'       => $term,
            'categoryId' => $categoryId,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $product = Product::findWithCategory((int) $id);
        if ($product === null) {
            $this->abort(404, 'That product was not found.');
        }

        return $this->view('shop/show', [
            'title'   => $product['name'],
            'product' => $product,
            'related' => Product::related((int) $product['category_id'], (int) $product['id'], 4),
        ]);
    }
}
