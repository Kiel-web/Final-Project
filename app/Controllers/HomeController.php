<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\Pet;
use App\Models\Product;
use Core\Controller;
use Core\Request;
use Core\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('home/index', [
            'title'      => 'Home',
            'featured'   => Product::featured(4),
            'pets'       => Pet::latestAvailable(4),
            'categories' => Category::all('id ASC'),
        ]);
    }
}
