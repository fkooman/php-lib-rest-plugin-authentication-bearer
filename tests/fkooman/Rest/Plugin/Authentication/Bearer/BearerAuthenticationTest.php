<?php

/**
 * Copyright 2015 François Kooman <fkooman@tuxed.net>.
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

namespace fkooman\Rest\Plugin\Authentication\Bearer;

use fkooman\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit_Framework_TestCase;

class BearerAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testBearerValidToken()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Bearer xyz',
            )
        );

        $client = new Client();
        $mock = new Mock(
            array(
                new Response(
                    200,
                    array('Content-Type' => 'application/json'),
                    Stream::factory(
                        json_encode(
                            array(
                                'active' => true,
                                'sub' => 'fkooman',
                            )
                        )
                    )
                ),
            )
        );
        $client->getEmitter()->attach($mock);

        $bearerAuth = new BearerAuthentication(
            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $client),
            array('realm' => 'My Realm')
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
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Bearer xyz',
            )
        );

        $client = new Client();
        $mock = new Mock(
            array(
                new Response(
                    200,
                    array('Content-Type' => 'application/json'),
                    Stream::factory(
                        json_encode(
                            array(
                                'active' => false,
                            )
                        )
                    )
                ),
            )
        );
        $client->getEmitter()->attach($mock);

        $bearerAuth = new BearerAuthentication(
            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $client),
            array('realm' => 'My Realm')
        );
        $bearerAuth->execute($request, array());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage no_credentials
     */
    public function testBearerNoToken()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );
        $bearerAuth = new BearerAuthentication(
            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
            array('realm' => 'My Realm')
        );
        $bearerAuth->execute($request, array());
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage invalid_request
     */
    public function testBearerMalformedToken()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Bearer *',
            )
        );
        $bearerAuth = new BearerAuthentication(
            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
            array('realm' => 'My Realm')
        );
        $bearerAuth->execute($request, array());
    }

#    public function testBearerQueryParameterToken()
#    {
#        $request = new Request(
#            array(
#                'SERVER_NAME' => 'www.example.org',
#                'SERVER_PORT' => 80,
#                'QUERY_STRING' => 'access_token=foo',
#                'REQUEST_URI' => '/?access_token=foo',
#                'SCRIPT_NAME' => '/index.php',
#                'REQUEST_METHOD' => 'GET',
#            )
#        );
#        $guzzleClient = new Client();
#        $plugin = new MockPlugin();
#        $response = new Response(200);
#        $response->setHeaders(array('Content-Type' => 'application/json'));
#        $response->setBody(
#            json_encode(
#                array(
#                    'active' => true,
#                    'sub' => 'fkooman',
#                )
#            )
#        );
#        $plugin->addResponse($response);
#        $guzzleClient->addSubscriber($plugin);

#        $bearerAuth = new BearerAuthentication(
#            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar', $guzzleClient),
#            array('realm' => 'My Realm')
#        );
#        $tokenIntrospection = $bearerAuth->execute($request, array());
#        $this->assertEquals('fkooman', $tokenIntrospection->get('sub'));
#    }

#    /**
#     * @expectedException fkooman\Http\Exception\BadRequestException
#     * @expectedExceptionMessage invalid_request
#     */
#    public function testBearerBothHeaderAndQueryParameter()
#    {
#        $request = new Request(
#            array(
#                'SERVER_NAME' => 'www.example.org',
#                'SERVER_PORT' => 80,
#                'QUERY_STRING' => 'access_token=foo',
#                'REQUEST_URI' => '/?access_token=foo',
#                'SCRIPT_NAME' => '/index.php',
#                'REQUEST_METHOD' => 'GET',
#                'HTTP_AUTHORIZATION' => 'Bearer foo',
#            )
#        );
#        $bearerAuth = new BearerAuthentication(
#            new IntrospectionUserPassValidator('http://localhost/php-oauth-as/introspect.php', 'foo', 'bar'),
#            array('realm' => 'My Realm')
#        );
#        $bearerAuth->execute($request, array());
#    }

    public function testOptionalAuthWithoutAttempt()
    {
        $stub = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\Bearer\ValidatorInterface')
                     ->setMockClassName('MyValidator')
                     ->getMock();
        $stub->method('validate')
             ->willReturn(new TokenInfo(array('active' => false)));

        $b = new BearerAuthentication(
            $stub, array('realm' => 'Realm')
        );

        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );
        $this->assertNull($b->execute($request, array('require' => false)));
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage invalid_token
     */
    public function testOptionalAuthWithAttempt()
    {
        $stub = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\Bearer\ValidatorInterface')
                     ->setMockClassName('MyValidator')
                     ->getMock();
        $stub->method('validate')
             ->willReturn(new TokenInfo(array('active' => false)));

        $b = new BearerAuthentication($stub);

        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'POST',
                'HTTP_AUTHORIZATION' => 'Bearer xyz',
            ),
            array('token' => 'foo')
        );
        $b->execute($request, array('require' => false));
    }
}
