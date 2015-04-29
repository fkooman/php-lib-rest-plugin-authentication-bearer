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

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Http\Exception\HttpException;
use fkooman\Http\Exception\InternalServerErrorException;
use fkooman\Http\Exception\ForbiddenException;
use fkooman\Rest\Service;
use fkooman\Rest\Plugin\Bearer\BearerAuthentication;
use fkooman\Rest\Plugin\Bearer\TokenInfo;

try {
    $service = new Service();

    $service->registerOnMatchPlugin(
        new BearerAuthentication(
            'http://localhost/php-oauth-as/introspect.php',
            'My OAuth API'
        )
    );

    $service->get(
        '/getMyUserId',
        function (TokenInfo $u) {
            if (!$u->getScope()->hasScope('userid')) {
                throw new ForbiddenException('insufficient_scope', 'scope "userid" needed');
            }

            return sprintf('Hello %s', $u->getSub());
        }
    );

    $service->run()->sendResponse();
} catch (Exception $e) {
    Service::handleException($e)->sendResponse();
}
