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

namespace fkooman\Rest\Plugin\Authentication\Bearer\Test;

use fkooman\Rest\Plugin\Authentication\Bearer\ValidatorInterface;
use fkooman\Rest\Plugin\Authentication\Bearer\TokenInfo;

class TestValidator implements ValidatorInterface
{
    public function validate($bearerToken)
    {
        if ('t_fkooman' === $bearerToken) {
            return new TokenInfo(
                array(
                    'active' => true,
                    'sub' => 'fkooman',
                )
            );
        }

        // assume not active
        return new TokenInfo(
            array(
                'active' => false,
            )
        );
    }
}
