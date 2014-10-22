<?php

/**
* Copyright 2014 François Kooman <fkooman@tuxed.net>
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

namespace fkooman\Rest;

use fkooman\Http\Request;
use fkooman\Rest\Plugin\Bearer\BearerAuthentication;
use PHPUnit_Framework_TestCase;

class BearerAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testBearerAuthCorrect()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $request->setHeader("Authorization", "Bearer xyz");

        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm');
        $tokenIntrospection = $bearerAuth->execute($request);
        $this->assertEquals('foo', $tokenIntrospection->getSub());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testBearerAuthWrongUser()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $request->setHeader("Authorization", "Bearer xyz");

        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm');
        $bearerAuth->execute($request);
    }
}
