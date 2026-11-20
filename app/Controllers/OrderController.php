<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Core\Auth;
use Core\Controller;
use Core\Logger;
use Core\Request;
use Core\Response;
use Core\Validator;

class OrderController extends Controller
{
    public function checkout(Request $request): Response
    {
        $items = CartService::items();
        if (empty($items)) {
            $this->flash('info', 'Your cart is empty.');
            return $this->redirect('/shop');
        }

        return $this->view('orders/checkout', [
            'title' => 'Checkout',
            'items' => $items,
            'total' => CartService::total($items),
            'user'  => Auth::user(),
        ]);
    }

    public function place(Request $request): Response
    {
        $items = CartService::items();
        if (empty($items)) {
            return $this->redirect('/shop');
        }

        $v = Validator::make($request->all(), [
            'shipping_name'    => 'required|min:2|max:100',
            'shipping_address' => 'required|min:8|max:255',
            'phone'            => 'required|min:6|max:30',
            'payment_method'   => 'required|in:cod,pickup',
        ]);
        if ($v->fails()) {
            return $this->backWithErrors($request, $v->errors(), '/checkout');
        }

        try {
            $orderId = Order::place(Auth::id(), $v->validated(), $items);
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            return $this->redirect('/cart');
        }

        Logger::info("Order #$orderId placed by user " . Auth::id());
        CartService::clear();
        $this->flash('success', "Thank you! Your order #$orderId was placed.");
        return $this->redirect('/orders/' . $orderId);
    }

    public function index(Request $request): Response
    {
        return $this->view('orders/index', [
            'title'  => 'My orders',
            'orders' => Order::forUser(Auth::id()),
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $order = Order::find((int) $id);

        if ($order === null || (int) $order['user_id'] !== Auth::id()) {
            $this->abort(404, 'Order not found.');
        }

        return $this->view('orders/show', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => OrderItem::forOrder((int) $order['id']),
        ]);
    }
}
