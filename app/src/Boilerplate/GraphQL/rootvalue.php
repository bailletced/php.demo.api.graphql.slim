<?php
return [
    'user' => function($rootValue, $args, $context) {
        return "I am the user resolver";
    },
    'hello' => 'Hello world !',
];