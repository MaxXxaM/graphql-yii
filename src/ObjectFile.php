<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 16:03
 */

namespace GraphQLYii;


use GraphQLYii\exceptions\CreateObjectException;
use GraphQLYii\exceptions\ObjectFileNotFound;

class ObjectFile
{
    /** @var string */
    private $path;

    /** @var string */
    private $className;

    public function __construct(string $className, string $path = null)
    {
        $this->path = $path;
        $this->className = $className;
    }

    /**
     * Load object and return it
     * @return mixed
     * @throws CreateObjectException
     * @throws ObjectFileNotFound
     */
    public function load(){
        if (!file_exists($this->path)){
            throw new ObjectFileNotFound();
        }
        try{
            return new $this->className();
        } catch (\Exception $exception){
            throw new CreateObjectException();
        }
    }

}