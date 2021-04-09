<?php


namespace App\Boilerplate\GraphQL;


class ResolverController
{
    public static function ResolveField($value, $args, $ctx, $info) {
        $name = $info->fieldName;
        $parentType = $info->parentType->name;

        if($parentType !== "Query") {
            if (method_exists("App\GraphQL\Schema\demo\User\\${parentType}Resolver", $name)) {
                $value = call_user_func_array("App\GraphQL\Schema\demo\User\\${parentType}Resolver::${name}", [$value, $args, $ctx, $info]);
            } else {
                $value = $value[$name];
            }
        }
        else {
            $value = $value[$name];
        }
        return $value;
    }

    public static function TypeConfigDecorator($typeConfig) {

        $typeConfig['resolveField'] = function($value, $args, $ctx, $info) {
            return static::ResolveField($value, $args, $ctx, $info);
        };

        return $typeConfig;
    }

}