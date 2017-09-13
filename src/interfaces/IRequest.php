<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:08
 */

namespace GraphQLYii\interfaces;


interface IRequest
{
    public function getQuery():?string;

    public function getVariables():?string;

    public function setQuery(string $query):self;

    public function setVariables(string $variables):self;

}