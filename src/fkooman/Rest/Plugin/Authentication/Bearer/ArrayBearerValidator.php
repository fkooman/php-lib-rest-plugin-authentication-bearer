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

use RuntimeException;

class ArrayBearerValidator implements ValidatorInterface
{
    /** @var array */
    private $bearerTokens;

    /**
     * @param array $bearerTokens an array with valid bearer tokens
     */
    public function __construct(array $bearerTokens)
    {
        $this->bearerTokens = $bearerTokens;
    }

    /**
     * @return TokenInfo
     */
    public function validate($bearerToken)
    {
        // we loop through all valid bearer tokens and try to find the one
        // provided; we perform a timing safe compare to not leak any
        // information about the valid tokens, we can return as soon as we
        // found a match because all non-matching tokens will take exactly the
        // same amount of time to find them, i.e. loop through all
        foreach ($this->bearerTokens as $k => $v) {
            if (is_array($v)) {
                if (!array_key_exists('token', $v) || empty($v['token'])) {
                    throw new RuntimeException(sprintf('no token configured for "%s"', $k));
                }

                if (self::hashEquals($v['token'], $bearerToken)) {
                    $scope = array_key_exists('scope', $v) ? $v['scope'] : '';

                    return new TokenInfo(
                        ['active' => true, 'scope' => $scope, 'username' => $k]
                    );
                }
            } else {
                // XXX: DEPRECATED
                if (self::hashEquals($v, $bearerToken)) {
                    return new TokenInfo(
                        ['active' => true]
                    );
                }
            }
        }

        return new TokenInfo(
            ['active' => false]
        );
    }

    /**
     * Wrapper to compare two hashes in a timing safe way.
     *
     * @param string $safe the string we control
     * @param string $user the string the user controls
     *
     * @return bool whether or not the two strings are identical
     */
    public static function hashEquals($safe, $user)
    {
        // PHP >= 5.6.0 has "hash_equals"
        if (function_exists('hash_equals')) {
            return hash_equals($safe, $user);
        }

        return self::timingSafeEquals($safe, $user);
    }

    /**
     * A timing safe equals comparison.
     *
     * @param string $safe The internal (safe) value to be checked
     * @param string $user The user submitted (unsafe) value
     *
     * @return bool true if the two strings are identical
     *
     * @see http://blog.ircmaxell.com/2014/11/its-all-about-time.html
     */
    public static function timingSafeEquals($safe, $user)
    {
        $safeLen = strlen($safe);
        $userLen = strlen($user);
        if ($userLen != $safeLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safe[$i]) ^ ord($user[$i]));
        }
        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }
}
