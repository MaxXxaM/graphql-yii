<?php
declare(strict_types=1);
/**
 * Created by MaxXxaM.
 * Date: 08.09.17 at 12:05
 */

namespace GraphQLYii\tests;

use GraphQLYii\GraphQL;
use GraphQLYii\Request;
use PHPUnit\Framework\TestCase;

final class GraphQLTest extends TestCase
{
    public function testGraphqlComponent(): GraphQL
    {
        $graphQL = new GraphQL();
        $this->assertInstanceOf(GraphQL::class, $graphQL);
        return $graphQL;
    }

    /**
     * @depends testGraphqlComponent
     * @param GraphQL $graphQL
     */
    public function testExecuteRequest(GraphQL $graphQL)
    {
        $this->markTestSkipped();
        /*$request = new Request();
        $request->setQuery('query{}');
        $graphQL->execute($request);*/
    }

    public function providerQueries(){
        return [
            'query{}',
            'mutation{}'
        ];
    }

}