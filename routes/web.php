<?php
use App\Controllers\AdoptionController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\PetController as AdminPetController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\BreedController;
use App\Controllers\CartController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\ShopController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Core\Router;

return function (Router $router) {

    $router->get('/', [HomeController::class, 'index']);

    $router->get('/shop', [ShopController::class, 'index']);
    $router->get('/shop/{id:\d+}', [ShopController::class, 'show']);

    $router->get('/adopt', [AdoptionController::class, 'index']);
    $router->get('/adopt/{id:\d+}', [AdoptionController::class, 'show']);
    $router->post('/adopt/{id:\d+}/inquire', [AdoptionController::class, 'inquire']);

    $router->get('/breeds', [BreedController::class, 'index']);

    $router->get('/api/cat-of-the-day', [ApiController::class, 'catOfTheDay']);
    $router->get('/api/pets/{id:\d+}/photo', [ApiController::class, 'petPhoto']);

    $router->get('/cart', [CartController::class, 'index']);
    $router->post('/cart', [CartController::class, 'add']);
    $router->put('/cart/{id:\d+}', [CartController::class, 'update']);
    $router->delete('/cart/{id:\d+}', [CartController::class, 'remove']);

    $router->group(['middleware' => [GuestMiddleware::class]], function (Router $r) {
        $r->get('/login', [AuthController::class, 'showLogin']);
        $r->post('/login', [AuthController::class, 'login']);
        $r->get('/register', [AuthController::class, 'showRegister']);
        $r->post('/register', [AuthController::class, 'register']);
    });

    $router->group(['middleware' => [AuthMiddleware::class]], function (Router $r) {
        $r->post('/logout', [AuthController::class, 'logout']);

        $r->get('/checkout', [OrderController::class, 'checkout']);
        $r->post('/checkout', [OrderController::class, 'place']);

        $r->get('/orders', [OrderController::class, 'index']);
        $r->get('/orders/{id:\d+}', [OrderController::class, 'show']);
    });

    $router->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (Router $r) {
        $r->get('', [DashboardController::class, 'index']);

        $r->get('/products', [AdminProductController::class, 'index']);
        $r->get('/products/create', [AdminProductController::class, 'create']);
        $r->post('/products', [AdminProductController::class, 'store']);
        $r->get('/products/{id:\d+}/edit', [AdminProductController::class, 'edit']);
        $r->put('/products/{id:\d+}', [AdminProductController::class, 'update']);
        $r->delete('/products/{id:\d+}', [AdminProductController::class, 'destroy']);

        $r->get('/pets', [AdminPetController::class, 'index']);
        $r->get('/pets/create', [AdminPetController::class, 'create']);
        $r->post('/pets', [AdminPetController::class, 'store']);
        $r->get('/pets/{id:\d+}/edit', [AdminPetController::class, 'edit']);
        $r->put('/pets/{id:\d+}', [AdminPetController::class, 'update']);
        $r->delete('/pets/{id:\d+}', [AdminPetController::class, 'destroy']);

        $r->get('/orders', [AdminOrderController::class, 'index']);
        $r->get('/orders/{id:\d+}', [AdminOrderController::class, 'show']);
        $r->put('/orders/{id:\d+}/status', [AdminOrderController::class, 'updateStatus']);

        $r->get('/inquiries', [AdminInquiryController::class, 'index']);
        $r->put('/inquiries/{id:\d+}/status', [AdminInquiryController::class, 'updateStatus']);
    });
};
