<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'country')]
class Country
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 2, unique: true)]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $region;

    #[ORM\Column(type: 'string', length: 100)]
    private string $subRegion;

    #[ORM\Column(type: 'string', length: 100)]
    private string $demonym;

    #[ORM\Column(type: 'bigint')]
    private int $population;

    #[ORM\Column(type: 'boolean')]
    private bool $independent;

    #[ORM\Column(type: 'string', length: 500)]
    private string $flag;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $currencyName = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $currencySymbol = null;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function getSubRegion(): string
    {
        return $this->subRegion;
    }

    public function setSubRegion(string $subRegion): self
    {
        $this->subRegion = $subRegion;
        return $this;
    }

    public function getDemonym(): string
    {
        return $this->demonym;
    }

    public function setDemonym(string $demonym): self
    {
        $this->demonym = $demonym;
        return $this;
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;
        return $this;
    }

    public function isIndependent(): bool
    {
        return $this->independent;
    }

    public function setIndependent(bool $independent): self
    {
        $this->independent = $independent;
        return $this;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): self
    {
        $this->flag = $flag;
        return $this;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currencyName;
    }

    public function setCurrencyName(?string $currencyName): self
    {
        $this->currencyName = $currencyName;
        return $this;
    }

    public function getCurrencySymbol(): ?string
    {
        return $this->currencySymbol;
    }

    public function setCurrencySymbol(?string $currencySymbol): self
    {
        $this->currencySymbol = $currencySymbol;
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'region' => $this->region,
            'subRegion' => $this->subRegion,
            'demonym' => $this->demonym,
            'population' => $this->population,
            'independent' => $this->independent,
            'flag' => $this->flag,
        ];
        if ($this->currencyName !== null || $this->currencySymbol !== null) {
            $data['currency'] = [
                'name' => $this->currencyName ?? '',
                'symbol' => $this->currencySymbol ?? '',
            ];
        }
        return $data;
    }
}
