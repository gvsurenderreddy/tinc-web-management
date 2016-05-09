<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sequence
 *
 * @ORM\Table(name="sequence")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SequenceRepository")
 */
class Sequence
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="Sequence", type="string", length=255, unique=true)
     */
    private $sequence;

    /**
     * @var int
     *
     * @ORM\Column(name="Value", type="integer")
     */
    private $value;

    /**
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return Sequence
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
}

