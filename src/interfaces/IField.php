<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 17:56
 */

namespace GraphQLYii\interfaces;

use GraphQL\Type\Definition\Type;

interface IField
{
    public function getDescription(): string;

    public function getType(): Type;
}