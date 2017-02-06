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

    public $namespace;

    public $graphqlDir;

    public $typesPath = '';

    public $queriesPath = '';

    public $mutationsPath = '';

    public $subscriptionPath = '';

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

        /** Собираем объекты из конфиг и из директорий */
        $this->types = array_unique( array_merge($this->types, $this->getListFiles($this->graphqlDir, $this->typesPath) ));
        $this->queries = array_unique( array_merge($this->queries, $this->getListFiles($this->graphqlDir, $this->queriesPath) ));
        $this->mutations = array_unique( array_merge($this->mutations, $this->getListFiles($this->graphqlDir, $this->mutationsPath) ));

        /** Собираем все типы */
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

    private function getListFiles($basepath, $subPath = ''){
        $files = [];
        $subNamespace = str_replace('/', '\\', $subPath);
        $path = $basepath . $subPath;
        if ($path !== ''){
            if (file_exists($path)) {
                $fp = opendir($path);
                while ($cvFile = readdir($fp)) {
                    $fileName = $path . '/' . $cvFile;
                    if (is_file($fileName)) {
                        if (preg_match('/(.*)\.php/', $cvFile, $matches)) {
                            $files[$matches[1]] = $this->namespace . $subNamespace . '\\' . $matches[1];
                        }
                    } elseif (!in_array($cvFile, ['.', '..'], true) && is_dir($fileName)) {
                        $files = array_merge($files, $this->getListFiles($basepath, $subPath . '/' . $cvFile));
                    }
                }
                closedir($fp);
            }
        }
        return $files;
    }

/*    private function getListFilesSortByFolder($basepath, $subPath = '', $nest = 0){
        $files = [];
        $subNamespace = str_replace('/', '\\', $subPath);
        $path = $basepath . $subPath;

        $objectName = '';
        foreach (array_reverse(explode('/', $subPath)) as $component){
            if ($component !== ''){
                $objectName .= ucfirst($component);
            }
        }

        if ($nest > 0) $nest++;

        if ($path !== ''){
            if (file_exists($path)) {
                $fp = opendir($path);
                while ($cvFile = readdir($fp)) {
                    $fileName = $path . '/' . $cvFile;
                    if (is_file($fileName)) {
                        if (preg_match('/(.*)\.php/', $cvFile, $matches)) {
                            $files[$objectName][] = $this->namespace . $subNamespace . '\\' . $matches[1];
                        }
                    } elseif (!in_array($cvFile, ['.', '..'], true) && is_dir($fileName)) {
                        $files = array_merge($files, $this->getListFilesSortByFolder($basepath, $subPath . '/' . $cvFile, $nest));
                    }
                }
                closedir($fp);
            }
        }
        return $files;
    }*/
    
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
