<?php declare(strict_types=1);

namespace DCarbone\PHPConsulAPI\Agent;

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

use DCarbone\PHPConsulAPI\AbstractModel;
use DCarbone\PHPConsulAPI\Hydration;

/**
 * Class AgentServiceConnect
 */
class AgentServiceConnect extends AbstractModel
{
    protected const FIELDS = [
        self::FIELD_NATIVE          => Hydration::OMITEMPTY_BOOLEAN_FIELD,
        self::FIELD_SIDECAR_SERVICE => [
            Hydration::FIELD_TYPE      => Hydration::ARRAY,
            Hydration::FIELD_CLASS     => AgentServiceRegistration::class,
            Hydration::FIELD_OMITEMPTY => true,
        ],
    ];

    private const FIELD_NATIVE          = 'Native';
    private const FIELD_SIDECAR_SERVICE = 'SidecarService';

    /** @var bool */
    public bool $Native = false;
    /** @var \DCarbone\PHPConsulAPI\Agent\AgentServiceRegistration[] */
    public array $SidecarService = [];

    /**
     * @return bool
     */
    public function isNative(): bool
    {
        return $this->Native;
    }

    /**
     * @param bool $Native
     * @return \DCarbone\PHPConsulAPI\Agent\AgentServiceConnect
     */
    public function setNative(bool $Native): self
    {
        $this->Native = $Native;
        return $this;
    }

    /**
     * @return \DCarbone\PHPConsulAPI\Agent\AgentServiceRegistration[]
     */
    public function getSidecarService(): array
    {
        return $this->SidecarService;
    }

    /**
     * @param \DCarbone\PHPConsulAPI\Agent\AgentServiceRegistration[] $SidecarService
     * @return \DCarbone\PHPConsulAPI\Agent\AgentServiceConnect
     */
    public function setSidecarService(array $SidecarService): self
    {
        $this->SidecarService = $SidecarService;
        return $this;
    }
}