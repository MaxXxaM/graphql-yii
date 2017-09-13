<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 16:20
 */

namespace GraphQLYii\interfaces;


use GraphQLYii\GraphQL;

interface ILoader
{
    public function load(string $rootPath):self;

    public function getQueries(): array;

    public function getMutations(): array;

    public function getTypes(): array;

}