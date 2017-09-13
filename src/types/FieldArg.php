<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 17:53
 */

namespace GraphQLYii\types;


use GraphQLYii\interfaces\IField;

abstract class FieldArg extends Field
{

    /** @var mixed */
    private $default;

    protected function setDefault(): self{
        $this->default = $this->defaultValue();
        return $this;
    }

    abstract protected function defaultValue();

}