<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupedSortedObjectCollection
 */
class GroupedSortedObjectCollection
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \stdClass
     */
    private $object;

    /**
     * @var string
     */
    private $ipe_handler;

    /**
     * @var integer
     */
    private $ipe_position;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set object
     *
     * @param \stdClass $object
     * @return GroupedSortedObjectCollection
     */
    public function setObject($object)
    {
        $this->object = $object;
    
        return $this;
    }

    /**
     * Get object
     *
     * @return \stdClass 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set ipe_handler
     *
     * @param string $ipeHandler
     * @return GroupedSortedObjectCollection
     */
    public function setIpeHandler($ipeHandler)
    {
        $this->ipe_handler = $ipeHandler;
    
        return $this;
    }

    /**
     * Get ipe_handler
     *
     * @return string 
     */
    public function getIpeHandler()
    {
        return $this->ipe_handler;
    }

    /**
     * Set ipe_position
     *
     * @param integer $ipePosition
     * @return GroupedSortedObjectCollection
     */
    public function setIpePosition($ipePosition)
    {
        $this->ipe_position = $ipePosition;
    
        return $this;
    }

    /**
     * Get ipe_position
     *
     * @return integer 
     */
    public function getIpePosition()
    {
        return $this->ipe_position;
    }
}
