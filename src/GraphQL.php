<?php namespace GraphQLYii;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Schema;
use GraphQL\Error;

use GraphQLYii\Support\InputObjectType;
use GraphQL\Type\Definition\ObjectType;

use GraphQLYii\Error\ValidationError;

use GraphQLYii\Exception\TypeNotFound;
use GraphQLYii\Exception\SchemaNotFound;

use GraphQLYii\Events\SchemaAdded;
use GraphQLYii\Events\TypeAdded;
use yii\base\Component;

class GraphQL extends Component
{
    protected $app;
    

    protected $typesInstances = [];

    /** @var array Доступные схемы */
    protected $schemas = [];

    /** @var string имя схемы */
    public $schema;

    public $namespace;
    public $graphqlDir;
    public $typesPath = '';
    public $queriesPath = '';
    public $mutationsPath = '';
    public $subscriptionPath = '';

    protected $types = [];
    protected $queries = [];
    protected $mutations = [];

    private $queryInstance;
    private $mutationInstance;

    public function schema($schema = null)
    {
        if ($schema instanceof Schema) {
            return $schema;
        }
        
        $this->clearTypeInstances();

        /** Если передана готовая схема подгружаем ее */
        if (!empty($this->schema)){
            try{
                $schema = new $this->schema;
                $this->schema = $schema::build();
            }catch(\Exception $error){
                throw new SchemaNotFound('Type ' . $this->schema . ' not found.');
            }
        }


        /** Собираем объекты из конфиг и из директорий */
        $this->types = array_unique( array_merge($this->types, $this->getListFiles($this->graphqlDir, $this->typesPath) ));
        $this->queries = array_unique( array_merge($this->queries, $this->getListFiles($this->graphqlDir, $this->queriesPath) ));
        $this->mutations = array_unique( array_merge($this->mutations, $this->getListFiles($this->graphqlDir, $this->mutationsPath) ));

        /** Собираем все типы */
        $types = [];
        foreach ($this->types as $name => $type) {
            $types[] = $this->type($name);
        }

        $this->queryInstance = $this->objectType($this->queries, [
            'name' => 'Query'
        ]);

        $this->mutationInstance = $this->objectType($this->mutations, [
            'name' => 'Mutation'
        ]);

        return new Schema([
            'query' => $this->queryInstance,
            'mutation' => $this->mutationInstance,
            'types' => $types
        ]);
    }

    /**
     * Получение объектов из директории
     * @param $basepath
     * @param string $subPath
     * @return array
     */
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
                            $files[str_replace(['Query', 'Mutation'], '', $matches[1])] = $this->namespace . $subNamespace . '\\' . $matches[1];
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
    
    public function type($name, $fresh = false)
    {
        if (!isset($this->types[$name])) {
            throw new TypeNotFound('Type '.$name.' not found.');
        }
        
        if (!$fresh && isset($this->typesInstances[$name])) {
            return $this->typesInstances[$name];
        }
        
        $class = $this->types[$name];
        $type = $this->objectType($class, [
            'name' => $name
        ]);
        $this->typesInstances[$name] = $type;
        
        return $type;
    }

    /**
     * Uses if Query is field of anyone types
     * @param $name
     * @param bool $fresh
     * @return array
     */
    public function queryAsField($name)
    {
        if (!empty($this->queryInstance)) {
            $queryInstance = $this->queryInstance->getField($name);
            return [
                'type' => $queryInstance->getType(),
                'args' => $queryInstance->config['args'],
                'resolve' => $queryInstance->resolveFn
            ];
        }
    }
    
    public function objectType($type, $opts = [])
    {
        // If it's already an ObjectType, just update properties and return it.
        // If it's an array, assume it's an array of fields and build ObjectType
        // from it. Otherwise, build it from a string or an instance.
        $objectType = null;
        if ($type instanceof ObjectType) {
            $objectType = $type;
            foreach ($opts as $key => $value) {
                if (property_exists($objectType, $key)) {
                    $objectType->{$key} = $value;
                }
                if (isset($objectType->config[$key])) {
                    $objectType->config[$key] = $value;
                }
            }
        } elseif (is_array($type)) {
            $objectType = $this->buildObjectTypeFromFields($type, $opts);
        } else {
            $objectType = $this->buildObjectTypeFromClass($type, $opts);
        }
        
        return $objectType;
    }
    
    public function query($query, $params = [], $opts = [])
    {
        $result = $this->queryAndReturnResult($query, $params, $opts);
        
        if (!empty($result->errors)) {
            return [
                'data' => $result->data,
                'errors' => array_map([$this, 'formatError'], $result->errors)
            ];
        } else {
            return [
                'data' => $result->data
            ];
        }
    }
    
    public function queryAndReturnResult($query, $params = [], $opts = [])
    {
        $root = array_get($opts, 'root', null);
        $context = array_get($opts, 'context', null);
        $schemaName = array_get($opts, 'schema', null);
        $operationName = array_get($opts, 'operationName', null);
        
        $schema = $this->schema($schemaName);
        
        $result = GraphQLBase::executeAndReturnResult($schema, $query, $root, $context, $params, $operationName);
        
        return $result;
    }
    
    public function addTypes($types)
    {
        foreach ($types as $name => $type) {
            $this->addType($type, is_numeric($name) ? null:$name);
        }
    }
    
    public function addType($class, $name = null)
    {
        $name = $this->getTypeName($class, $name);
        $this->types[$name] = $class;
    }
    
    public function addSchema($name, $schema)
    {
        $this->schemas[$name] = $schema;
    }
    
    public function clearType($name)
    {
        if (isset($this->types[$name])) {
            unset($this->types[$name]);
        }
    }
    
    public function clearSchema($name)
    {
        if (isset($this->schemas[$name])) {
            unset($this->schemas[$name]);
        }
    }
    
    public function clearTypes()
    {
        $this->types = [];
    }
    
    public function clearSchemas()
    {
        $this->schemas = [];
    }
    
    public function getTypes()
    {
        return $this->types;
    }
    
    public function getSchemas()
    {
        return $this->schemas;
    }

    protected function clearTypeInstances()
    {
        $this->typesInstances = [];
    }
    
    protected function buildObjectTypeFromClass($type, $opts = [])
    {
        if (!is_object($type)) {
            $type = new $type($opts);
        }
        
        foreach ($opts as $key => $value) {
            $type->{$key} = $value;
        }

        return $type->toType();
    }
    
    protected function buildObjectTypeFromFields($fields, $opts = [])
    {
        $typeFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                try {
                    $field = new $field;
                } catch (\Error $e){
                    break;
                }
                $name = is_numeric($name) ? $field->name:$name;
                $field->name = $name;
                $field = $field->toArray();
            } else {
                $name = is_numeric($name) ? $field['name']:$name;
                $field['name'] = $name;
            }
            $typeFields[$name] = $field;
        }
        
        return new ObjectType(array_merge([
            'fields' => $typeFields
        ], $opts));
    }
    
    protected function getTypeName($class, $name = null)
    {
        if ($name) {
            return $name;
        }
        
        $type = is_object($class) ? $class : new $class;
        return $type->name;
    }
    
    public static function formatError(Error $e)
    {
        $error = [
            'message' => $e->getMessage()
        ];

        $locations = $e->getLocations();
        if (!empty($locations)) {
            $error['locations'] = array_map(function ($loc) {
                return $loc->toArray();
            }, $locations);
        }

        $previous = $e->getPrevious();
        if (!empty($previous->statusCode)) {
            $error['statusCode'] = $previous->statusCode;
        }

        $previous = $e->getPrevious();
        if (!empty($previous)) {
            $error['file'] = $previous->getFile();
            $error['line'] = $previous->getLine();
        }

        $stackTrace = $e->getTrace();
        if (!empty($stackTrace)) {
            foreach ($stackTrace as $key => $item) {
                $newItem = [];
                $newItem['file'] = isset($item['file']) ? $item['file'] : '';
                $newItem['line'] = isset($item['line']) ? $item['line'] : '';
                $newItem['function'] = isset($item['function']) ? $item['function'] : '';
                $error['trace'][] = $newItem;
            }
        }

        if ($previous && $previous instanceof ValidationError) {
            $error['validation'] = $previous->getValidatorMessages();
        }

        return $error;
    }
}
