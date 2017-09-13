<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 16:11
 */

namespace GraphQLYii\exceptions;


use Throwable;

class CreateObjectException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message === '' ? 'Create object failed' : $message , $code, $previous);
    }


}