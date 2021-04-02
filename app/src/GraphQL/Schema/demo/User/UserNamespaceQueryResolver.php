<?php


namespace App\GraphQL\Schema\demo\User;


class UserNamespaceQueryResolver
{
    public static function getJWT($value, $args, $ctx, $info, $resolver) {
        return "I am the getJWT Resolver";
    }

}