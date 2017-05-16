<?php

namespace GraphQLYii\Support;

use GraphQLYii\GraphQL;
use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;

class Type extends Fluent
{
    /** @var GraphQL */
    protected $appInstance;

    protected static $instances = [];
    
    protected $inputObject = false;
    protected $scalarType = false;
    protected $enumType = false;

    public function setAppInstance($instance){
        $this->appInstance = $instance;
    }
    
    public function attributes()
    {
        return [];
    }
    
    public function fields()
    {
        return [];
    }
    
    public function interfaces()
    {
        return [];
    }
    
    protected function getFieldResolver($name, $field)
    {
        $resolveMethod = 'resolve'.studly_case($name).'Field';
        if (isset($field['resolve'])) {
            return $field['resolve'];
        }

        if (method_exists($this, $resolveMethod)) {
            $resolver = array($this, $resolveMethod);
            return function () use ($resolver) {
                $args = func_get_args();
                return call_user_func_array($resolver, $args);
            };
        }

        return null;
    }
    
    public function getFields()
    {
        $fields = $this->fields();
        $allFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                $field = app($field);
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            } else {
                $resolver = $this->getFieldResolver($name, $field);
                if ($resolver) {
                    $field['resolve'] = $resolver;
                }
                $allFields[$name] = $field;
            }
        }
        
        return $allFields;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        $interfaces = $this->interfaces();
        
        $attributes = array_merge($this->attributes, [
            'fields' => function () {
                return $this->getFields();
            }
        ], $attributes);
        
        if (count($interfaces)) {
            $attributes['interfaces'] = $interfaces;
        }
        
        return $attributes;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }
    
    public function toType()
    {
        if ($this->inputObject) {
            return new InputObjectType($this->toArray());
        }

        if ($this->scalarType) {
            return new CustomScalarType($this->toArray());
        }

        if ($this->enumType) {
            return new EnumType($this->toArray());
        }

        return new ObjectType($this->toArray());
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]) ? $attributes[$key]:null;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$key]);
    }
}
