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
    public $directives;

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
                if (!isset($type->resolveFieldFn)) {
                    static::overrideResolver($type);
                }
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
                    static::overrideResolver($field);
                }
            }
        }
    }

    /**
     * @param $kind
     */
    private static function overrideResolver($kind) {
        foreach ($kind->config['schemaDirectives'] as $directiveArray) {
            $directive = $directiveArray["directive"];
            $directiveParams = $directiveArray["params"];
            SchemaTypeMapDirectiveVisitor::getInstance()->directives[] = $directive::getDirective();
            if ($kind instanceof FieldDefinition) {
                $directive::addArgumentDynamically($kind);
                $kind->resolveFn = $directive::onVisitCallback($kind->resolveFn, $directiveParams);
            }
            else if ($kind instanceof Type) {
                $kind->resolveFieldFn = $directive::onVisitCallback(function() {}, $directiveParams);
            }
        }
    }
}