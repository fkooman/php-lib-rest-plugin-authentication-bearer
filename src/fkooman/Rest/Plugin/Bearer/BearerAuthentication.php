<?php

/**
 * Copyright 2014 François Kooman <fkooman@tuxed.net>.
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
use UnexpectedValueException;

class BearerAuthentication implements ServicePluginInterface
{
    /** @var fkooman\Rest\Plugin\ValidatorInterface */
    private $validator;

    /** @var string */
    private $bearerAuthRealm;

    public function __construct(ValidatorInterface $validator, $bearerAuthRealm = 'Protected Resource')
    {
        $this->bearerAuthRealm = $bearerAuthRealm;
        $this->validator = $validator;
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
        $queryParameter = $request->getUrl()->getQueryParameter('access_token');
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

        // call the registered validator
        $tokenInfo = $this->validator->validate($bearerToken);
        if (!($tokenInfo instanceof TokenInfo)) {
            throw new UnexpectedValueException('invalid response of validate method');
        }

        if (!$tokenInfo->get('active')) {
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
