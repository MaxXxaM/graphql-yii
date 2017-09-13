<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:45
 */

namespace GraphQLYii\types;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ScalarType;

class Scalar extends ScalarType
{
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
     * @return Integer
     */
    abstract public static function get(): ScalarType;

}