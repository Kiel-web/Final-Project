<?php
namespace App\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Core\Controller;
use Core\Request;
use Core\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');

        return $this->view('admin/orders/index', [
            'title'  => 'Orders',
            'orders' => Order::adminList($status),
            'status' => $status,
        ], 'layouts/admin');
    }

    public function show(Request $request, string $id): Response
    {
        $order = Order::withCustomer((int) $id);
        if ($order === null) {
            $this->abort(404, 'Order not found.');
        }

        return $this->view('admin/orders/show', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => OrderItem::forOrder((int) $order['id']),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request, string $id): Response
    {
        try {
            Order::changeStatus((int) $id, (string) $request->input('status'));
            $this->flash('success', 'Order #' . (int) $id . ' status updated.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        return $this->back($request, '/admin/orders/' . (int) $id);
    }
}
