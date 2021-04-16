<?php


namespace App\GraphQL\Schema\demo\Directives;


use App\Boilerplate\GraphQL\SchemaTypeDirectiveInterface;
use App\Boilerplate\GraphQL\SchemaTypeMapDirectiveVisitor;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;

class AuthDirective  extends SchemaTypeMapDirectiveVisitor implements SchemaTypeDirectiveInterface
{
    /**
     * @return Directive
     */
    public static function getDirective(): Directive
    {
        return new Directive([
            'name' => 'auth',
            'description' => 'Check user permission',
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION
            ],
        ]);
    }

    /**
     * @param callable $resolveFn
     * @param array $params
     * @return callable
     */
    public static function onVisitCallback(callable $resolveFn, array $params) :callable {
        return function($value, $args, $context, $info) use ($resolveFn, $params) {
            if (isset($params["access"]) && $params["access"] === $value["profile"]) {
                return $value[$info->fieldName];
            }
            else {
                return null;
            }
        };
    }

    /**
     * @param FieldDefinition $field
     */
    public static function addArgumentDynamically(FieldDefinition $field)
    {
        // TODO: Implement addArgumentDynamically() method.
    }
}