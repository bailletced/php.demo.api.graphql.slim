<?php


namespace App\Boilerplate\GraphQL;


use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;

interface SchemaTypeDirectiveInterface
{

    /**
     * @param callable $resolveFn
     * @param array $params
     * @return mixed
     */
    public static function onVisitCallback(callable $resolveFn, array $params);

    /**
     * @return Directive
     */
    public static function getDirective(): Directive;


    /**
     * @param FieldDefinition $field
     * @return mixed
     */
    public static function addArgumentDynamically(FieldDefinition $field);

}