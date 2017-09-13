<?php
/**
 * Created by MaxXxaM.
 * Date: 13.09.17 at 15:08
 */

namespace GraphQLYii;


use GraphQLYii\exceptions\UnexpectedObjectTypeException;
use GraphQLYii\interfaces\ILoader;
use GraphQLYii\types\Mutation;
use GraphQLYii\types\Query;
use GraphQLYii\types\Type;

class ObjectsLoader implements ILoader
{

    /** @var GraphQL */
    private $appInstance;

    /** @var string */
    private $rootPath;

    /** @var ObjectFile[] */
    private $files;

    /** @var Query[] */
    private $queries = [];

    /** @var Mutation[] */
    private $mutations = [];

    /** @var Type[] */
    private $types = [];

    public function __construct(GraphQL $appInstance)
    {
        $this->appInstance = $appInstance;
        $this->rootPath = $appInstance->getSourceDir();
    }

    /**
     * Load object from files and make Query, Mutation, Type objects
     * @param string|null $rootPath
     * @return ILoader
     * @throws \GraphQLYii\exceptions\ObjectFileNotFound
     * @throws \GraphQLYii\exceptions\CreateObjectException
     * @throws \GraphQLYii\exceptions\UnexpectedObjectTypeException
     */
    public function load(string $rootPath = null): ILoader{
        if ($rootPath !== null){
            $this->rootPath = $rootPath;
        }
        if ($this->rootPath !== null) {
            $this->files = $this->getObjectsByPath();
        }
        $this->getObjectsByConfig();
        $this->makeObjects();
        return $this;
    }

    /**
     * Search files by path
     * @param string $subPath
     * @return array
     */
    private function getObjectsByPath($subPath = ''): array{
        $this->files = [];
        $path = $this->rootPath . $subPath;
        if ($path !== ''){
            if (file_exists($path)) {
                $fp = opendir($path);
                while ($cvFile = readdir($fp)) {
                    $fileName = $path . '/' . $cvFile;
                    if (is_file($fileName)) {
                        if (preg_match('/^(.*)\.php$/', $cvFile, $matches)) {
                            $this->files[$matches[1]] = new ObjectFile(__NAMESPACE__ . str_replace('/', '\\', $subPath) . '\\' . $matches[1], $cvFile);
                        }
                    } elseif (!in_array($cvFile, ['.', '..'], true) && is_dir($fileName)) {
                        $this->getObjectsByPath($subPath . '/' . $cvFile);
                    }
                }
                closedir($fp);
            }
        }
        return $this->files;
    }

    /**
     * Load objects from config
     */
    private function getObjectsByConfig():self {
        $this->getQueriesByConfig();
        $this->getMutationsByConfig();
        $this->getTypesByConfig();
        return $this;
    }

    /**
     * Load objects Queries from config
     * @return array
     */
    private function getQueriesByConfig():array {
        return $this->getObjectsByKey('queries');
    }

    /**
     * Load objects Mutations from config
     * @return array
     */
    private function getMutationsByConfig():array {
        return $this->getObjectsByKey('mutations');
    }

    /**
     * Load objects Types from config
     * @return array
     */
    private function getTypesByConfig():array {
        return $this->getObjectsByKey('types');
    }

    /**
     * Load objects from config by key
     * @param string $key
     * @return array
     */
    private function getObjectsByKey(string $key):array {
        $objects = [];
        $configObjects = $this->appInstance->getObjects();
        if ($configObjects === null || !isset($configObjects[$key]) || !is_array($configObjects[$key])){
            return [];
        }
        foreach ($configObjects[$key] as $objectName){
            $objects[] = new ObjectFile($objectName);
        }
        $this->files = array_merge($this->files, $objects);
        return $objects;
    }

    /**
     * Make objects and set queries, mutations, types
     * @return ObjectsLoader
     * @throws \GraphQLYii\exceptions\ObjectFileNotFound
     * @throws \GraphQLYii\exceptions\CreateObjectException
     * @throws UnexpectedObjectTypeException
     */
    private function makeObjects():self {
        foreach ($this->files as $objectKey => $objectFile){
            $object = $objectFile->load();
            if ($object instanceof Query){
                $this->queries[] = $object;
            } elseif ($object instanceof Mutation){
                $this->mutations[] = $object;
            } elseif ($object instanceof Type){
                $this->types[] = $object;
            } else {
                throw new UnexpectedObjectTypeException();
            }
        }
        return $this;
    }

    /**
     * @return Query[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return Mutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

}