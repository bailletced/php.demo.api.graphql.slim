<?php


namespace App\GraphQL\Schema\demo\Directives;


use App\Boilerplate\GraphQL\SchemaTypeDirectiveInterface;
use App\Boilerplate\GraphQL\SchemaTypeMapDirectiveVisitor;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;

class LowerCaseDirective extends SchemaTypeMapDirectiveVisitor implements SchemaTypeDirectiveInterface
{

    /**
     * @return Directive
     */
    public static function getDirective(): Directive
    {
        return new Directive([
            'name' => 'lower',
            'description' => 'Set text to lower case',
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION,
            ],
        ]);
    }

    /**
     * @param callable $resolveFn
     * @return callable
     */
    public static function onVisitCallback(callable $resolveFn) :callable {
        return function($value, $args, $context, $info) use ($resolveFn) {
            $resolverFnResult = $resolveFn($value, $args, $context, $info);
            return strtolower($resolverFnResult);
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