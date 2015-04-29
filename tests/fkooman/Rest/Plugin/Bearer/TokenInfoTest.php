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

use PHPUnit_Framework_TestCase;

class TokenInfoTest extends PHPUnit_Framework_TestCase
{
    public function testNotActive()
    {
        $t = new TokenInfo(array('active' => false));
        $this->assertFalse($t->getActive());
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
                'x-entitlement' => 'fooe bare baze',
                'x-ext' => array('proprietary', 'extension', 'data'),
            )
        );
        $this->assertTrue($t->getActive());
        $this->assertEquals($now + 1000, $t->getExpiresAt());
        $this->assertEquals($now - 1000, $t->getIssuedAt());
        $this->assertEquals('foo', $t->getSub());
        $this->assertEquals('bar', $t->getClientId());
        $this->assertEquals('foobar', $t->getAud());
        $this->assertTrue($t->getScope()->hasScope('foo'));
        $this->assertTrue($t->getEntitlement()->hasEntitlement('fooe'));
        $this->assertEquals('bearer', $t->getTokenType());
        $token = $t->getToken();
        $this->assertEquals(array('proprietary', 'extension', 'data'), $token['x-ext']);
    }

    public function testActive()
    {
        $t = new TokenInfo(array('active' => true));
        $this->assertTrue($t->getActive());
        // non exiting key should return null
        $this->assertNull($t->getSub());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage active key should be set and its value a boolean
     */
    public function testMissingActive()
    {
        $t = new TokenInfo(array());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage token issued in the future
     */
    public function testIssueTimeInFuture()
    {
        $t = new TokenInfo(array('active' => true, 'iat' => time()+1000));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage token expired before it was issued
     */
    public function testExpiresBeforeIssued()
    {
        $t = new TokenInfo(array('active' => true, 'iat' => time()-500, 'exp' => time()-1000));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage iat value must be positive integer
     */
    public function testNegativeIssueTime()
    {
        $t = new TokenInfo(array('active' => true, 'iat' => -4));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage iat value must be positive integer
     */
    public function testNonIntIssueTime()
    {
        $t = new TokenInfo(array('active' => true, 'iat' => '1234567'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage exp value must be positive integer
     */
    public function testNonIntExpiryTime()
    {
        $t = new TokenInfo(array('active' => true, 'exp' => '1234567'));
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage scope must be string
     */
    public function testNonStringScope()
    {
        $t = new TokenInfo(array('active' => true, 'scope' => 123));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage exp value must be positive integer
     */
    public function testNegativeExpiryTime()
    {
        $t = new TokenInfo(array('active' => true, 'exp' => -4));
    }

    public function testExpiredToken()
    {
        $t = new TokenInfo(array('active' => true, 'exp' => time() - 100));
        $this->assertFalse($t->isValid());
    }

    public function testNonExpiredToken()
    {
        $t = new TokenInfo(array('active' => true, 'exp' => time() + 100));
        $this->assertTrue($t->isValid());
    }

    public function testNonExpiredNonActiveToken()
    {
        $t = new TokenInfo(array('active' => false, 'exp' => time() + 100));
        $this->assertFalse($t->isValid());
    }
}
