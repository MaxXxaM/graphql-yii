<?php
/**
 * Created by MaxXxaM.
 * Date: 08.09.17 at 16:05
 */

namespace GraphQLYii\tests;

use GraphQLYii\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testRequestConstruct()
    {
        $request = new Request([
            'query' => 'query{}',
            'variables' => '{"a" : 10, "b" : 20}',
        ]);
        $this->assertNotEmpty($request->getQuery(), 'request not empty query');
        $this->assertNotEmpty($request->getVariables(), 'request not empty vars');
    }

    public function testRequestEmptyConstruct()
    {
        $request = new Request([]);
        $this->assertEmpty($request->getQuery(), 'request not empty query');
        $this->assertEmpty($request->getVariables(), 'request not empty vars');
    }

}
