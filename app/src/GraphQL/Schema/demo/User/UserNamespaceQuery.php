<?php

namespace App\GraphQL\Schema\demo\User;

use App\Boilerplate\AppContext;
use App\Boilerplate\GraphQL\Exception\GenericGraphQlException;
use App\Boilerplate\GraphQL\Type\Definition\ObjectType;
use App\GraphQL\Schema\_common\Data\UserAccount\Repository\UserAccountRepository;
use App\GraphQL\Schema\demo\TypeRegistry;
use GraphQL\Type\Definition\ResolveInfo;

class UserNamespaceQuery extends ObjectType
{
    public function __construct()
    {
        $types = TypeRegistry::getInstance();

        $config = [
            'name' => 'UserNamespaceQuery',
            'description' => 'Interact with the APIs for user related purposes',
            'interfaces' => [],
            'fields' => [

                'getJWT' => [
                    'type' => $types->UserAccountJwt(),
                    'description' => 'Request a valid JWT token using the user\'s credentials. Returns null if unable to login for various reason',
                    'args' => [
                        [
                            'name' => 'login',
                            'type' => $types::string(),
                            'description' => 'User login handle',
                            'defaultValue' => null,
                        ],
                        [
                            'name' => 'password',
                            'type' => $types::string(),
                            'description' => 'User password (plain text)',
                            'defaultValue' => null,
                        ],
                    ],
                    'resolve' => [$this, 'resolveGetJWT'],
                ],

                '_me' => [
                    'type' => $types->UserAccount(),
                    'description' => 'Return the current authenticated user (Using `Authorization: Bearer` header or `token` cookie)',
                    'resolve' => [$this, 'resolveMe'],
                ],

            ],
        ];
        parent::__construct($config);
    }

    public function resolveMe($rootValue, $args, AppContext $context, ResolveInfo $info)
    {
        $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
        $nowUTC->setTimezone(new \DateTimeZone('Europe/Paris'));
        return [
            "firstName" => "toto",
            "creationDate" => $nowUTC,
        ];
//        return $context->getAuthenticatedUserAccount();
    }

    public function resolveGetJWT($rootValue, $args, $context, ResolveInfo $info)
    {
        // Identifying...
        $accounts = UserAccountRepository::getInstance()->fetchAll();
        $matchingAccounts = array_filter($accounts, function ($item) use ($args) {
            return (isset($item) && $item !== null && isset($item['login']) && $item['login'] === $args['login'] && isset($item['password']) && $item['password'] === hash('sha256', $args['password']));
        });

        if (count($matchingAccounts) > 0) {
            $account = reset($matchingAccounts);
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $date->add(new \DateInterval('P1Y')); // +1 year

            return [
                'user' => $account,
                'token' => base64_encode($account['id']), // don't do this in real life, use a proper JWE or JWS.
                'expire' => $date,
            ];
        }

        return null;
    }

}
