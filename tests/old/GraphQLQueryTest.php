<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;

class GraphQLQueryTest extends TestCase
{    
    /**
     * Test query
     *
     * @test
     */
    public function testQueryAndReturnResult()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertObjectHasAttribute('data', $result);
        
        $this->assertEquals($result->data, [
            'examples' => $this->data
        ]);
    }
    
    /**
     * Test query methods
     *
     * @test
     */
    public function testQuery()
    {
        $resultArray = GraphQL::query($this->queries['examples']);
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertInternalType('array', $resultArray);
        $this->assertArrayHasKey('data', $resultArray);
        $this->assertEquals($resultArray['data'], $result->data);
    }
    
    /**
     * Test query with params
     *
     * @test
     */
    public function testQueryAndReturnResultWithParams()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithParams'], [
            'index' => 0
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }
    
    /**
     * Test query with initial root
     *
     * @test
     */
    public function testQueryAndReturnResultWithRoot()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithRoot'], null, [
            'root' => [
                'test' => 'root'
            ]
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesRoot' => [
                'test' => 'root'
            ]
        ]);
    }
    
    /**
     * Test query with context
     *
     * @test
     */
    public function testQueryAndReturnResultWithContext()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithContext'], null, [
            'context' => [
                'test' => 'context'
            ]
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesContext' => [
                'test' => 'context'
            ]
        ]);
    }
    
    /**
     * Test query with schema
     *
     * @test
     */
    public function testQueryAndReturnResultWithSchema()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'], null, [
            'schema' => [
                'query' => [
                    'examplesCustom' => ExamplesQuery::class
                ]
            ]
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesCustom' => $this->data
        ]);
    }
    
    /**
     * Test query with error
     *
     * @test
     */
    public function testQueryWithError()
    {
        $result = GraphQL::query($this->queries['examplesWithError']);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNull($result['data']);
        $this->assertCount(1, $result['errors']);
        $this->assertArrayHasKey('message', $result['errors'][0]);
        $this->assertArrayHasKey('locations', $result['errors'][0]);
    }
    
    /**
     * Test query with validation error
     *
     * @test
     */
    public function testQueryWithValidationError()
    {
        $result = GraphQL::query($this->queries['examplesWithValidation']);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('validation', $result['errors'][0]);
        $this->assertTrue($result['errors'][0]['validation']->has('index'));
    }
    
    /**
     * Test query with validation without error
     *
     * @test
     */
    public function testQueryWithValidation()
    {
        $result = GraphQL::query($this->queries['examplesWithValidation'], [
            'index' => 0
        ]);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }
}
