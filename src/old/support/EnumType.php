<?php

namespace GraphQLYii\Support;

use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\InterfaceType;

class EnumType extends Type
{

    protected $enumType = true;

    /**
     * @return array
     */
    public function values(){
        return [];
    }

    /**
     * Convert the Fluent instance to an array.
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getAttributes(), [
            'values' => $this->values()
        ]);
    }
}
