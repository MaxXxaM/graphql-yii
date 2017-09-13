<?php

namespace GraphQLYii\Support;

use Validator;
use GraphQLYii\Error\ValidationError;
use GraphQLYii\Support\Traits\ShouldValidate;

class Mutation extends Field
{
    use ShouldValidate;
}
