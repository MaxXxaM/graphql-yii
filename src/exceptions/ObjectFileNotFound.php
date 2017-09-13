<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 16:05
 */

namespace GraphQLYii\exceptions;

class ObjectFileNotFound extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message === '' ? 'Object file not found' : $message , $code, $previous);
    }

}