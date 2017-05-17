<?php

namespace GraphQLYii\Support;

class Query extends Field
{

    public static function query(): array
    {
        $array = explode('\\', static::class);
        $className = $array[count($array) - 1];
        return Instance::getAppInstance()->queryAsField($className);
    }
    
}
