<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:45
 */

namespace GraphQLYii\types;


use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\Type;

class String extends BaseType
{

    /**
     * @return Integer
     */
    public static function type(): Type
    {
        return Type::string();
    }

}