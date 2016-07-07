<?php namespace DCarbone\PHPConsulAPI\Status;

/*
   Copyright 2016 Daniel Carbone (daniel.p.carbone@gmail.com)

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

use DCarbone\PHPConsulAPI\AbstractConsulClient;
use DCarbone\PHPConsulAPI\QueryOptions;

/**
 * Class StatusClient
 * @package DCarbone\PHPConsulAPI\Status
 */
class StatusClient extends AbstractConsulClient
{
    /**
     * @param QueryOptions|null $queryOptions
     * @return array|null
     */
    public function leader(QueryOptions $queryOptions = null)
    {
        return $this->execute('get', 'v1/status/leader', $queryOptions);
    }

    /**
     * @param QueryOptions|null $queryOptions
     * @return array|null
     */
    public function peers(QueryOptions $queryOptions = null)
    {
        return $this->execute('get', 'v1/status/peers', $queryOptions);
    }
}