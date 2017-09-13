<?php

namespace GraphQLYii\Support;

use GraphQLYii\GraphQL;

class Instance
{
    /** @var GraphQL */
    static protected $appInstance;

    static public function setAppInstance($instance){
        self::$appInstance = $instance;
    }

    static public function getAppInstance(): GraphQL
    {
        return self::$appInstance;
    }
}
