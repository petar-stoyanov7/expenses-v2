<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ExpenseRepository::class)
 * @Table(name="expenses")
 */
class Expense
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Car::class, inversedBy="expenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $car;

    /**
     * @ORM\ManyToOne(targetEntity=ExpenseType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $expenseType;

    /**
     * @ORM\ManyToOne(targetEntity=FuelType::class)
     */
    private $fuelType;

    /**
     * @ORM\Column(type="integer")
     */
    private $mileage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $liters;

    /**
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notes;

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

    public function getExpenseType(): ?ExpenseType
    {
        return $this->expenseType;
    }

    public function setExpenseType(?ExpenseType $expenseType): self
    {
        $this->expenseType = $expenseType;

        return $this;
    }

    public function getFuelType(): ?FuelType
    {
        return $this->fuelType;
    }

    public function setFuelType(?FuelType $fuelType): self
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): self
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getLiters(): ?int
    {
        return $this->liters;
    }

    public function setLiters(?int $liters): self
    {
        $this->liters = $liters;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
