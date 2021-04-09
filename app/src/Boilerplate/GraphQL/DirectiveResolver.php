<?php


namespace App\Boilerplate\GraphQL;

use GuzzleHttp\Promise\Promise;

class DirectiveResolver
{
    public static $directives = [];

    private static $instance;

    /**
     * Singleton
     * @return DirectiveResolver
     */
    public static function getInstance() : DirectiveResolver
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function getDirectives($node) :array {
        $directives = array();
        foreach($node->directives as $directive) {
            $name = $directive->name->value;
            $args = array();
            foreach($directive->arguments as $arg) {
                $args[$arg->name->value] = $arg->value->value;
            }
            $directives[$name] = $args;
        }
        return $directives;
    }

    public static function bind($schema, $resolver) {
        $types = $schema->getTypeMap();
        foreach($types as $type) {
            if (!$type instanceof \GraphQL\Type\Definition\ObjectType) {
                continue;
            }
            foreach($type->getFields() as $field) {
                if (!is_object($field)) continue;
                if (!is_object($field->astNode)) continue;

                $schema_directives = static::getDirectives($field->astNode);

                $original =  $field->resolveFn;
                if (!$original) {
                    $original = $type->resolveFieldFn;
                }
                if (!$original) {
                    $original = function($value, $args, $ctx, $info) {
                        return \GraphQL\Executor\Executor::defaultFieldResolver($value, $args, $ctx, $info);
                    };
                }
                $field->resolveFn = function($value, $args, $ctx, $info) use ($schema_directives, $original, $resolver) {

                    $original = function($value, $args, $ctx, $info) use ($original) {
                        $p = new Promise();
                        $value = $original($value, $args, $ctx, $info);
                        $p->resolve($value);
                        return $p;
                    };

                    $p = $original;

                    foreach($schema_directives as $directive => $directive_args) {
                        $fn = $resolver($directive);
                        if($fn) {
                            $p = function($value, $args, $ctx, $info) use ($p, $fn) {
                                return call_user_func_array($fn, array($p, "ok", $value, $args, $ctx, $info));
                            };
                        }

                    }

                    return $p($value, $args, $ctx, $info)->wait();
                };
            }
        }
    }
}