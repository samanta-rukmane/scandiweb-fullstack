<?php

namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use App\GraphQL\Types\CategoryType;
use App\GraphQL\Types\OrderType;
use App\GraphQL\Types\ProductType;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use RuntimeException;
use Throwable;

class GraphQL
{
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;
    private OrderRepository $orderRepository;

    private ObjectType $productType;
    private ObjectType $categoryType;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        OrderRepository $orderRepository
    ) {
        $this->productRepository  = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->orderRepository    = $orderRepository;
    }

    // Handle GraphQL POST request
    public function handle(): string
    {
        try {
            $this->initTypes();
            $schema = $this->buildSchema();
            $input  = $this->getInput();

            if (empty($input['query'])) {
                throw new RuntimeException('No query provided');
            }

            $result = GraphQLBase::executeQuery(
            $schema,
            $input['query'],
            null,
            null,
            $input['variables'] ?? null
        );

        $output = $result->toArray(\GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\DebugFlag::INCLUDE_TRACE);
        } catch (Throwable $e) {
            // Return safe JSON for production
            http_response_code(500);
            return json_encode([
                'error' => true,
                'message' => 'Internal Server Error'
            ]);
        }

        header('Content-Type: application/json; charset=UTF-8');

        return json_encode($output);
    }

    // Build GraphQL schema with queries and mutations
    private function buildSchema(): Schema
    {
        // Query type
        $queryType = new ObjectType([
            'name'   => 'Query',
            'fields' => [
                'categories' => [
                    'type'    => Type::listOf($this->categoryType),
                    'resolve' => fn() => $this->categoryRepository->findAll()
                ],
                'products' => [
                    'type'    => Type::listOf($this->productType),
                    'resolve' => fn() => $this->productRepository->findAll()
                ],
                'productsByCategory' => [
                    'type' => Type::listOf($this->productType),
                    'args' => ['category' => Type::nonNull(Type::string())],
                    'resolve' => fn($root, $args) => 
                        $this->productRepository->findByCategory($args['category'])
                ],
                'product' => [
                    'type' => $this->productType,
                    'args' => ['id' => Type::nonNull(Type::string())],
                    'resolve' => fn($root, $args) =>
                        $this->productRepository->findById($args['id'])
                ],
            ],
        ]);

        // Input type for order items
        $orderItemInputType = new InputObjectType([
            'name'   => 'OrderItemInput',
            'fields' => [
                'productId'  => Type::nonNull(Type::string()),
                'quantity'   => Type::nonNull(Type::int()),
                'attributes' => Type::listOf(new InputObjectType([
                    'name'   => 'AttributeInput',
                    'fields' => [
                        'name'  => Type::nonNull(Type::string()),
                        'value' => Type::nonNull(Type::string())
                    ]
                ]))
            ]
        ]);

        // Mutation type
        $mutationType = new ObjectType([
            'name'   => 'Mutation',
            'fields' => [
                'createOrder' => [
                    'type' => OrderType::getInstance(),
                    'args' => [
                        'items' => Type::nonNull(Type::listOf(Type::nonNull($orderItemInputType))),
                        'total' => Type::nonNull(Type::float())
                    ],
                    'resolve' => fn($root, $args) => $this->orderRepository->create($args['items'], $args['total'])
                ]
            ]
        ]);

        return new Schema((new SchemaConfig())->setQuery($queryType)->setMutation($mutationType));
    }

    // Get POSTed JSON input
    private function getInput(): array
    {
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            throw new RuntimeException('Failed to read input');
        }

        return json_decode($rawInput, true) ?? [];
    }

    // Initialize GraphQL types
    private function initTypes(): void
    {
        $this->productType  = ProductType::getInstance();
        $this->categoryType = new CategoryType();
    }
}