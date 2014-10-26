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

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class EntitlementTest extends PHPUnit_Framework_TestCase
{
    public function testEntitlement()
    {
        $s = new Entitlement('read write foo');
        $this->assertTrue($s->hasEntitlement('read'));
        $this->assertTrue($s->hasEntitlement('write'));
        $this->assertTrue($s->hasEntitlement('foo'));
        $this->assertFalse($s->hasEntitlement('bar'));
    }

    public function testEmptyEntitlement()
    {
        $s = new Entitlement();
        $this->assertFalse($s->hasEntitlement('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage invalid characters in entitlement token
     */
    public function testInvalidEntitlementToken()
    {
        $s = new Entitlement('€ $');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage entitlement token must be a non-empty string
     */
    public function testEmptyArrayEntitlement()
    {
        $s = new Entitlement('foo  bar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage argument must be string
     */
    public function testNonStringFromString()
    {
        $s = new Entitlement(5);
    }
}
