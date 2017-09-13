<?php
/**
 * Created by MaxXxaM.
 * Date: 07.09.17 at 16:08
 */

namespace GraphQLYii;

use GraphQLYii\interfaces\IRequest;

/**
 * Class Request
 * The class is used to store graphql query params
 * @package GraphQLYii
 */
class Request implements IRequest
{
    /** @var string */
    private $query;

    /** @var string */
    private $variables;

    public function __construct(array $request = [])
    {
        isset($request['query']) ? $this->setQuery($request['query']) : null;
        isset($request['variables']) ? $this->setVariables($request['variables']) : null;
    }

    /**
     * Get graphql query
     * @return string
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Get graphql variables
     * @return string
     */
    public function getVariables(): ?string
    {
        return $this->variables;
    }

    /**
     * Set graphql query
     * @param string $query
     * @return IRequest
     */
    public function setQuery(string $query): IRequest {
        $this->query = $query;
        return $this;
    }

    /**
     * Set graphql variables
     * @param string $variables
     * @return IRequest
     */
    public function setVariables(string $variables): IRequest {
        $this->variables = $variables;
        return $this;
    }
}