<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 17:53
 */

namespace GraphQLYii\types;


use GraphQLYii\interfaces\IField;

abstract class FieldObject extends Field
{

    /** @var Field[] */
    private $args;

    abstract protected function resolve($root, $args);

}