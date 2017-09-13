<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:39
 */

namespace GraphQLYii\types;

use GraphQL\Type\Definition\Type;

abstract class BaseType
{

    abstract public static function type(): Type;

}