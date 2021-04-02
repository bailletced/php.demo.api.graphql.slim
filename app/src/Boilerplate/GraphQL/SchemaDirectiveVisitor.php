<?php


namespace App\Boilerplate\GraphQL;


use App\GraphQL\Schema\demo\User\UserNamespaceQueryResolvers;

class SchemaDirectiveVisitor
{
    /** @var array */
    protected $directives = [];

    private static $instance;

    private $schema;

    /**
     * Singleton
     * @return SchemaDirectiveVisitor
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }


    public function setSchema($astSchema)
    {
        $this->schema = $astSchema;
    }

    public function visitFieldDefinition($fieldName)
    {
        Visitor::visit($this->schema, [
            'FieldDefinition' => [
                'enter' => function ($node, $key, $parent, $path, $ancestors) use ($fieldName) {
                    if ($node->name->value === $fieldName) {
                        $namespace = $parent[0]->name->loc->startToken->prev->prev->prev->value;
                        $args = $node->arguments;
                        return $this->getResolverResult($fieldName, $namespace, $args);
//                        var_dump($node->arguments[0]->name->value); //Récupérer des arguments de field
                    }
                },
//                if($node->name->value === "user") {
////                        var_dump("ozkeofkzeokf");
//                    return "Hello Jacob !";
//                }
//                'leave' => function ($node) {
//                }

            ],
//            'OBJECT_TYPE_DEFINITION ' => [
//            'enter' => function ($node, $key, $parent, $path, $ancestors) {
//                var_dump($node);
//                // enter the "Kind" node
//            },
//            'leave' => function ($node) {
//                // leave the "Kind" node
//            }
//        ]
        ]);
        return true;
    }

    private function getResolverResult($fieldName, $namespace, $args)
    {
        return UserNamespaceQueryResolvers::getJWT();
//        var_dump(call_user_func( "{$namespace}Resolvers::{$fieldName}" ));
//        return "ok";
    }
}

//Tentative (à l'arrache) de print le schéma
class Printer
{
    public static function doPrint($ast)
    {
        return Visitor::visit($ast, array('leave' => array('Name' => function ($node) {
            return $node->value . '';
        },'Variable' => function ($node) {
            return '$' . $node->name;
        }, 'Document' => function (DocumentNode $node) {
            return self::join($node->definitions, "\n\n") . "\n";
        }, 'OperationDefinition' => function (OperationDefinitionNode $node) {
            $op = $node->operation;
            $name = $node->name;
            $defs = Printer::manyList('(', $node->variableDefinitions, ', ', ')');
            $directives = self::join($node->directives, ' ');
            $selectionSet = $node->selectionSet;
            return !$name ? $selectionSet : self::join([$op, self::join([$name, $defs]), $directives, $selectionSet], ' ');
        }, 'VariableDefinition' => function (VariableDefinitionNode $node) {
            return self::join([$node->variable . ': ' . $node->type, $node->defaultValue], ' = ');
        }, 'SelectionSet' => function (SelectionSetNode $node) {
            return self::blockList($node->selections, ",\n");
        }, 'FieldDefinition' => function ($node) {
//            var_dump($node->alias);
            $r11 = self::join([$node->alias, $node->name], ': ');
            $r1 = self::join([$r11, self::manyList('(', $node->arguments, ', ', ')')]);
//            $r2 = self::join($node->directives, ' ');
//            return self::join([$r1, $r2, $node->selectionSet], ' ');
        }, 'Argument' => function (ArgumentNode $node) {
            return $node->name . ': ' . $node->value;
        }, 'FragmentSpread' => function (FragmentSpreadNode $node) {
            return self::join(['...' . $node->name, self::join($node->directives, '')], ' ');
        }, 'InlineFragment' => function (InlineFragmentNode $node) {
            return self::join(['... on', $node->typeCondition, self::join($node->directives, ' '), $node->selectionSet], ' ');
        }, 'FragmentDefinition' => function (FragmentDefinitionNode $node) {
            return self::join(['fragment', $node->name, 'on', $node->typeCondition, self::join($node->directives, ' '), $node->selectionSet], ' ');
        }, 'IntValue' => function (IntValueNode $node) {
            return $node->value;
        }, 'FloatValue' => function (FloatValueNode $node) {
            return $node->value;
        }, 'StringValue' => function (StringValueNode $node) {
            return json_encode($node->value);
        }, 'BooleanValue' => function (BooleanValueNode $node) {
            return $node->value ? 'true' : 'false';
        }, 'EnumValue' => function (EnumValueNode $node) {
            return $node->value;
        }, 'ListValue' => function (ListValueNode $node) {
            return '[' . self::join($node->values, ', ') . ']';
        }, Node::OBJECT => function (ObjectValueNode $node) {
            return '{' . self::join($node->fields, ', ') . '}';
        }, Node::OBJECT_FIELD => function (ObjectFieldNode $node) {
            return $node->name . ': ' . $node->value;
        }, Node::DIRECTIVE => function (DirectiveNode $node) {
            return self::join(['@' . $node->name, $node->value], ': ');
        }, Node::LIST_TYPE => function (ListTypeNode $node) {
            return '[' . $node->type . ']';
        }, Node::NON_NULL_TYPE => function (NonNullTypeNode $node) {
            return $node->type . '!';
        })));
    }
    public static function blockList($list, $separator)
    {
        return self::length($list) === 0 ? null : self::indent("{\n" . self::join($list, $separator)) . "\n}";
    }
    public static function indent($maybeString)
    {
        return $maybeString ? str_replace("\n", "\n  ", $maybeString) : '';
    }
    public static function manyList($start, $list, $separator, $end)
    {
        return self::length($list) === 0 ? null : $start . self::join($list, $separator) . $end;
    }
    public static function length($maybeArray)
    {
        return $maybeArray ? count($maybeArray) : 0;
    }
    public static function join($maybeArray, $separator = '')
    {
        return $maybeArray ? implode($separator, array_filter($maybeArray, function ($x) {
            return !!$x;
        })) : '';
    }
}