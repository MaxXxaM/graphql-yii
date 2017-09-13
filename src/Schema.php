<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:05
 */

namespace GraphQLYii;

use GraphQL\Type\Schema as ExtSchema;
use GraphQLYii\interfaces\IConfig;

class Schema
{

    /** @var GraphQL */
    private $appInstance;

    /** @var IConfig */
    private $config;

    public function __construct(GraphQL $appInstance, IConfig $config = null)
    {
        $this->appInstance = $appInstance;
        if ($config === null){
            $config = new ConfigSchema($appInstance);
        }
        $this->config = $config;
    }

    /**
     * Build schema
     */
    public function build(): ExtSchema {
        return new ExtSchema($this->config->build());
    }

}