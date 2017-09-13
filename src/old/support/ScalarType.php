<?php

namespace GraphQLYii\Support;

use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\InterfaceType;

class ScalarType extends Type
{

    protected $scalarType = true;

    /**
     * @param $value
     * @return mixed
     */
    public function serialize($value){
        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value){
        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function parseLiteral($value){
        return $value;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getAttributes(), [
            'serialize' => [$this, 'serialize'],
            'parseValue' => [$this, 'parseValue'],
            'parseLiteral' => [$this, 'parseLiteral'],
        ]);
    }
}
