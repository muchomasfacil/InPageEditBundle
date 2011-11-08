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

    public function getLoremIpsum($count = 1, $number_of_words_to_lorem_ipssum = null)
    {
        $return = array();
        $lorem_ipsum = new LoremIpsumGenerator();
        if ((!is_int($count)) || ($count < 0)) {
            $count = 1;
        }
        if ((!is_int($number_of_words_to_lorem_ipssum)) || ($number_of_words_to_lorem_ipssum < 0)) {
            $number_of_words_to_lorem_ipssum = rand(20, 40);
        }
        for ($i = 0; $i < $count; $i++)
        {
            $entry = new self;
            $entry->setContent(trim($lorem_ipsum->getContent($number_of_words_to_lorem_ipssum, 'txt')));
            $return[] = $entry;
        }
        return $return;
    }

    public function __toString()
    {
        $cut_in = 60;
        return (strlen($this->content) > $cut_in) ? substr($this->content, 0, $cut_in).'...' : $this->content ;
    }


}

