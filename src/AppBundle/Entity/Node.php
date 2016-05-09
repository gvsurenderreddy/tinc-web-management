<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Node
 *
 * @ORM\Table(name="node")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NodeRepository")
 * @UniqueEntity("name", message="Node name already in use.")
 * @ORM\HasLifecycleCallbacks
 */
class Node
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="Name", type="string", length=16, unique=true)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="IsStaticOrDynDNS", type="boolean")
     */
    private $isStaticOrDynDNS;

    /**
     * @var string
     *
     * @ORM\Column(name="Address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var int
     *
     * @ORM\Column(name="Port", type="integer", nullable=true)
     */
    private $port;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DateTimeReg", type="datetime")
     */
    private $dateTimeReg;

    /**
     * @var int
     *
     * @ORM\Column(name="Version", type="smallint")
     */
    private $version;

    /**
     * @var int
     *
     * @ORM\Column(name="Seq", type="integer")
     */
    private $seq;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Node
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isStaticOrDynDNS
     *
     * @param boolean $isStaticOrDynDNS
     *
     * @return Node
     */
    public function setIsStaticOrDynDNS($isStaticOrDynDNS)
    {
        $this->isStaticOrDynDNS = $isStaticOrDynDNS;

        return $this;
    }

    /**
     * Get isStaticOrDynDNS
     *
     * @return bool
     */
    public function getIsStaticOrDynDNS()
    {
        return $this->isStaticOrDynDNS;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Node
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return Node
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set dateTimeReg
     *
     * @param \DateTime $dateTimeReg
     *
     * @return Node
     */
    public function setDateTimeReg($dateTimeReg)
    {
        $this->dateTimeReg = $dateTimeReg;

        return $this;
    }

    /**
     * Get dateTimeReg
     *
     * @return \DateTime
     */
    public function getDateTimeReg()
    {
        return $this->dateTimeReg;
    }

    /**
     * Set version
     *
     * @param integer $version
     *
     * @return Node
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set seq
     *
     * @param integer $seq
     *
     * @return Node
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;

        return $this;
    }

    /**
     * Get seq
     *
     * @return int
     */
    public function getSeq()
    {
        return $this->seq;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateSeq(LifecycleEventArgs $args) {
        $args->getEntityManager()->beginTransaction();
        $repo = $args->getEntityManager()->getRepository('AppBundle:Sequence');

        $sequence = $repo->find('sync');
        $this->seq = $sequence->getValue();

        $sequence->setValue($this->seq + 1);
        $args->getEntityManager()->flush($sequence);

        $args->getEntityManager()->commit();
    }

    /**
    * @ORM\PreRemove
    */
    public function preDelete(LifecycleEventArgs $args){
        $args->getEntityManager()->beginTransaction();
        $repoSeq = $args->getEntityManager()->getRepository('AppBundle:Sequence');

        $sequence = $repoSeq->find('sync');
        $seq = $sequence->getValue();

        $sequence->setValue($seq + 1);
        $args->getEntityManager()->flush($sequence);

        //Insert Delete row
        /*
        $repoDel = $args->getEntityManager()->getRepository('VrmAdminBundle:SoundSchekelDelete');
        $delete = new SoundSchekelDelete();
        $delete->setSeq($seq);
        $delete->setSid($this->getSid());
        $args->getEntityManager()->persist($delete);

        $args->getEntityManager()->flush();
        */

        $args->getEntityManager()->commit();
    }
}

