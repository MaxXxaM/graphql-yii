<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 18:28
 */

namespace GraphQLYii\types;


abstract class Object
{
    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var FieldObject[] */
    private $fields = [];

    public function __construct()
    {
        $this->name = $this->getName();
        $this->description = $this->description();
    }

    private function getName(){
        return $this->name();
    }

    protected function setName(string $name):self {
        $this->name = $name;
        return $this;
    }

    protected function setDescription(string $description):self {
        $this->description = $description;
        return $this;
    }

    protected function addField(FieldObject $field):self {
        $this->fields[] = $field;
        return $this;
    }

    protected function name(): string
    {
        return '';
    }

    protected function description(): string
    {
        return '';
    }

}