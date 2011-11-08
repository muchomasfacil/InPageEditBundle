<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class AdvancedImage
{

    private $url;

    private $label;

    private $link;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }


    public function getLoremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(50); //words per paragraph
        $number_of_words_to_lorem_ipssum = rand(1, 4);
        $entry = new self;
        $entry->setUrl('http://www.muchomasfacil.com/images/logo.png');
        $entry->setLabel(trim($lorem_ipsum->getContent($number_of_words_to_lorem_ipssum, 'txt', false)));
        $entry->setLink('http://www.muchomasfacil.com');
        return $entry;
    }

    public function __toString()
    {
        return $this->getLabel() ;
    }
}
