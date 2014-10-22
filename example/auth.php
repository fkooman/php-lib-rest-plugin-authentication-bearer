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

use fkooman\Http\Request;
use fkooman\Http\IncomingRequest;
use fkooman\Http\Exception\HttpException;
use fkooman\Http\Exception\InternalServerErrorException;
use fkooman\Rest\Service;
use fkooman\Rest\Plugin\Bearer\BearerAuthentication;
use fkooman\OAuth\Common\TokenIntrospection;

try {
    $service = new Service();

    $service->registerBeforeEachMatchPlugin(
       new BearerAuthentication()
    );

    $service->get(
        '/getMyUserId',
        function (TokenIntrospection $u) {
            return sprintf('Hello %s', $u->getSub());
        }
    );

    $request = Request::fromIncomingRequest(
        new IncomingRequest()
    );

    $service->run($request)->sendResponse();
} catch (Exception $e) {
    if ($e instanceof HttpException) {
        $response = $e->getJsonResponse();
    } else {
        // we catch all other (unexpected) exceptions and return a 500
        $e = new InternalServerErrorException($e->getMessage());
        $response = $e->getJsonResponse();
    }
    $response->sendResponse();
}
