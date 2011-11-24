<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class PlainText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------

    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator();
        $this->setContent(trim($lorem_ipsum->getContent(rand(5, 10), 'txt')));
    }

    public function __toString()
    {
        $cut_in = 60;
        return (strlen($this->content) > $cut_in) ? substr($this->content, 0, $cut_in).'...' : $this->content ;
    }
//------------------------------------------------------------------------------

    /**
     * @var string $content
     */
    private $content;

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
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

