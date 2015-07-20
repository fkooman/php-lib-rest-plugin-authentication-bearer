<?php

/**
 *  Copyright 2015 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace fkooman\Rest\Plugin\Authentication\Bearer;

use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use InvalidArgumentException;
use RuntimeException;

class TokenInfo implements UserInfoInterface
{
    /** @var array */
    private $response;

    public function __construct(array $response)
    {
        $supportedFields = array(
            'active' => 'is_bool',      // REQUIRED
            'username' => 'is_string',
            'client_id' => 'is_string',
            'scope' => 'is_string',
            'token_type' => 'is_string',
            'exp' => 'is_int',
            'iat' => 'is_int',
            'nbf' => 'is_int',
            'sub' => 'is_string',
            'aud' => 'is_string',
            'iss' => 'is_string',
            'jti' => 'is_string',
        );

        // active key MUST exist
        if (!array_key_exists('active', $response)) {
            throw new InvalidArgumentException('active key missing');
        }

        // some type checking
        foreach ($supportedFields as $key => $validate) {
            if (array_key_exists($key, $response)) {
                if (!call_user_func($validate, $response[$key])) {
                    throw new InvalidArgumentException(
                        sprintf('"%s" fails "%s" check', $key, $validate)
                    );
                }
            }
        }

        $this->response = $response;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->response)) {
            return $this->response[$key];
        }

        return;
    }

    public function getUserId()
    {
        $userIdFields = array(
            'username',
            'sub',
            'me',
        );

        foreach ($userIdFields as $userIdField) {
            if (null !== $this->get($userIdField)) {
                return $this->get($userIdField);
            }
        }

        throw new RuntimeException('user identifier not available from introspection endpoint');
    }

    public function getScope()
    {
        return new Scope($this->get('scope'));
    }
}
