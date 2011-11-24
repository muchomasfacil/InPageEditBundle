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


    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(50); //words per paragraph
        $this->setUrl('http://www.muchomasfacil.com/images/logo.png');
        $this->setLabel(trim($lorem_ipsum->getContent(rand(1, 4), 'txt', false)));
        $this->setLink('http://www.muchomasfacil.com');
    }

    public function __toString()
    {
        return $this->getLabel() ;
    }
}
