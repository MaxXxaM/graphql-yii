<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 17:53
 */

namespace GraphQLYii\types;


use GraphQLYii\interfaces\IField;

abstract class Field implements IField
{
    /** @var string */
    private $key;

    /** @var string */
    private $description;

    /** @var BaseType */
    private $type;

    public function __construct()
    {
        $this->type = $this->type();
        $this->key = $this->key();
        $this->description = $this->description();
    }

    abstract protected function type(): BaseType;

    protected function description(): string
    {
        return '';
    }

    abstract protected function key(): string;

}