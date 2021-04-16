<?php


namespace App\GraphQL\Schema\demo\Directives;


use App\Boilerplate\GraphQL\SchemaTypeDirectiveInterface;
use App\Boilerplate\GraphQL\SchemaTypeMapDirectiveVisitor;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class DateFormatDirective extends SchemaTypeMapDirectiveVisitor implements SchemaTypeDirectiveInterface
{
    /**
     * @return Directive
     */
    public static function getDirective(): Directive
    {
        return new Directive([
            'name' => 'format',
            'description' => 'Set text to lower case',
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION,
            ],
        ]);
    }

    /**
     * @param callable $resolveFn
     * @param array $params
     * @return callable
     */
    public static function onVisitCallback(callable $resolveFn, array $params) :callable {
        return function($value, $args, $context, $info) use ($resolveFn) {
            $resolverFnResult = $resolveFn($value, $args, $context, $info);
            if ($resolverFnResult) {
                return $resolverFnResult->format($args['format']);
            }
        };
    }

    /**
     * @param FieldDefinition $field
     */
    public static function addArgumentDynamically(FieldDefinition $field)
    {
        $field->args[] = new FieldArgument([
            "name" => "format",
            "type" => Type::string(),
            "defaultValue" => "d/m/Y",
            "description" => "Convert the field to specified date format",
        ]);
    }

}