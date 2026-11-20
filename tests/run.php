<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/core/bootstrap.php';

use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CatApiService;
use Core\Cache;
use Core\Config;
use Core\Database;
use Core\Env;
use Core\HttpException;
use Core\Middleware;
use Core\Migrator;
use Core\Model;
use Core\Request;
use Core\Response;
use Core\Router;
use Core\Session;
use Core\Validator;

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');
Session::start();

$passed = 0; $failed = 0; $skipped = 0; $currentGroup = '';

function group(string $name): void { global $currentGroup; $currentGroup = $name; echo "\n\033[1m$name\033[0m\n"; }

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "  \033[32m✓\033[0m $name\n";
    } catch (\Throwable $e) {
        $failed++;
        echo "  \033[31m✗ $name\033[0m\n      " . get_class($e) . ': ' . $e->getMessage() . ' (line ' . $e->getLine() . ")\n";
    }
}

function skip(string $name, string $why): void { global $skipped; $skipped++; echo "  \033[33m- $name (skipped: $why)\033[0m\n"; }

function assertTrue($condition, string $message = 'Expected true'): void { if (!$condition) { throw new \Exception($message); } }
function assertSame($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new \Exception(($message ? "$message: " : '') . 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}
function assertThrows(string $class, callable $fn): void
{
    try { $fn(); } catch (\Throwable $e) {
        if ($e instanceof $class) { return; }
        throw new \Exception("Expected $class, got " . get_class($e) . ': ' . $e->getMessage());
    }
    throw new \Exception("Expected $class to be thrown");
}

class OrderLogMiddleware implements Middleware
{
    public static array $log = [];
    private string $name;
    public function __construct(string $name = 'A') { $this->name = $name; }
    public function handle(Request $request, callable $next): Response
    {
        self::$log[] = 'before ' . $this->name;
        $response = $next($request);
        self::$log[] = 'after ' . $this->name;
        return $response;
    }
}
class A_Mw extends OrderLogMiddleware { public function __construct() { parent::__construct('A'); } }
class B_Mw extends OrderLogMiddleware { public function __construct() { parent::__construct('B'); } }
class BlockMw implements Middleware
{
    public function handle(Request $request, callable $next): Response { return Response::redirect('/login'); }
}
class FakeController
{
    public function show(Request $r, string $id) { return Response::html("item $id"); }
    public function data(Request $r) { return ['ok' => true]; }
}

group('Env and Config');

test('Env parses values, quotes, comments and booleans', function () {
    $file = tempnam(sys_get_temp_dir(), 'env');
    file_put_contents($file, "# comment\nT_A=hello\nT_B=\"two words\"\nT_C=yes # trailing comment\nT_DEBUG=true\nT_EMPTY=\n");
    Env::load($file);
    assertSame('hello', Env::get('T_A'));
    assertSame('two words', Env::get('T_B'));
    assertSame('yes', Env::get('T_C'));
    assertSame(true, Env::get('T_DEBUG'));
    assertSame('', Env::get('T_EMPTY'));
    assertSame('fallback', Env::get('T_MISSING', 'fallback'));
    unlink($file);
});

test('Config reads nested values with dot notation', function () {
    assertSame('whiskerhub', Config::get('database.database'));
    assertSame('x', Config::get('nope.nothing', 'x'));
    Config::set('test.deep.value', 5);
    assertSame(5, Config::get('test.deep.value'));
});

group('Request');

test('Request finds the path when the project is in a sub-folder (XAMPP)', function () {
    $old = $_SERVER;
    $_SERVER = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/whiskerhub/shop?q=a', 'SCRIPT_NAME' => '/whiskerhub/public/index.php'];
    $r = Request::capture();
    assertSame('/shop', $r->path());
    assertSame('/whiskerhub', Request::basePath());
    $_SERVER = $old;
});

test('Request works when opened through /public directly', function () {
    $old = $_SERVER;
    $_SERVER = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/whiskerhub/public/adopt/3', 'SCRIPT_NAME' => '/whiskerhub/public/index.php'];
    $r = Request::capture();
    assertSame('/adopt/3', $r->path());
    assertSame('/whiskerhub/public', Request::basePath());
    $_SERVER = $old;
});

test('Request works at the web root (php -S) and for the home page', function () {
    $old = $_SERVER;
    $_SERVER = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/', 'SCRIPT_NAME' => '/index.php'];
    $r = Request::capture();
    assertSame('/', $r->path());
    assertSame('', Request::basePath());
    $_SERVER = $old;
});

test('Request reads query, body and _method override', function () {
    $old = [$_SERVER, $_POST, $_GET];
    $_SERVER = ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/x?a=1', 'SCRIPT_NAME' => '/index.php'];
    $_POST = ['name' => 'Mia', '_method' => 'DELETE'];
    $_GET = ['a' => '1'];
    $r = Request::capture();
    assertSame('DELETE', $r->method());
    assertSame('Mia', $r->input('name'));
    assertSame('1', $r->query('a'));
    assertSame(['name' => 'Mia'], $r->only(['name']));
    [$_SERVER, $_POST, $_GET] = $old;
});

test('url() helper adds the base path', function () {
    Request::setBasePath('/whiskerhub');
    assertSame('/whiskerhub/shop', url('/shop'));
    assertSame('/whiskerhub/shop?q=a+b&page=2', url('shop', ['q' => 'a b', 'page' => 2]));
    assertSame('/whiskerhub/', url('/'));
    assertSame('/whiskerhub/assets/css/style.css', asset('css/style.css'));
    Request::setBasePath('');
});

group('Router');

function makeRouter(): Router
{
    $r = new Router();
    $r->get('/', function () { return 'home'; });
    $r->get('/items/{id:\d+}', [FakeController::class, 'show']);
    $r->get('/data', [FakeController::class, 'data']);
    $r->post('/items', function () { return 'created'; });
    return $r;
}

test('Router matches static routes', function () {
    assertSame('home', makeRouter()->dispatch(new Request('GET', '/'))->getContent());
});

test('Router passes {id} to the controller method', function () {
    assertSame('item 42', makeRouter()->dispatch(new Request('GET', '/items/42'))->getContent());
});

test('Router constraint \\d+ rejects letters (404)', function () {
    assertThrows(HttpException::class, function () { makeRouter()->dispatch(new Request('GET', '/items/abc')); });
    try { makeRouter()->dispatch(new Request('GET', '/items/abc')); } catch (HttpException $e) { assertSame(404, $e->getStatusCode()); }
});

test('Router returns 405 when the path exists but the method is wrong', function () {
    try { makeRouter()->dispatch(new Request('DELETE', '/items')); throw new Exception('no exception'); }
    catch (HttpException $e) { assertSame(405, $e->getStatusCode()); }
});

test('Router turns an array result into a JSON response', function () {
    $res = makeRouter()->dispatch(new Request('GET', '/data'));
    assertSame('{"ok":true}', $res->getContent());
    assertTrue(strpos($res->getHeader('Content-Type'), 'application/json') === 0);
});

test('Route groups add a prefix', function () {
    $r = new Router();
    $r->group(['prefix' => '/admin'], function (Router $r) {
        $r->get('', function () { return 'dash'; });
        $r->get('/products', function () { return 'list'; });
    });
    assertSame('dash', $r->dispatch(new Request('GET', '/admin'))->getContent());
    assertSame('list', $r->dispatch(new Request('GET', '/admin/products'))->getContent());
});

test('Middleware runs in order around the controller (onion)', function () {
    OrderLogMiddleware::$log = [];
    $r = new Router();
    $r->globalMiddleware([A_Mw::class]);
    $r->get('/x', function () { OrderLogMiddleware::$log[] = 'controller'; return 'ok'; }, [B_Mw::class]);
    $r->dispatch(new Request('GET', '/x'));
    assertSame(['before A', 'before B', 'controller', 'after B', 'after A'], OrderLogMiddleware::$log);
});

test('A middleware can stop the request before the controller', function () {
    $called = false;
    $r = new Router();
    $r->get('/secret', function () use (&$called) { $called = true; return 'secret'; }, [BlockMw::class]);
    $res = $r->dispatch(new Request('GET', '/secret'));
    assertSame(302, $res->getStatusCode());
    assertSame('/login', $res->getHeader('Location'));
    assertTrue(!$called, 'controller should not run');
});

test('All application routes are registered', function () {
    $r = new Router();
    (require BASE_PATH . '/routes/web.php')($r);
    assertTrue(count($r->routes()) >= 40, 'only ' . count($r->routes()) . ' routes');
});

group('Response');

test('Response::redirect sets status and Location', function () {
    $res = Response::redirect('/shop');
    assertSame(302, $res->getStatusCode());
    assertSame('/shop', $res->getHeader('Location'));
});

test('Response::json encodes data', function () {
    assertSame('{"a":1,"b":"ü"}', Response::json(['a' => 1, 'b' => 'ü'])->getContent());
});

test('e() escapes HTML (XSS protection)', function () {
    assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e('<script>alert("x")</script>'));
});

group('Validator');

test('required / email / min / max for text', function () {
    $v = Validator::make(['name' => '', 'email' => 'bad', 'pw' => 'abc', 'bio' => str_repeat('x', 11)], [
        'name' => 'required', 'email' => 'required|email', 'pw' => 'min:6', 'bio' => 'max:10',
    ]);
    $e = $v->errors();
    assertTrue($v->fails());
    assertTrue(isset($e['name'], $e['email'], $e['pw'], $e['bio']), 'all four should fail');
});

test('numeric rules compare the VALUE, not the length', function () {
    assertTrue(Validator::make(['price' => '5'], ['price' => 'required|numeric|min:1|max:10'])->passes());
    assertTrue(Validator::make(['price' => '-1'], ['price' => 'required|numeric|min:0'])->fails());
    assertTrue(Validator::make(['price' => '1000'], ['price' => 'required|numeric|max:10'])->fails());
    assertTrue(Validator::make(['qty' => '3.5'], ['qty' => 'required|integer'])->fails());
});

test('in: / confirmed / nullable', function () {
    assertTrue(Validator::make(['g' => 'male'], ['g' => 'in:male,female'])->passes());
    assertTrue(Validator::make(['g' => 'robot'], ['g' => 'in:male,female'])->fails());
    assertTrue(Validator::make(['p' => 'a', 'p_confirmation' => 'b'], ['p' => 'confirmed'])->fails());
    assertTrue(Validator::make(['p' => 'a', 'p_confirmation' => 'a'], ['p' => 'confirmed'])->passes());
    assertTrue(Validator::make(['u' => ''], ['u' => 'nullable|url'])->passes());
    assertTrue(Validator::make(['u' => 'nope'], ['u' => 'nullable|url'])->fails());
});

test('unique: checks the database', function () {
    assertTrue(Validator::make(['email' => 'admin@whiskerhub.test'], ['email' => 'unique:users,email'])->fails());
    assertTrue(Validator::make(['email' => 'new-person@example.com'], ['email' => 'unique:users,email'])->passes());
    assertTrue(Validator::make(['email' => 'admin@whiskerhub.test'], ['email' => 'unique:users,email,1'])->passes(), 'own row is ignored');
});

test('validated() trims text and keeps only ruled fields', function () {
    $clean = Validator::make(['name' => '  Mia  ', 'hack' => 'x'], ['name' => 'required'])->validated();
    assertSame(['name' => 'Mia'], $clean);
});

group('Database, Model and Migrator');

class TestItem extends Model
{
    protected static string $table = 'test_items';
    protected static array $fillable = ['label', 'qty'];
}

$db = Database::getInstance();
$db->execute('DROP TABLE IF EXISTS test_items');
$db->execute('CREATE TABLE test_items (id INT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(50), qty INT DEFAULT 0, secret VARCHAR(20) DEFAULT "safe")');

test('Model::create / find / update / delete', function () {
    $id = TestItem::create(['label' => 'one', 'qty' => 5]);
    assertTrue($id > 0);
    assertSame('one', TestItem::find($id)['label']);
    TestItem::update($id, ['label' => 'uno']);
    assertSame('uno', TestItem::find($id)['label']);
    TestItem::delete($id);
    assertSame(null, TestItem::find($id));
});

test('Model ignores columns that are not $fillable (mass-assignment protection)', function () {
    $id = TestItem::create(['label' => 'x', 'secret' => 'HACKED']);
    assertSame('safe', TestItem::find($id)['secret']);
    TestItem::update($id, ['secret' => 'HACKED']);
    assertSame('safe', TestItem::find($id)['secret']);
});

test('Model::findOrFail throws a 404', function () {
    assertThrows(HttpException::class, function () { TestItem::findOrFail(999999); });
});

test('Model::paginate returns the right page and totals', function () {
    $db = Database::getInstance();
    $db->execute('DELETE FROM test_items');
    for ($i = 1; $i <= 25; $i++) { TestItem::create(['label' => "n$i", 'qty' => $i]); }
    $p = TestItem::paginate(3, 10, '1=1', [], 'qty ASC');
    assertSame(25, $p['total']);
    assertSame(3, $p['pages']);
    assertSame(5, count($p['data']));
    assertSame('n21', $p['data'][0]['label']);
    assertSame(3, TestItem::paginate(99, 10)['page'], 'page number is clamped');
});

test('SQL injection attempt is treated as plain text', function () {
    $hit = TestItem::findBy('label', "x' OR '1'='1");
    assertSame(null, $hit);
    assertSame(25, TestItem::count());
});

test('Database::transaction rolls back on error', function () {
    $before = TestItem::count();
    try {
        Database::getInstance()->transaction(function () {
            TestItem::create(['label' => 'in-transaction']);
            throw new \RuntimeException('boom');
        });
    } catch (\RuntimeException $e) {}
    assertSame($before, TestItem::count());
});

test('Migrator splits SQL on ; but not inside quotes or comments', function () {
    $sql = "-- comment; with semicolon\nINSERT INTO a VALUES ('x;y');\nINSERT INTO a VALUES (\"q\"); -- end\n";
    $parts = Migrator::splitStatements($sql);
    assertSame(2, count($parts));
    assertSame("INSERT INTO a VALUES ('x;y')", $parts[0]);
});

$db->execute('DROP TABLE test_items');

group('Cart and orders (business logic)');

test('Cart adds, caps at stock, updates and removes', function () {
    Session::set('cart', []);
    $pid = Product::create(['category_id' => 1, 'name' => 'UT cart item', 'description' => 'unit test product', 'price' => 2.50, 'stock' => 3, 'is_featured' => 0]);
    assertSame(null, CartService::add($pid, 2));
    assertSame(2, CartService::count());
    $notice = CartService::add($pid, 5);
    assertTrue($notice !== null && strpos($notice, 'stock') !== false);
    assertSame(3, CartService::count());
    $items = CartService::items();
    assertSame(7.5, CartService::total($items));
    CartService::setQuantity($pid, 1);
    assertSame(1, CartService::count());
    CartService::setQuantity($pid, 0);
    assertSame(0, CartService::count());
    assertTrue(CartService::add(999999, 1) !== null, 'unknown product is refused');
    Product::delete($pid);
});

test('Order::place saves order, items and reduces stock', function () {
    $pid = Product::create(['category_id' => 1, 'name' => 'UT order item', 'description' => 'unit test product', 'price' => 10, 'stock' => 5, 'is_featured' => 0]);
    $items = [['product' => Product::find($pid), 'quantity' => 2]];
    $orderId = Order::place(2, ['shipping_name' => 'T', 'shipping_address' => '1 Test Street', 'phone' => '12345', 'payment_method' => 'cod'], $items);
    assertSame('20.00', Order::find($orderId)['total']);
    assertSame(3, (int) Product::find($pid)['stock']);
    Order::changeStatus($orderId, 'cancelled');
    assertSame(5, (int) Product::find($pid)['stock'], 'cancel restocks');
    assertThrows(\RuntimeException::class, function () use ($orderId) { Order::changeStatus($orderId, 'pending'); });
    Order::delete($orderId);
    Product::delete($pid);
});

test('Order::place rolls back completely when stock is not enough', function () {
    $pid = Product::create(['category_id' => 1, 'name' => 'UT scarce item', 'description' => 'unit test product', 'price' => 10, 'stock' => 1, 'is_featured' => 0]);
    $ordersBefore = Order::count();
    $items = [['product' => Product::find($pid), 'quantity' => 2]];
    assertThrows(\RuntimeException::class, function () use ($items) {
        Order::place(2, ['shipping_name' => 'T', 'shipping_address' => '1 Test Street', 'phone' => '12345', 'payment_method' => 'cod'], $items);
    });
    assertSame($ordersBefore, Order::count(), 'no order was saved');
    assertSame(1, (int) Product::find($pid)['stock'], 'stock untouched');
    Product::delete($pid);
});

group('Cat API service (error handling, caching, rate limiting)');

$mockUp = @fsockopen('127.0.0.1', 8081, $errno, $errstr, 1);
if (!$mockUp) {
    skip('all API tests', 'mock server not running on 127.0.0.1:8081');
} else {
    fclose($mockUp);
    $modeFile = BASE_PATH . '/tests/mock_catapi/mode.txt';
    $setMode = function (string $mode) use ($modeFile) { file_put_contents($modeFile, $mode); };
    $reset = function () use ($setMode) {
        $setMode('ok');
        foreach (['cat_breeds', (new CatApiService())->pauseKey(), 'cat_of_the_day_' . date('Y-m-d')] as $k) { Cache::forget($k); }
    };
    Config::set('services.cat_api.url', 'http://127.0.0.1:8081/v1');

    test('breeds() loads the list from the API and caches it', function () use ($reset, $setMode) {
        $reset();
        $api = new CatApiService();
        $breeds = $api->breeds();
        assertTrue(count($breeds) >= 12);
        assertSame(null, $api->lastError());
        $setMode('500');
        assertTrue(count((new CatApiService())->breeds()) >= 12, 'served from cache');
        $reset();
    });

    test('breed() finds one breed and photoForBreed() returns a URL', function () use ($reset) {
        $reset();
        $api = new CatApiService();
        assertSame('Siamese', $api->breed('siam')['name']);
        assertSame(null, $api->breed('does-not-exist'));
        assertTrue(strpos($api->photoForBreed('beng')['url'], 'beng') !== false);
        $reset();
    });

    test('HTTP 429 (rate limit): no crash, friendly error, circuit breaker opens', function () use ($reset, $setMode) {
        $reset();
        $setMode('429');
        $api = new CatApiService();
        assertSame(null, $api->photoForBreed('siam'));
        assertTrue(strpos($api->lastError(), 'rate limit') !== false);
        assertTrue(Cache::get($api->pauseKey()) !== null, 'breaker is open');
        $setMode('ok');
        assertSame(null, (new CatApiService())->photoForBreed('siam'), 'no call while paused');
        $reset();
    });

    test('HTTP 401 (bad API key) gives a clear message, and a new key is tried immediately', function () use ($reset, $setMode) {
        $reset();
        $setMode('401');
        $api = new CatApiService();
        assertSame(null, $api->photoForBreed('siam'));
        assertTrue(strpos($api->lastError(), 'key') !== false);
        $setMode('ok');
        Config::set('services.cat_api.key', 'my-new-key');
        assertTrue((new CatApiService())->photoForBreed('siam') !== null, 'not blocked by the old pause');
        Config::set('services.cat_api.key', '');
        Cache::forget((new CatApiService())->pauseKey());
        $reset();
    });

    test('HTTP 500: stale cached data is served instead of an error', function () use ($reset, $setMode) {
        $reset();
        (new CatApiService())->breeds();
        Database::getInstance()->execute("UPDATE cache SET expires_at = 1 WHERE cache_key = 'cat_breeds'");
        $setMode('500');
        $api = new CatApiService();
        assertTrue(count($api->breeds()) >= 12, 'stale copy used');
        $reset();
    });

    test('breedOptions() falls back to the built-in list when the API is down and nothing is cached', function () use ($reset, $setMode) {
        $reset();
        Cache::forget('cat_breeds');
        $setMode('500');
        $options = (new CatApiService())->breedOptions();
        assertSame('Siamese', $options['siam']);
        $reset();
    });

    test('Network failure (server unreachable) is handled', function () use ($reset) {
        $reset();
        Config::set('services.cat_api.url', 'http://127.0.0.1:9/v1');
        $api = new CatApiService();
        assertSame(null, $api->photoForBreed('siam'));
        assertTrue(strpos($api->lastError(), 'Could not reach') !== false, $api->lastError());
        Config::set('services.cat_api.url', 'http://127.0.0.1:8081/v1');
        $reset();
    });

    test('catOfTheDay() is cached for the day', function () use ($reset, $setMode) {
        $reset();
        $first = (new CatApiService())->catOfTheDay();
        assertTrue(!empty($first['url']));
        $setMode('500');
        assertSame($first, (new CatApiService())->catOfTheDay(), 'same cat all day, no new API call');
        $reset();
    });

    @unlink($modeFile);
}

echo "\n\033[1mResult: $passed passed, $failed failed" . ($skipped ? ", $skipped skipped" : '') . "\033[0m\n";
exit($failed === 0 ? 0 : 1);
