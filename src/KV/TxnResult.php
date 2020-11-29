<?php declare(strict_types=1);

namespace DCarbone\PHPConsulAPI\KV;

/*
   Copyright 2016-2020 Daniel Carbone (daniel.p.carbone@gmail.com)

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

use DCarbone\PHPConsulAPI\AbstractModel;

/**
 * Class TxnResult
 * @package DCarbone\PHPConsulAPI\KV
 */
class TxnResult extends AbstractModel
{
    /** @var \DCarbone\PHPConsulAPI\KV\KVPair|null */
    public $KV = null;

    /**
     * TxnResult constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        if (null !== $this->KV && !($this->KV instanceof KVPair)) {
            $this->KV = new KVPair((array)$this->KV, true);
        }
    }

    /**
     * @return \DCarbone\PHPConsulAPI\KV\KVPair|null
     */
    public function getKV()
    {
        return $this->KV;
    }
}