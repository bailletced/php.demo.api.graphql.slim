<?php

use App\GraphQL\Schema\demo\QueryType;
use App\GraphQL\Schema\demo\TypeRegistry;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;

return (function () {
    /*
     * Schema Definition
     * @see http://webonyx.github.io/graphql-php/type-system/schema/
     */
    $config = SchemaConfig::create()
        ->setQuery(new QueryType())
        ->setTypeLoader(function($name) {
            $typeRegistry = TypeRegistry::getInstance();
            return $typeRegistry->byTypeName($name);
        })
        ->setDirectives([new Directive([
            'name' => 'track',
            'description' => 'Instruction to record usage of the field by client',
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION,
            ],
            'args' => [
                new FieldArgument([
                    'name' => 'details',
                    'type' => Type::string(),
                    'description' => 'String with additional details of field usage scenario',
                    'defaultValue' => ''
                ])
            ]
        ])]
        )
    ;

    return new Schema($config);
})();
