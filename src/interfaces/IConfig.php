<?php
/**
 * Created by MaxXxaM.
 * Date: 12.09.17 at 18:52
 */

namespace GraphQLYii\interfaces;


use GraphQL\Type\SchemaConfig;

interface IConfig
{
    public function build(): SchemaConfig;
}