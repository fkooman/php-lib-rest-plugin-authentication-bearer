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
use fkooman\Rest\ServicePluginInterface;
use fkooman\Http\Exception\UnauthorizedException;
use fkooman\Http\Exception\BadRequestException;
use Guzzle\Http\Client;

class BearerAuthentication implements ServicePluginInterface
{
    /** @var string */
    private $introspectionEndpoint;

    /** @var string */
    private $bearerAuthRealm;

    /** @var GuzzleHttp\Client */
    private $guzzleClient;

    public function __construct($introspectionEndpoint, $bearerAuthRealm = 'Protected Resource', Client $guzzleClient = null)
    {
        $this->introspectionEndpoint = $introspectionEndpoint;
        $this->bearerAuthRealm = $bearerAuthRealm;
        if (null === $guzzleClient) {
            $guzzleClient = new Client();
        }
        $this->guzzleClient = $guzzleClient;
    }

    public function execute(Request $request, array $routeConfig)
    {
        $requireAuth = true;
        if (array_key_exists('requireAuth', $routeConfig)) {
            if (!$routeConfig['requireAuth']) {
                $requireAuth = false;
            }
        }

        $headerFound = false;
        $queryParameterFound = false;

        $authorizationHeader = $request->getHeader('Authorization');
        if (0 === stripos($authorizationHeader, 'Bearer ')) {
            // Bearer header found
            $headerFound = true;
        }
        $queryParameter = $request->getQueryParameter('access_token');
        if (null !== $queryParameter) {
            // Query parameter found
            $queryParameterFound = true;
        }

        if (!$headerFound && !$queryParameterFound) {
            // none found
            if (!$requireAuth) {
                return false;
            }
            throw new UnauthorizedException(
                'invalid_token',
                'no token provided',
                'Bearer',
                array(
                    'realm' => $this->bearerAuthRealm,
                )
            );
        }
        if ($headerFound && $queryParameterFound) {
            // both found
            throw new BadRequestException(
                'invalid_request',
                'token provided through both authorization header and query string'
            );
        }
        if ($headerFound) {
            $bearerToken = substr($authorizationHeader, 7);
        } else {
            $bearerToken = $queryParameter;
        }

        // we received a Bearer token, verify the syntax
        if (!$this->isValidTokenSyntax($bearerToken)) {
            throw new BadRequestException(
                'invalid_request',
                'invalid token syntax'
            );
        }

        // we have a token that has valid syntax, send it to the introspection
        // service
        $request = $this->guzzleClient->get($this->introspectionEndpoint);
        $request->getQuery()->set('token', $bearerToken);
        $response = $request->send();

        $tokenInfo = new TokenInfo($response->json());
        if (!$tokenInfo->isValid()) {
            if (!$requireAuth) {
                return false;
            }
            throw new UnauthorizedException(
                'invalid_token',
                'token is invalid or expired',
                'Bearer',
                array(
                    'realm' => $this->bearerAuthRealm,
                    'error' => 'invalid_token',
                    'error_description' => 'token is invalid or expired',
                )
            );
        }

        return $tokenInfo;
    }

    private function isValidTokenSyntax($bearerToken)
    {
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        if (1 !== preg_match('|^[[:alpha:][:digit:]-._~+/]+=*$|', $bearerToken)) {
            return false;
        }

        return true;
    }
}
