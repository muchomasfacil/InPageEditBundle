<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupedSortedMappedString
 */
class GroupedSortedMappedString
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $string;

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
     * Set string
     *
     * @param string $string
     * @return GroupedSortedMappedString
     */
    public function setString($string)
    {
        $this->string = $string;
    
        return $this;
    }

    /**
     * Get string
     *
     * @return string 
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Set ipe_handler
     *
     * @param string $ipeHandler
     * @return GroupedSortedMappedString
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
     * @return GroupedSortedMappedString
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
