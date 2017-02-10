<?php namespace GraphQLYii\Events;

class TypeAdded
{
    public $type;
    public $name;
    
    public function __construct($type, $name)
    {
        $this->type = $type;
        $this->name = $name;
    }
}
