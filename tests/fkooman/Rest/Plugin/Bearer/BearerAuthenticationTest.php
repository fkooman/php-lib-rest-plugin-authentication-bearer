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
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit_Framework_TestCase;

class BearerAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testBearerValidToken()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $request->setHeader("Authorization", "Bearer xyz");

        $guzzleClient = new Client();
        $mock = new Mock([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                Stream::factory(json_encode(
                    [
                        "active" => true,
                        "sub" => "fkooman",
                    ]
                ))
            ),
        ]);
        $guzzleClient->getEmitter()->attach($mock);
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm', $guzzleClient);
        $tokenIntrospection = $bearerAuth->execute($request);
        $this->assertEquals('fkooman', $tokenIntrospection->getSub());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testBearerInvalidToken()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $request->setHeader("Authorization", "Bearer xyz");

        $guzzleClient = new Client();
        $mock = new Mock([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                Stream::factory(json_encode(
                    [
                        "active" => false,
                    ]
                ))
            ),
        ]);
        $guzzleClient->getEmitter()->attach($mock);
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm', $guzzleClient);
        $bearerAuth->execute($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testBearerNoToken()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm');
        $bearerAuth->execute($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage invalid_request
     */
    public function testBearerMalformedToken()
    {
        $request = new Request('http://www.example.org/foo', "GET");
        $request->setHeader("Authorization", "Bearer *");
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm');
        $bearerAuth->execute($request);
    }

    public function testBearerQueryParameterToken()
    {
        $request = new Request('http://www.example.org/foo?access_token=foo', "GET");
        $guzzleClient = new Client();
        $mock = new Mock([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                Stream::factory(json_encode(
                    [
                        "active" => true,
                        "sub" => "fkooman",
                    ]
                ))
            ),
        ]);
        $guzzleClient->getEmitter()->attach($mock);
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm', $guzzleClient);
        $tokenIntrospection = $bearerAuth->execute($request);
        $this->assertEquals('fkooman', $tokenIntrospection->getSub());
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage invalid_request
     */
    public function testBearerBothHeaderAndQueryParamter()
    {
        $request = new Request('http://www.example.org/foo?access_token=foo', "GET");
        $request->setHeader("Authorization", "Bearer foo");
        $bearerAuth = new BearerAuthentication('http://localhost/php-oauth-as/introspect.php', 'My Realm');
        $bearerAuth->execute($request);
    }
}
