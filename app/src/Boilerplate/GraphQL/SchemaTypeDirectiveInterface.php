<?php


namespace App\Boilerplate\GraphQL;


use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;

interface SchemaTypeDirectiveInterface
{
    /**
     * @param callable $resolveFn
     * @return mixed
     */
    public static function onVisitCallback(callable $resolveFn);

    /**
     * @return Directive
     */
    public static function getDirective(): Directive;


    public static function addArgumentDynamically(FieldDefinition $field);

}