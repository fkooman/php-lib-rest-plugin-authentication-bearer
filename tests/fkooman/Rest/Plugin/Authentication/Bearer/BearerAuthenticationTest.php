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

require_once __DIR__.'/Test/TestValidator.php';

use fkooman\Http\Request;
use PHPUnit_Framework_TestCase;
use fkooman\Rest\Plugin\Authentication\Bearer\Test\TestValidator;
use fkooman\Http\Exception\UnauthorizedException;

class BearerAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testIsAuthenticatedValid()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Bearer t_fkooman',
            )
        );

        $auth = new BearerAuthentication(
            new TestValidator()
        );

        $tokenInfo = $auth->isAuthenticated($request);
        $this->assertTrue($tokenInfo->get('active'));
        $this->assertSame('fkooman', $tokenInfo->get('sub'));
    }

    public function testIsAuthenticatedValidQueryParameter()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => 'access_token=t_fkooman',
                'REQUEST_URI' => '/?access_token=t_fkooman',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );

        $auth = new BearerAuthentication(
            new TestValidator()
        );

        $tokenInfo = $auth->isAuthenticated($request);
        $this->assertTrue($tokenInfo->get('active'));
        $this->assertSame('fkooman', $tokenInfo->get('sub'));
    }

    public function testIsAuthenticatedInvalid()
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

        $auth = new BearerAuthentication(
            new TestValidator()
        );
        $this->assertFalse($auth->isAuthenticated($request));
    }

    public function testIsAuthenticatedInvalidQueryParameter()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => 'access_token=xyz',
                'REQUEST_URI' => '/?access_token=xyz',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );

        $auth = new BearerAuthentication(
            new TestValidator()
        );
        $this->assertFalse($auth->isAuthenticated($request));
    }

    public function testIsAuthenticatedNoAttempt()
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

        $auth = new BearerAuthentication(
            new TestValidator()
        );
        $this->assertFalse($auth->isAuthenticated($request));
    }

    public function testRequestAuthenticationNoToken()
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
        $auth = new BearerAuthentication(
            new TestValidator(),
            array(
                'my_var' => 'foo',
            )
        );

        try {
            $auth->requestAuthentication($request);
            $this->assertTrue(false);
        } catch (UnauthorizedException $e) {
            $this->assertSame(
                array(
                    'HTTP/1.1 401 Unauthorized',
                    'Content-Type: application/json',
                    'Content-Length: 20',
                    'Www-Authenticate: Bearer my_var="foo",realm="Protected Resource"',
                    '',
                    '{"error":"no_token"}',
                ),
                $e->getJsonResponse()->toArray()
            );
        }
    }

    public function testRequestAuthenticationInvalidToken()
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
        $auth = new BearerAuthentication(
            new TestValidator(),
            array(
                'my_var' => 'foo',
            )
        );

        try {
            $auth->requestAuthentication($request);
            $this->assertTrue(false);
        } catch (UnauthorizedException $e) {
            $this->assertSame(
                array(
                    'HTTP/1.1 401 Unauthorized',
                    'Content-Type: application/json',
                    'Content-Length: 25',
                    'Www-Authenticate: Bearer my_var="foo",realm="Protected Resource",error="invalid_token"',
                    '',
                    '{"error":"invalid_token"}',
                ),
                $e->getJsonResponse()->toArray()
            );
        }
    }
}
