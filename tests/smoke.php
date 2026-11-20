<?php
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$jarFile = tempnam(sys_get_temp_dir(), 'jar');

$passed = 0;
$failed = 0;

function check(string $label, bool $condition, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  \033[32m✓\033[0m $label\n";
    } else {
        $failed++;
        echo "  \033[31m✗ $label\033[0m" . ($detail ? "  -> $detail" : '') . "\n";
    }
}

function section(string $title): void
{
    echo "\n\033[1m$title\033[0m\n";
}

function request(string $method, string $path, array $data = [], bool $follow = true, ?string $jar = null): array
{
    global $base, $jarFile;
    $url = strpos($path, 'http') === 0 ? $path : $base . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR      => $jar ?? $jarFile,
        CURLOPT_COOKIEFILE     => $jar ?? $jarFile,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HEADER         => true,
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $size   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $final  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    $headers = substr((string) $raw, 0, $size);
    $location = preg_match('/^Location:\s*(\S+)/mi', $headers, $m) ? $m[1] : null;

    return ['status' => $status, 'body' => substr((string) $raw, $size), 'url' => $final, 'location' => $location];
}

function get(string $path, bool $follow = true, ?string $jar = null): array
{
    return request('GET', $path, [], $follow, $jar);
}

function submit(string $formPage, string $action, array $data, bool $follow = true, ?string $jar = null): array
{
    $page = get($formPage, true, $jar);
    $token = preg_match('/name="_token" value="([^"]+)"/', $page['body'], $m) ? $m[1] : '';
    return request('POST', $action, array_merge(['_token' => $token], $data), $follow, $jar);
}

function stock_of(int $productId): int
{
    $page = get("/shop/$productId");
    return preg_match('/(\d+) in stock/', $page['body'], $m) ? (int) $m[1] : 0;
}

function login(string $email, string $password, ?string $jar = null): array
{
    return submit('/login', '/login', ['email' => $email, 'password' => $password], true, $jar);
}

section('1. Public pages');
foreach (['/', '/shop', '/shop?q=salmon', '/shop?category=2', '/shop/1', '/adopt', '/adopt?gender=female&status=available', '/adopt/1', '/breeds', '/breeds?q=bengal', '/cart', '/login', '/register'] as $path) {
    $r = get($path);
    check("GET $path -> 200", $r['status'] === 200, 'got ' . $r['status']);
}
check('Home page shows the shop name', strpos(get('/')['body'], 'WhiskerHub') !== false);
check('Search finds "salmon"', strpos(get('/shop?q=salmon')['body'], 'Salmon Delight') !== false);
check('Search hides non-matching products', strpos(get('/shop?q=salmon')['body'], 'Feather Wand') === false);
check('Pet page shows Cat API breed facts', strpos(get('/adopt/1')['body'], 'Thailand') !== false);
check('Breed guide lists breeds from the API', strpos(get('/breeds')['body'], 'Maine Coon') !== false);
$json = json_decode(get('/api/cat-of-the-day')['body'], true);
check('/api/cat-of-the-day returns JSON {ok:true,url}', ($json['ok'] ?? false) === true && !empty($json['url']));
$json = json_decode(get('/api/pets/2/photo')['body'], true);
check('/api/pets/2/photo returns a photo URL', ($json['ok'] ?? false) === true && !empty($json['url']));

section('2. Errors and security');
check('Unknown page -> 404', get('/this-page-does-not-exist')['status'] === 404);
check('Unknown product -> 404', get('/shop/99999')['status'] === 404);
check('Unknown pet -> 404', get('/adopt/99999')['status'] === 404);
check('JSON 404 for unknown pet photo', get('/api/pets/99999/photo')['status'] === 404);
check('POST without CSRF token is refused (403)', request('POST', '/cart', ['product_id' => 1, 'quantity' => 1], false)['status'] === 403);
check('Wrong HTTP method -> 405', request('POST', '/shop', [], false)['status'] === 405);
check('Guest opening /admin is redirected to /login', strpos((string) get('/admin', false)['location'], '/login') !== false);
check('Guest opening /checkout is redirected to /login', strpos((string) get('/checkout', false)['location'], '/login') !== false);
check('Guest opening /orders is redirected to /login', strpos((string) get('/orders', false)['location'], '/login') !== false);

section('3. Register and login');
$email = 'student' . time() . '@example.com';
$r = submit('/register', '/register', ['name' => 'A', 'email' => 'not-an-email', 'password' => '123', 'password_confirmation' => '999']);
check('Invalid registration shows validation errors', strpos($r['body'], 'valid email') !== false && strpos($r['body'], 'at least 6') !== false);
$r = submit('/register', '/register', ['name' => 'Test Student', 'email' => $email, 'password' => 'secret123', 'password_confirmation' => 'secret123']);
check('Valid registration logs the user in', strpos($r['body'], 'Log out (Test Student)') !== false);
$r = get('/register');
check('Logged-in user cannot open /register again (redirected home)', strpos($r['body'], 'Create your account') === false);
$r = submit('/', '/logout', []);
check('Logout works', strpos($r['body'], 'Log in') !== false && strpos($r['body'], 'Log out') === false);
$r = submit('/register', '/register', ['name' => 'Test Student', 'email' => $email, 'password' => 'secret123', 'password_confirmation' => 'secret123']);
check('Duplicate email is rejected', strpos($r['body'], 'already taken') !== false);
$r = login($email, 'wrong-password');
check('Wrong password is rejected', strpos($r['body'], 'Invalid email or password') !== false);
$r = login($email, 'secret123');
check('Correct login works', strpos($r['body'], 'Welcome back, Test Student') !== false);

section('4. Cart and checkout');
$stockBefore = stock_of(5);
$r = submit('/shop/5', '/cart', ['product_id' => 5, 'quantity' => 2]);
check('Add to cart shows confirmation', strpos($r['body'], 'Added to your cart') !== false);
$cart = get('/cart')['body'];
check('Cart lists the product and total', strpos($cart, 'Feather Wand Teaser') !== false && strpos($cart, '₱298.00') !== false);
$r = submit('/cart', '/cart/5', ['_method' => 'PUT', 'quantity' => 3]);
check('Updating quantity to 3 changes the total', strpos($r['body'], '₱447.00') !== false);
$r = submit('/cart', '/cart', ['product_id' => 9, 'quantity' => 1]);
check('Out-of-stock product cannot be added', strpos($r['body'], 'out of stock') !== false);
$r = submit('/shop/5', '/cart', ['product_id' => 5, 'quantity' => 9999]);
check('Quantity is capped at the available stock', strpos($r['body'], 'in stock - quantity adjusted') !== false);
$r = submit('/cart', '/cart/5', ['_method' => 'PUT', 'quantity' => 3]);

$r = submit('/checkout', '/checkout', ['shipping_name' => '', 'shipping_address' => 'x', 'phone' => '1', 'payment_method' => 'bitcoin']);
check('Checkout validates the form', strpos($r['body'], 'is required') !== false || strpos($r['body'], 'at least') !== false);
$r = submit('/checkout', '/checkout', ['shipping_name' => 'Test Student', 'shipping_address' => '12 Cat Street, Meow City', 'phone' => '555-1234', 'payment_method' => 'cod']);
check('Order is placed', strpos($r['body'], 'was placed') !== false, substr(strip_tags($r['body']), 0, 200));
preg_match('/Order #(\d+)/', $r['body'], $m);
$orderId = (int) ($m[1] ?? 0);
check('Order page shows the item and total', strpos($r['body'], 'Feather Wand Teaser') !== false && strpos($r['body'], '₱447.00') !== false);
check('Stock was reduced by 3', stock_of(5) === $stockBefore - 3, 'before ' . $stockBefore . ', after ' . stock_of(5));
check('Cart is empty after the order', strpos(get('/cart')['body'], 'Your cart is empty') !== false);
check('"My orders" lists the new order', strpos(get('/orders')['body'], "#$orderId") !== false);

section('5. Customers cannot access admin or other customers\' data');
check('Customer opening /admin -> 403', get('/admin')['status'] === 403);
check('Customer opening /admin/products -> 403', get('/admin/products')['status'] === 403);
check('Customer cannot create a product (403)', submit('/', '/admin/products', ['name' => 'Hack'], false)['status'] === 403);
$other = '/tmp/jar_other_' . getmypid();
login('customer@whiskerhub.test', 'customer123', $other);
check('Another customer cannot see my order (404)', get("/orders/$orderId", true, $other)['status'] === 404);

section('6. Adoption inquiry');
$guestJar = '/tmp/jar_guest_' . getmypid();
$r = submit('/adopt/3', '/adopt/3/inquire', ['name' => 'G', 'email' => 'bad', 'message' => 'hi'], true, $guestJar);
check('Inquiry form validates input', strpos($r['body'], 'valid email') !== false);
$r = submit('/adopt/3', '/adopt/3/inquire', ['name' => 'Guest Visitor', 'email' => 'guest@example.com', 'phone' => '', 'message' => 'I would love to adopt Cleo, I have a quiet apartment.'], true, $guestJar);
check('Guest can send an inquiry', strpos($r['body'], 'inquiry about Cleo was sent') !== false);
check('Adopted cat page has no inquiry form', strpos(get('/adopt/12')['body'], 'name="message"') === false);
$r = submit('/adopt/3', '/adopt/12/inquire', ['name' => 'Guest Visitor', 'email' => 'guest@example.com', 'message' => 'Can I adopt Snowball please?'], true, $guestJar);
check('Server also refuses inquiries for adopted cats', strpos($r['body'], 'no longer available') !== false);

section('7. Admin: products');
$admin = '/tmp/jar_admin_' . getmypid();
$r = login('admin@whiskerhub.test', 'admin123', $admin);
check('Admin login goes to the dashboard', strpos($r['body'], 'Dashboard') !== false);
check('Dashboard shows statistics', strpos($r['body'], 'Revenue') !== false && strpos($r['body'], 'Low stock') !== false);
foreach (['/admin/products', '/admin/products/create', '/admin/products/1/edit', '/admin/pets', '/admin/pets/create', '/admin/pets/1/edit', '/admin/orders', "/admin/orders/$orderId", '/admin/inquiries'] as $p) {
    check("Admin GET $p -> 200", get($p, true, $admin)['status'] === 200);
}
$r = submit('/admin/products/create', '/admin/products', ['name' => 'X', 'category_id' => 99, 'description' => 'abc', 'price' => '-5', 'stock' => 'many'], true, $admin);
check('Product form validation works', strpos($r['body'], 'must be at least') !== false && strpos($r['body'], 'valid category') !== false);
$r = submit('/admin/products/create', '/admin/products', ['name' => 'Smoke Test Treats', 'category_id' => 1, 'description' => 'Tasty test treats for cats', 'price' => '4.50', 'stock' => 12, 'image_url' => '', 'is_featured' => 1], true, $admin);
check('Admin can create a product', strpos($r['body'], 'Smoke Test Treats') !== false && strpos($r['body'], 'was added') !== false);
preg_match('#<td>(\d+)</td>\s*<td>Smoke Test Treats</td>#', $r['body'], $m);
$productId = (int) ($m[1] ?? 0);
check('New product has an id', $productId > 0);
check('New product is visible in the shop', strpos(get('/shop?q=Smoke+Test')['body'], 'Smoke Test Treats') !== false);
$r = submit("/admin/products/$productId/edit", "/admin/products/$productId", ['_method' => 'PUT', 'name' => 'Smoke Test Treats XL', 'category_id' => 1, 'description' => 'Tasty test treats for cats', 'price' => '5.75', 'stock' => 20], true, $admin);
check('Admin can edit a product', strpos($r['body'], 'was updated') !== false && strpos($r['body'], 'Smoke Test Treats XL') !== false);
$r = submit('/admin/products', "/admin/products/$productId", ['_method' => 'DELETE'], true, $admin);
check('Admin can delete a product', strpos($r['body'], 'was deleted') !== false && strpos($r['body'], '<td>Smoke Test Treats XL</td>') === false);
check('Deleted product returns 404', get("/shop/$productId")['status'] === 404);

section('8. Admin: adoption listings');
$r = submit('/admin/pets/create', '/admin/pets', ['name' => 'T', 'breed_id' => 'zzzz', 'age_months' => 'x', 'gender' => 'robot', 'color' => '', 'description' => 'short', 'adoption_fee' => '-1', 'status' => 'lost'], true, $admin);
check('Pet form validation works (invalid breed, gender, status ...)', strpos($r['body'], 'choose a breed') !== false && strpos($r['body'], 'invalid value') !== false);
$r = submit('/admin/pets/create', '/admin/pets', ['name' => 'Smoky Test', 'breed_id' => 'beng', 'age_months' => 9, 'gender' => 'male', 'color' => 'Gold', 'description' => 'A very energetic test kitten.', 'adoption_fee' => '40', 'status' => 'available', 'image_url' => ''], true, $admin);
check('Admin can add a cat (breed name taken from the API list)', strpos($r['body'], 'Smoky Test') !== false && strpos($r['body'], 'Bengal') !== false);
preg_match('#/adopt/(\d+)" target="_blank">Smoky Test#', $r['body'], $m);
$petId = (int) ($m[1] ?? 0);
check('New cat has an id', $petId > 0);
$page = get("/adopt/$petId")['body'];
check('New cat page loads and fetches a photo from the API', strpos($page, 'img/beng.svg') !== false);
$r = submit("/admin/pets/$petId/edit", "/admin/pets/$petId", ['_method' => 'PUT', 'name' => 'Smoky Test', 'breed_id' => 'beng', 'age_months' => 9, 'gender' => 'male', 'color' => 'Gold', 'description' => 'A very energetic test kitten.', 'adoption_fee' => '40', 'status' => 'adopted', 'image_url' => ''], true, $admin);
check('Admin can mark a cat as adopted', strpos($r['body'], 'was updated') !== false);
check('Adopted cat shows the Adopted status', strpos(get("/adopt/$petId")['body'], 'Adopted') !== false);
$r = submit('/admin/pets', "/admin/pets/$petId", ['_method' => 'DELETE'], true, $admin);
check('Admin can delete a cat', strpos($r['body'], 'was removed') !== false);

section('9. Admin: orders and inquiries');
$stockNow = stock_of(5);
$r = submit("/admin/orders/$orderId", "/admin/orders/$orderId/status", ['_method' => 'PUT', 'status' => 'processing'], true, $admin);
check('Admin can set order to processing', strpos($r['body'], 'status updated') !== false);
$r = submit("/admin/orders/$orderId", "/admin/orders/$orderId/status", ['_method' => 'PUT', 'status' => 'cancelled'], true, $admin);
check('Admin can cancel the order', strpos($r['body'], 'Cancelled') !== false);
check('Cancelling puts the stock back (+3)', stock_of(5) === $stockNow + 3);
$r = submit("/admin/orders/$orderId", "/admin/orders/$orderId/status", ['_method' => 'PUT', 'status' => 'pending'], true, $admin);
check('A cancelled order cannot be re-opened', strpos($r['body'], 'cannot be re-opened') !== false);
check('Orders can be filtered by status', strpos(get('/admin/orders?status=cancelled', true, $admin)['body'], "#$orderId") !== false);
$inq = get('/admin/inquiries?status=new', true, $admin)['body'];
check('Guest inquiry appears as "new" in admin', strpos($inq, 'Guest Visitor') !== false);
preg_match('#/admin/inquiries/(\d+)/status#', $inq, $m);
$r = submit('/admin/inquiries', '/admin/inquiries/' . $m[1] . '/status', ['_method' => 'PUT', 'status' => 'contacted'], true, $admin);
check('Admin can mark an inquiry as contacted', strpos($r['body'], 'marked as') !== false);

section('10. Resilience');
check('Site works when visiting many pages in a row (no 500s)', array_sum(array_map(function ($p) { return get($p)['status'] >= 500 ? 1 : 0; }, ['/', '/shop', '/adopt', '/breeds'])) === 0);

@unlink($jarFile);
@unlink($other);
@unlink($guestJar);
@unlink($admin);

echo "\n\033[1mResult: $passed passed, $failed failed\033[0m\n";
exit($failed === 0 ? 0 : 1);
