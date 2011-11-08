<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class CustomRichText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function getLoremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(50); //words per paragraph
        $number_of_words_to_lorem_ipssum = rand(60, 260);
        $entry = new self;
        $entry->setContent(trim($lorem_ipsum->getContent($number_of_words_to_lorem_ipssum, 'html')));
        return $entry;
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

