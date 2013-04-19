<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 */
class Content
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ipe_handler;

    /**
     * @var integer
     */
    private $ipe_position;

    /**
     * @var string
     */
    private $header;

    /**
     * @var string
     */
    private $content;


    /**
     * Get id
     *
     * @return integer 
     */
    public function __toString()
    {
        return $this->header;
    }

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
     * Set ipe_handler
     *
     * @param string $ipeHandler
     * @return Content
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
     * @return Content
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

    /**
     * Set header
     *
     * @param string $header
     * @return Content
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return string 
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Content
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
}
