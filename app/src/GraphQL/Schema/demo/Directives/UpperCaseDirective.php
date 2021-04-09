<?php


namespace App\GraphQL\Schema\demo\Directives;


use App\Boilerplate\GraphQL\SchemaTypeMapDirectiveVisitor;

class UpperCaseDirective extends SchemaTypeMapDirectiveVisitor
{
    public static $name = "upper";

    /**
     * @param callable $resolveFn
     * @return callable
     */
    public static function onVisitCallback(callable $resolveFn) :callable {
        return function($value, $args, $context, $info) use ($resolveFn) {
            $resolverFnResult = $resolveFn($value, $args, $context, $info);
            return strtoupper($resolverFnResult);
        };
    }
}