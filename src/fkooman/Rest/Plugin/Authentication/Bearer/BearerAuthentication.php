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
use fkooman\Rest\Service;
use fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface;
use fkooman\Http\Exception\UnauthorizedException;
use fkooman\Http\Exception\BadRequestException;
use UnexpectedValueException;

class BearerAuthentication implements AuthenticationPluginInterface
{
    /** @var fkooman\Rest\Plugin\Authentication\Bearer\ValidatorInterface */
    private $validator;

    /** @var array */
    private $authParams;

    public function __construct(ValidatorInterface $validator, $authParams = array())
    {
        $this->validator = $validator;
        if (!array_key_exists('realm', $authParams)) {
            $authParams['realm'] = 'Protected Resource';
        }
        $this->authParams = $authParams;
    }

    public function init(Service $service)
    {
        // NOP
    }

    private static function isAttempt($authHeader)
    {
        if (null === $authHeader) {
            return false;
        }
        if (7 >= strlen($authHeader)) {
            return false;
        }
        if (0 !== strpos($authHeader, 'Bearer ')) {
            return false;
        }

        return true;
    }

    public function isAuthenticated(Request $request)
    {
        $authHeader = $request->getHeader('Authorization');
        if (!self::isAttempt($authHeader)) {
            // no attempt
            return false;
        }

        // if there is an attempt, it MUST succeed
        $bearerToken = substr($authHeader, 7);
        self::validateTokenSyntax($bearerToken);

        // call the registered validator
        $tokenInfo = $this->validator->validate($bearerToken);
        if (!($tokenInfo instanceof TokenInfo)) {
            throw new UnexpectedValueException('invalid response of validate method');
        }

        if (!$tokenInfo->get('active')) {
            return false;
        }

        return $tokenInfo;
    }

    public function requestAuthentication(Request $request)
    {
        $authHeader = $request->getHeader('Authorization');
        if (self::isAttempt($authHeader)) {
            $error = 'invalid_token';
            $authParams = array_merge(
                $this->authParams,
                array(
                    'error' => $error,
                )
            );
        } else {
            $error = 'no_token';
            // if there is no token provided, we should not include an
            // error in the WWW-Authenticate header
            $authParams = $this->authParams;
        }

        $e = new UnauthorizedException(
            $error,
            null    // parameter no longer needed in fkooman/http >= 1.3.1
        );
        $e->addScheme('Bearer', $authParams);
        throw $e;
    }

    public static function validateTokenSyntax($bearerToken)
    {
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        if (1 !== preg_match('|^[[:alpha:][:digit:]-._~+/]+=*$|', $bearerToken)) {
            throw new BadRequestException(
                'invalid_request',
                'invalid token syntax'
            );
        }
    }
}
