<?php declare(strict_types=1);

namespace DCarbone\PHPConsulAPI;

/*
   Copyright 2016-2021 Daniel Carbone (daniel.p.carbone@gmail.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

use Psr\Http\Message\UriInterface;

/**
 * Class RequestMeta
 */
class RequestMeta
{
    /** @var string */
    public string $method;
    /** @var \Psr\Http\Message\UriInterface */
    public UriInterface $uri;

    /**
     * RequestMeta constructor.
     * @param string $method
     * @param \Psr\Http\Message\UriInterface $uri
     */
    public function __construct(string $method, UriInterface $uri)
    {
        $this->method = $method;
        $this->uri    = $uri;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s %s', $this->method, $this->uri);
    }
}
