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

namespace fkooman\Rest\Plugin\Bearer;

use fkooman\Http\Request;
use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use PHPUnit_Framework_TestCase;

class BearerAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testBearerValidToken()
    {
        $request = new Request('http://www.example.org/foo', 'GET');
        $request->setHeaders(array('Authorization' => 'Bearer xyz'));

        $guzzleClient = new Client();
        $plugin = new MockPlugin();
        $response = new Response(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(
            json_encode(
                array(
                    'active' => true,
                    'sub' => 'fkooman',
                )
            )
        );
        $plugin->addResponse($response);
        $guzzleClient->addSubscriber($plugin);

        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $guzzleClient),
            'My Realm'
        );
        $tokenIntrospection = $bearerAuth->execute($request, array());
        $this->assertEquals('fkooman', $tokenIntrospection->get('sub'));
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testBearerInvalidToken()
    {
        $request = new Request('http://www.example.org/foo', 'GET');
        $request->setHeaders(array('Authorization' => 'Bearer xyz'));

        $guzzleClient = new Client();
        $plugin = new MockPlugin();
        $response = new Response(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(
            json_encode(
                array(
                        'active' => false,
                )
            )
        );
        $plugin->addResponse($response);
        $guzzleClient->addSubscriber($plugin);

        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $guzzleClient),
            'My Realm'
        );
        $bearerAuth->execute($request, array());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testBearerNoToken()
    {
        $request = new Request('http://www.example.org/foo', 'GET');
        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
            'My Realm'
        );
        $bearerAuth->execute($request, array());
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage invalid_request
     */
    public function testBearerMalformedToken()
    {
        $request = new Request('http://www.example.org/foo', 'GET');
        $request->setHeaders(array('Authorization' => 'Bearer *'));
        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
            'My Realm'
        );
        $bearerAuth->execute($request, array());
    }

    public function testBearerQueryParameterToken()
    {
        $request = new Request('http://www.example.org/foo?access_token=foo', 'GET');

        $guzzleClient = new Client();
        $plugin = new MockPlugin();
        $response = new Response(200);
        $response->setHeaders(array('Content-Type' => 'application/json'));
        $response->setBody(
            json_encode(
                array(
                    'active' => true,
                    'sub' => 'fkooman',
                )
            )
        );
        $plugin->addResponse($response);
        $guzzleClient->addSubscriber($plugin);

        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $guzzleClient),
            'My Realm'
        );
        $tokenIntrospection = $bearerAuth->execute($request, array());
        $this->assertEquals('fkooman', $tokenIntrospection->get('sub'));
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage invalid_request
     */
    public function testBearerBothHeaderAndQueryParamter()
    {
        $request = new Request('http://www.example.org/foo?access_token=foo', 'GET');
        $request->setHeaders(array('Authorization' => 'Bearer foo'));
        $bearerAuth = new BearerAuthentication(
            new IntrospectionValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
            'My Realm'
        );
        $bearerAuth->execute($request, array());
    }
}
