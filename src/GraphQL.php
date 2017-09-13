<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 15:53
 */

namespace GraphQLYii;

use GraphQLYii\interfaces\IRequest;
use GraphQLYii\interfaces\IResponse;
use yii\base\Component;
use GraphQL\GraphQL as ExtGraphQL;

/**
 * Class GraphQL
 * Yii component GraphQL
 *
 * @package GraphQLYii
 */
class GraphQL extends Component
{

    /** @var string */
    public $sourceDir;

    /** @var array[] */
    public $objects;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Execute request
     * @param IRequest $request
     * @return IResponse
     */
    public function execute(IRequest $request): IResponse{
        $schema = new Schema($this);
        $response = Response::make();
        try {
            $result = ExtGraphQL::executeQuery($schema->build(), $request->getQuery(), null, null, $request->getVariables());
            $response->setData($result);
        } catch (\Exception $e) {
            $response->addError($e);
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getSourceDir(): ?string
    {
        return $this->sourceDir;
    }

    /**
     * @return array[]
     */
    public function getObjects(): ?array
    {
        return $this->objects;
    }

}