<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class MultiLinePlainText
{

    private $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator();
        $this->setContent(trim($lorem_ipsum->getContent(rand(20, 40), 'txt')));
    }

    public function __toString()
    {
        $cut_in = 60;
        return (strlen($this->content) > $cut_in) ? substr($this->content, 0, $cut_in).'...' : $this->content ;
    }


}

