<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class RichText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator();
        $this->setContent(trim($lorem_ipsum->getContent(rand(2, 10), 'html')));
    }


    public function __toString()
    {
        $cut_in = 60;
        $content = strip_tags($this->getContent());
        return (strlen($content) > $cut_in) ? substr($content, 0, $cut_in).'...' : $content ;
    }
//------------------------------------------------------------------------------
    private $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

}

