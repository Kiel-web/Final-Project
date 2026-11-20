<?php
namespace App\Controllers\Admin;

use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Product;
use Core\Controller;
use Core\Request;
use Core\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/dashboard', [
            'title'        => 'Dashboard',
            'productCount' => Product::count(),
            'petCounts'    => Pet::countByStatus(),
            'orderCounts'  => Order::countByStatus(),
            'newInquiries' => Inquiry::count("status = 'new'"),
            'revenue'      => Order::revenue(),
            'recentOrders' => Order::recent(5),
            'lowStock'     => Product::lowStock(5),
        ], 'layouts/admin');
    }
}
