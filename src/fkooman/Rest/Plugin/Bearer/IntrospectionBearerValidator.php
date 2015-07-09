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

namespace fkooman\Rest\Plugin\Bearer;

use GuzzleHttp\Client;

class IntrospectionBearerValidator implements ValidatorInterface
{
    /** @var string */
    private $endpoint;

    /** @var string */
    private $bearerToken;

    /** @var \GuzzleHttp\Client */
    private $client;

    public function __construct($endpoint, $bearerToken, Client $client = null)
    {
        $this->endpoint = $endpoint;
        $this->bearerToken = $bearerToken;

        if (null === $client) {
            $client = new Client();
        }
        $this->client = $client;
    }

    public function validate($bearerToken)
    {
        $response = $this->client->post(
            $this->endpoint,
            array(
                'headers' => array(
                    'Authorization' => sprintf('Bearer %s', $this->bearerToken),
                    'Accept' => 'application/json',
                ),
                'body' => array(
                    'token' => $bearerToken,
                ),
            )
        );

        return new TokenInfo($response->json());
    }
}
