<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class RichTextHeaderAndCustomRichText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(50); //words per paragraph
        $this->setContent(trim($lorem_ipsum->getContent(rand(1, 10), 'html')));        
        $this->setContent(trim($lorem_ipsum->getContent(rand(60, 2600), 'html')));
    }

    public function __toString()
    {
        $cut_in = 60;
        $content = strip_tags($this->getHeader());
        return (strlen($content) > $cut_in) ? substr($content, 0, $cut_in).'...' : $content ;
    }
//------------------------------------------------------------------------------

    private $header;

    private $content;

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }


    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}

