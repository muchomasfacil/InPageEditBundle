<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class AdvancedImage
{

    private $img_url;

    private $label;
    
    private $content;

    private $link;

    public function setImgUrl($img_url)
    {
        $this->img_url = $img_url;
    }

    public function getImgUrl()
    {
        return $this->img_url;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
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
        $this->setImgUrl('http://www.muchomasfacil.com/images/logo.png');
        $this->setLabel(trim($lorem_ipsum->getContent(rand(1, 4), 'txt', false)));
        $this->setContent(trim($lorem_ipsum->getContent(rand(5, 10), 'html', false)));
        $this->setLink('http://www.muchomasfacil.com');
    }

    public function __toString()
    {
        return $this->getLabel() ;
    }
}
