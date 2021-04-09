<?php


namespace App\Boilerplate\GraphQL;


use GraphQL\Type\Schema;

class SchemaTypeMapDirectiveVisitor
{
    /**
     * @param callable $resolveFn
     */
    static function onVisitCallback(callable $resolveFn) {}

    public static function visit(Schema $schema) {
        $types = $schema->getTypeMap();

        foreach($types as $type) {
            if (!$type instanceof \GraphQL\Type\Definition\ObjectType) {
                continue;
            }

            if (isset($type->config['schemaDirectives'])) {
                //TODO : replace resolveFN for type
            }

            foreach ($type->getFields() as $field) {
                if (!is_object($field)) continue;

                if (!isset($field->resolveFn)) {
                    $field->resolveFn = $type->resolveFieldFn; //We get the default resolver of the type
                    if (!isset($field->resolveFn)) {
                        $field->resolveFn = function ($value, $args, $ctx, $info) {
                            return \GraphQL\Executor\Executor::defaultFieldResolver($value, $args, $ctx, $info); //We get the default resolver provided by the library
                        };
                    }
                }

                //We are here overriding the field resolver
                if (isset($field->config['schemaDirectives'])) {
                    foreach ($field->config['schemaDirectives'] as $fieldDirective) {
                        $field->resolveFn = $fieldDirective::onVisitCallback($field->resolveFn);
                    }
                }
            }
        }
    }

}