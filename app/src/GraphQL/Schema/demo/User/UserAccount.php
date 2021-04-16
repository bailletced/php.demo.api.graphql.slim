<?php

namespace App\GraphQL\Schema\demo\User;

use App\Boilerplate\AppContext;
use App\Boilerplate\GraphQL\Type\Definition\ObjectType;
use App\GraphQL\Schema\demo\Directives\DateFormatDirective;
use App\GraphQL\Schema\demo\Directives\LowerCaseDirective;
use App\GraphQL\Schema\demo\Directives\AuthDirective;
use App\GraphQL\Schema\demo\Directives\UpperCaseDirective;
use App\GraphQL\Schema\demo\TypeRegistry;
use GraphQL\Type\Definition\ResolveInfo;

class UserAccount extends ObjectType
{
    public function __construct()
    {
        $types = TypeRegistry::getInstance();

        $config = [
            'name' => 'UserAccount',
            'description' => 'Our blog authors',
            'interfaces' => [
                $types->DataNodeInterface(),
            ],
            'schemaDirectives' => [
                [
                    "directive" => AuthDirective::class,
                    "params" => [
                        "access" => "ADMIN"
                    ],
                ]
            ],
            'fields' => function () use ($types) {
                return [
                    'id' => [
                        'type' => $types::nonNull($types::id()),
                        'description' => 'User\'s unique identifier',
                    ],
                    'login' => [
                        'type' => $types::string(),
                        'description' => 'User\'s login handler',
                    ],
                    'email' => [
                        'type' => $types->Email(),
                        'description' => 'User\'s email address',
                    ],
                    'firstName' => [
                        'type' => $types::string(),
                        'schemaDirectives' => [
                            [
                                "directive" => UpperCaseDirective::class,
                                "params" => [],
                            ],
                        ],
                    ],
                    'lastName' => [
                        'type' => $types::string(),
                    ],
                    'creationDate' => [
                        'type' => $types->string(),
                        'schemaDirectives' => [
                            [
                                "directive" => AuthDirective::class,
                                "params" => [
                                    "access" => "ADMIN",
                                ],
                            ],
                            [
                                "directive" => DateFormatDirective::class,
                                "params" => [],
                            ],
                        ]
                    ],
                    '_isMe' => [
                        'type' => $types::boolean(),
                        'description' => '`true` if your are authenticated as this user',
                        'resolve' => function ($objectValue, $args, AppContext $context, ResolveInfo $info) {
                            $authed = $context->getAuthenticatedUserAccount();
                            return $authed !== null && $objectValue !== null && $authed['id'] === $objectValue['id'];
                        },
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }

}
