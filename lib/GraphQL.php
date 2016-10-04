<?php

namespace GraphQLYii;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Error;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InterfaceType;

use GraphQLYii\Error\ValidationError;
use yii\base\Component;
use yii\base\Exception;

class GraphQL extends Component{

    public $mutations = [];
    public $queries = [];
    public $types = [];
    public $default_schema;

    protected $schema;

    private $typesInstances = [];
    
    public function __construct()
    {
        
    }

    /**
     * Метод для создания схемы из типов, запросов и мутаций
     * @return Schema
     * @throws \Exception
     *
     */
    public function schema()
    {
        $this->typesInstances = [];

        // Если передана готовая схема подгружаем ее
        if (!empty($this->default_schema)){
            try{
                $schema = new $this->default_schema;
                $this->schema = $schema::build();
            }catch(Exception $error){
                throw new Exception('Incorrect schema:'. $this->default_schema);
            }
        }

        if($this->schema instanceof Schema)
        {
            return $this->schema;
        }

        // Собираем все типы
        foreach($this->types as $name => $type)
        {
            $this->type($name);
        }


        $queryType = $this->buildTypeFromFields($this->queries, [
            'name' => 'Query'
        ]);

        $mutationType = $this->buildTypeFromFields($this->mutations, [
            'name' => 'Mutation'
        ]);
        
        return new Schema([
            'query' => $queryType,
            'mutation' => $mutationType
        ]);
    }
    
    protected function buildTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach($fields as $key => $field)
        {
            if(is_string($field))
            {
                    $obj = new $field;
                    $typeFields[$key] = $obj->toArray();
            }
            else
            {
                $typeFields[$key] = $field;
            }
        }
        
        return new ObjectType(array_merge([
            'fields' => $typeFields
        ], $opts));
    }
    
    public function query($query, $params = [])
    {
        $executionResult = $this->queryAndReturnResult($query, $params);
        return [
            'data' => $executionResult->data,
            'errors' => array_map([$this, 'formatError'], $executionResult->errors)
        ];
    }
    
    public function queryAndReturnResult($query, $params = [])
    {
        $schema = $this->schema();
        $result = GraphQLBase::executeAndReturnResult($schema, $query, null, $params);
        return $result;
    }
    
    public function addMutation($mutator, $name)
    {
        $this->mutations[$name] = $mutator;
    }
    
    public function addQuery($query, $name)
    {
        $this->queries[$name] = $query;
    }
    
    public function addType($class, $name = null)
    {
        if(!$name)
        {
            $type = is_object($class) ? $class:app($class);
            $name = $type->name;    
        }
        
        $this->types[$name] = $class;
    }
    
    public function type($name, $fresh = false)
    {
        if(!isset($this->types[$name]))
        {
            throw new \Exception('Type '.$name.' not found.');
        }
        
        if(!$fresh && isset($this->typesInstances[$name]))
        {
            return $this->typesInstances[$name];
        }
        
        $type = $this->types[$name];
        if(!is_object($type))
        {
            $type = new $type;
        }
        
        $instance = $type->toType();
        $this->typesInstances[$name] = $instance;
        
        //Check if the object has interfaces
        if($type->interfaces)
        {
            InterfaceType::addImplementationToInterfaces($instance);
        }
        
        return $instance;
    }
    
    public function formatError(Error $e)
    {
        $error = [
            'message' => $e->getMessage()
        ];
        
        $locations = $e->getLocations();
        if(!empty($locations))
        {
            $error['locations'] = array_map(function($loc) { return $loc->toArray();}, $locations);
        }
        
        $previous = $e->getPrevious();
        if($previous && $previous instanceof ValidationError)
        {
            $error['validation'] = $previous->getValidatorMessages();
        }
        
        return $error;
    }
}
