<?php

namespace GraphQLYii\Support;

use GraphQL\Type\Definition\InputObjectType as GQObject;
use GraphQLYii\GraphQL;

class InputObjectType extends GQObject
{

    /** @var GraphQL */
    protected $appInstance;

    protected static $instances = [];

    protected $inputObject = false;

    public function __construct($attributes = [])
    {
        $this->appInstance = Instance::getAppInstance();
        parent::__construct($attributes);
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
        } elseif (method_exists($this, $resolveMethod)) {
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
        return new GQObject($this->toArray());
    }



}
