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
require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Http\Exception\ForbiddenException;
use fkooman\Rest\Plugin\Authentication\Bearer\BearerAuthentication;
use fkooman\Rest\Plugin\Authentication\Bearer\IntrospectionUserPassValidator;
use fkooman\Rest\Plugin\Authentication\Bearer\TokenInfo;
use fkooman\Rest\Service;

$service = new Service();
$service->getPluginRegistry()->registerDefaultPlugin(
    new BearerAuthentication(
        new IntrospectionUserPassValidator(
            'http://localhost/php-oauth-as/introspect.php',
            'foo',
            'bar'
        ),
        array('realm' => 'My OAuth API')
    )
);
$service->get(
    '/getMyUserId',
    function (TokenInfo $u) {
        if (!$u->getScope()->hasScope('userid')) {
            throw new ForbiddenException('insufficient_scope', 'scope "userid" needed');
        }

        return sprintf('Hello %s', $u->get('sub'));
    }
);

$service->run()->send();
