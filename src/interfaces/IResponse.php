<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:08
 */

namespace GraphQLYii\interfaces;


interface IResponse
{

    public function addError(\Exception $e): self;

}