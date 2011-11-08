<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class PlainText
{

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------

    public function getLoremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator();
        $number_of_words_to_lorem_ipssum = rand(5, 10);
        $entry = new self;
        $entry->setContent(trim($lorem_ipsum->getContent($number_of_words_to_lorem_ipssum, 'txt')));
        return $entry;
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

