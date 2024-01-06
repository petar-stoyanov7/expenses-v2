<?php

namespace App\Entity;

use App\Repository\CarFuelsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass=CarFuelsRepository::class)
 * @Table(name="car_fuels")
 */
class CarFuels
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Car::class, inversedBy="fuels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $car;

    /**
     * @ORM\ManyToOne(targetEntity=FuelType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $fuel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): self
    {
        $this->car = $car;

        return $this;
    }

    public function getFuel(): ?FuelType
    {
        return $this->fuel;
    }

    public function setFuel(?FuelType $fuel): self
    {
        $this->fuel = $fuel;

        return $this;
    }
}
