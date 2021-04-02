<?php


namespace App\GraphQL\Schema\demo\Directives;


use App\Boilerplate\GraphQL\DirectiveResolver;

class UpperCaseDirective extends DirectiveResolver
{
    public function __construct()
    {
        self::$directives['upper'] = self::execute();
    }
//
//    public function visitFieldDefinition($field)
//    {
//        $result = parent::visitFieldDefinition($field);
//        return strtoupper($result);
//    }

    public static function execute() :callable {
        return function($next, $directiveArgs, $value, $args, $context, $info) {
            return $next($value, $args, $context, $info)->then(function ($str) {
                return strtoupper($str);
            });
        };
    }
}