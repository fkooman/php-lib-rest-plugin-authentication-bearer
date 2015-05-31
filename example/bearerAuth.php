<?php

/**
 * Copyright 2014 François Kooman <fkooman@tuxed.net>.
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
use fkooman\Rest\Plugin\Bearer\BearerAuthentication;
use fkooman\Rest\Plugin\Bearer\IntrospectionUserPassValidator;
use fkooman\Rest\Plugin\Bearer\TokenInfo;

$pluginRegistry = new PluginRegistry();
$pluginRegistry->registerDefaultPlugin(
    new BearerAuthentication(
        new IntrospectionUserPassValidator(
            'http://localhost/php-oauth-as/introspect.php',
            'foo',
            'bar'
        ),
        'My OAuth API'
    )
);
$service->setPluginRegistry($pluginRegistry);

$service->get(
    '/getMyUserId',
    function (TokenInfo $u) {
        if (!$u->getScope()->hasScope('userid')) {
            throw new ForbiddenException('insufficient_scope', 'scope "userid" needed');
        }

        return sprintf('Hello %s', $u->get('sub'));
    }
);

$service->run()->sendResponse();