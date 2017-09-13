<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 16:35
 */

namespace GraphQLYii\exceptions;


use Throwable;

class UnexpectedObjectTypeException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message === '' ? 'Unexpected object type' : $message, $code, $previous);
    }


}