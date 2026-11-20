<?php
namespace App\Controllers;

use App\Services\CartService;
use Core\Controller;
use Core\Request;
use Core\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $items = CartService::items();

        return $this->view('cart/index', [
            'title' => 'Your cart',
            'items' => $items,
            'total' => CartService::total($items),
        ]);
    }

    public function add(Request $request): Response
    {
        $notice = CartService::add((int) $request->input('product_id'), (int) $request->input('quantity', 1));

        if ($notice !== null) {
            $this->flash('error', $notice);
        } else {
            $this->flash('success', 'Added to your cart.');
        }
        return $this->back($request, '/shop');
    }

    public function update(Request $request, string $id): Response
    {
        $notice = CartService::setQuantity((int) $id, (int) $request->input('quantity', 1));
        if ($notice !== null) {
            $this->flash('error', $notice);
        }
        return $this->redirect('/cart');
    }

    public function remove(Request $request, string $id): Response
    {
        CartService::remove((int) $id);
        $this->flash('success', 'Item removed from your cart.');
        return $this->redirect('/cart');
    }
}
