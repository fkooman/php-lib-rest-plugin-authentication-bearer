<?php

/**
 *  Copyright 2014 François Kooman <fkooman@tuxed.net>
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

namespace fkooman\Rest\Plugin\Bearer;

use InvalidArgumentException;

class Entitlement
{
    /** @var array */
    private $entitlement;

    public function __construct($entitlement = null)
    {
        if (null === $entitlement) {
            $this->entitlement = array();
        } else {
            if (!is_string($entitlement)) {
                throw new InvalidArgumentException('argument must be string');
            }
            if (0 === strlen($entitlement)) {
                $this->entitlement = array();
            } else {
                $entitlementTokens = explode(' ', $entitlement);
                foreach ($entitlementTokens as $token) {
                    $this->validateEntitlementToken($token);
                }
                sort($entitlementTokens, SORT_STRING);
                $this->entitlement = array_values(array_unique($entitlementTokens, SORT_STRING));
            }
        }
    }

    public function hasEntitlement($entitlementToken)
    {
        $this->validateEntitlementToken($entitlementToken);

        return in_array($entitlementToken, $this->entitlement);
    }

    private function validateEntitlementToken($entitlementToken)
    {
        if (!is_string($entitlementToken) || 0 >= strlen($entitlementToken)) {
            throw new InvalidArgumentException('entitlement token must be a non-empty string');
        }
        $entitlementTokenRegExp = '/^(?:\x21|[\x23-\x5B]|[\x5D-\x7E])+$/';
        $result = preg_match($entitlementTokenRegExp, $entitlementToken);

        if (1 !== $result) {
            throw new InvalidArgumentException('invalid characters in entitlement token');
        }
    }
}
