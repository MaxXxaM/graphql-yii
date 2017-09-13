<?php
/**
 * Created by MaxXxaM.
 * Date: 12.09.17 at 18:36
 */

namespace GraphQLYii;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\SchemaConfig;
use GraphQLYii\interfaces\IConfig;

class ConfigSchema implements IConfig
{

    /** @var GraphQL */
    private $appInstance;

    /** @var ObjectsLoader */
    private $loader;

    public function __construct(GraphQL $appInstance)
    {
        $this->appInstance = $appInstance;
        $this->loader = new ObjectsLoader($appInstance);
    }

    public function build():SchemaConfig{
        $this->loader->load();
        return SchemaConfig::create()
            ->setQuery($this->getBaseQuery())
            ->setMutation($this->getBaseMutation());
    }

    /**
     * Return base query for Schema
     * @return ObjectType
     */
    private function getBaseQuery(): ObjectType {
        return new ObjectType([
            'name' => 'Query',
            'fields' => []
        ]);
    }

    /**
     * Returned base Mutation for Schema
     * @return ObjectType
     */
    private function getBaseMutation(): ObjectType{
        return new ObjectType([
            'name' => 'Mutation',
            'fields' => []
        ]);
    }

}