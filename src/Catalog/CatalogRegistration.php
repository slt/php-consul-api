<?php declare(strict_types=1);

namespace DCarbone\PHPConsulAPI\Catalog;

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
use DCarbone\PHPConsulAPI\Agent\AgentCheck;
use DCarbone\PHPConsulAPI\Agent\AgentService;
use DCarbone\PHPConsulAPI\Hydration;

/**
 * Class CatalogRegistration
 */
class CatalogRegistration extends AbstractModel
{
    private const FIELD_SERVICE = 'Service';
    private const FIELD_CHECK   = 'Check';

    /** @var string */
    public string $ID = '';
    /** @var string */
    public string $Node = '';
    /** @var string */
    public string $Address = '';
    /** @var array */
    public array $TaggedAddresses = [];
    /** @var array */
    public array $NodeMeta = [];
    /** @var string */
    public string $Datacenter = '';
    /** @var \DCarbone\PHPConsulAPI\Agent\AgentService|null */
    public ?AgentService $Service = null;
    /** @var \DCarbone\PHPConsulAPI\Agent\AgentCheck|null */
    public ?AgentCheck $Check = null;

    /** @var array[] */
    protected const FIELDS = [
        self::FIELD_SERVICE => [
            Hydration::FIELD_TYPE  => Hydration::OBJECT,
            Hydration::FIELD_CLASS => AgentService::class,
        ],
        self::FIELD_CHECK   => [
            Hydration::FIELD_TYPE  => Hydration::OBJECT,
            Hydration::FIELD_CLASS => AgentCheck::class,
        ],
    ];

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->ID;
    }

    /**
     * @param string $id
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setID(string $id): self
    {
        $this->ID = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getNode(): string
    {
        return $this->Node;
    }

    /**
     * @param string $node
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setNode(string $node): self
    {
        $this->Node = $node;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->Address;
    }

    /**
     * @param string $address
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setAddress(string $address): self
    {
        $this->Address = $address;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaggedAddresses(): array
    {
        return $this->TaggedAddresses;
    }

    /**
     * @param array $taggedAddresses
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setTaggedAddresses(array $taggedAddresses): self
    {
        $this->TaggedAddresses = $taggedAddresses;
        return $this;
    }

    /**
     * @return array
     */
    public function getNodeMeta(): array
    {
        return $this->NodeMeta;
    }

    /**
     * @param array $nodeMeta
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setNodeMeta(array $nodeMeta): self
    {
        $this->NodeMeta = $nodeMeta;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatacenter(): string
    {
        return $this->Datacenter;
    }

    /**
     * @param string $datacenter
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setDatacenter(string $datacenter): self
    {
        $this->Datacenter = $datacenter;
        return $this;
    }

    /**
     * @return \DCarbone\PHPConsulAPI\Agent\AgentService|null
     */
    public function getService(): ?AgentService
    {
        return $this->Service;
    }

    /**
     * @param \DCarbone\PHPConsulAPI\Agent\AgentService|null $service
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setService(?AgentService $service): self
    {
        $this->Service = $service;
        return $this;
    }

    /**
     * @return \DCarbone\PHPConsulAPI\Agent\AgentCheck|null
     */
    public function getCheck(): ?AgentCheck
    {
        return $this->Check;
    }

    /**
     * @param \DCarbone\PHPConsulAPI\Agent\AgentCheck|null $check
     * @return \DCarbone\PHPConsulAPI\Catalog\CatalogRegistration
     */
    public function setCheck(?AgentCheck $check): self
    {
        $this->Check = $check;
        return $this;
    }
}
