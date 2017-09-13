<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation;

class UpdateExampleMutation extends Mutation
{
    
    protected $attributes = [
        'name' => 'updateExample'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }
    
    public function rules()
    {
        return [
            'test' => ['required']
        ];
    }

    public function args()
    {
        return [
            'test' => [
                'name' => 'test',
                'type' => Type::string()
            ],
            
            'test_with_rules' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => ['required']
            ],
            
            'test_with_rules_closure' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => function () {
                    return ['required'];
                }
            ]
        ];
    }

    public function resolve($root, $args)
    {
        return [
            'test' => array_get($args, 'test')
        ];
    }
}
