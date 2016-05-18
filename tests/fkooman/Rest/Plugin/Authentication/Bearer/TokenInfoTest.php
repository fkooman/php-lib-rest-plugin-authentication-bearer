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

use PHPUnit_Framework_TestCase;

class TokenInfoTest extends PHPUnit_Framework_TestCase
{
    public function testNotActive()
    {
        $t = new TokenInfo(array('active' => false));
        $this->assertFalse($t->get('active'));
    }

    public function testComplete()
    {
        $now = time();

        $t = new TokenInfo(
            array(
                'active' => true,
                'exp' => $now + 1000,
                'iat' => $now - 1000,
                'sub' => 'foo',
                'client_id' => 'bar',
                'aud' => 'foobar',
                'scope' => 'foo bar baz',
                'token_type' => 'bearer',
                'x-ext' => array('proprietary', 'extension', 'data'),
            )
        );
        $this->assertTrue($t->get('active'));
        $this->assertEquals($now + 1000, $t->get('exp'));
        $this->assertEquals($now - 1000, $t->get('iat'));
        $this->assertEquals('foo', $t->get('sub'));
        $this->assertEquals('bar', $t->get('client_id'));
        $this->assertEquals('foobar', $t->get('aud'));
        $this->assertTrue($t->getScope()->hasScope('foo'));
        $this->assertEquals('bearer', $t->get('token_type'));
    }

    public function testActive()
    {
        $t = new TokenInfo(array('active' => true));
        $this->assertTrue($t->get('active'));
        // non exiting key should return null
        $this->assertNull($t->get('sub'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage active key missing
     */
    public function testMissingActive()
    {
        $t = new TokenInfo(array());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "iat" fails "is_int" check
     */
    public function testNonIntIssueTime()
    {
        $t = new TokenInfo(array('active' => true, 'iat' => '1234567'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "scope" fails "is_string" check
     */
    public function testNonStringScope()
    {
        $t = new TokenInfo(array('active' => true, 'scope' => 123));
    }
}
