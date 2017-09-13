<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:23
 */

namespace GraphQLYii;


use GraphQLYii\interfaces\IResponse;

class Response implements IResponse
{

    private $errors = [];

    public function __construct()
    {

    }

    public static function make():self{
        return new self();
    }

    public function addError(\Exception $e): IResponse
    {
        $this->errors[] = $e;
        return $this;
    }

    public function setData($result)
    {

    }

}