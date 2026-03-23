<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// End preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\GraphQL;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;

// Initialize repositories
$productRepository  = new ProductRepository();
$categoryRepository = new CategoryRepository();
$orderRepository    = new OrderRepository($productRepository);

// Initialize GraphQL controller
$graphqlController = new GraphQL(
    $productRepository,
    $categoryRepository,
    $orderRepository
);

// Setup FastRoute
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($graphqlController) {
    $r->post('/graphql', [$graphqlController, 'handle']); // Handle POST /graphql
});

// Dispatch the incoming request
$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

switch ($routeInfo[0]) {

    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars    = $routeInfo[2];

        // Execute handler safely and return JSON response
        try {
            $response = $handler($vars);
            echo $response;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ]);
        }

        break;
}