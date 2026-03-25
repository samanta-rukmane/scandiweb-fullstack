<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class AttributeItemType extends ObjectType
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct([
            'name'   => 'AttributeItem',
            'fields' => [
                'value'        => Type::string(),
                'displayValue' => Type::string(),
            ],
        ]);
    }
}