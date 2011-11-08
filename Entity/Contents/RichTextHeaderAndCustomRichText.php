<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class RichTextHeaderAndCustomRichText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function getLoremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(50); //words per paragraph
        $entry = new self;
        $entry->setHeader(trim($lorem_ipsum->getContent(rand(1, 10), 'html')));
        $entry->setContent(trim($lorem_ipsum->getContent(rand(60, 260), 'html')));
        return $entry;
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

