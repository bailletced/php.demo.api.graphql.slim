<?php


namespace App\Boilerplate\GraphQL;


use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class SchemaTypeMapDirectiveVisitor
{
    /** @var SchemaTypeMapDirectiveVisitor|null */
    private static $instance;

    /** @var array */
    private static $directives;

    /**
     * Singleton
     * @return SchemaTypeMapDirectiveVisitor|static|null
     */
    public static function getInstance () : SchemaTypeMapDirectiveVisitor
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     *
     */
    public static function getDirective() {}

    /**
     * @param Schema $schema
     */
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
                        SchemaTypeMapDirectiveVisitor::getInstance()::$directives[] = $fieldDirective::getDirective();
                        $fieldDirective::addArgumentDynamically($field);
                        $field->resolveFn = $fieldDirective::onVisitCallback($field->resolveFn);
                    }
                }
            }
        }
    }

}