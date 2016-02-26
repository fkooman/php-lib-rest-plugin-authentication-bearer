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

use PHPUnit_Framework_TestCase;

class ArrayBearerValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValid()
    {
        $v = new ArrayBearerValidator([
            '12345678',
            '87654321',
        ]);
        $this->assertTrue($v->validate('87654321')->get('active'));
    }

    public function testInvalid()
    {
        $v = new ArrayBearerValidator([
            '12345678',
            '87654321',
        ]);
        $this->assertFalse($v->validate('44445555')->get('active'));
    }

    public function testValidArrayScopeFormat()
    {
        $v = new ArrayBearerValidator(
            [
                'foo' => [
                    'token' => 'abcdef',
                    'scope' => 'foo bar',
                ],
                'bar' => [
                    'token' => 'fedcba',
                    'scope' => 'baz',
                ],
            ]
        );
        $tokenInfo = $v->validate('abcdef');
        $this->assertTrue($tokenInfo->get('active'));
        $this->assertSame('foo bar', $tokenInfo->get('scope'));
        $this->assertSame('foo', $tokenInfo->get('username'));
    }

    public function testInvalidArrayScopeFormat()
    {
        $v = new ArrayBearerValidator(
            [
                'foo' => [
                    'token' => 'abcdef',
                    'scope' => 'foo bar',
                ],
                'bar' => [
                    'token' => 'fedcba',
                    'scope' => 'baz',
                ],
            ]
        );
        $this->assertFalse($v->validate('aabbcc')->get('active'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage no token configured for "foo"
     */
    public function testInvalidArrayScope()
    {
        $v = new ArrayBearerValidator(
            [
                'foo' => [
                    'scope' => 'foo bar',
                ],
            ]
        );
        $v->validate('aabbcc');
    }

    public function testNoScopeArrayScope()
    {
        $v = new ArrayBearerValidator(
            [
                'foo' => [
                    'token' => 'aabbcc',
                ],
            ]
        );
        $tokenInfo = $v->validate('aabbcc');
        $this->assertTrue($tokenInfo->get('active'));
        $this->assertSame('', $tokenInfo->get('scope'));
    }
}
