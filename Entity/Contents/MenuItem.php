<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

use Symfony\Component\Yaml\Yaml;

class MenuItem
{

    private $uid;

    private $label;

    private $uri;
    
    private $yml_attributes;
    
    private $yml_link_attributes;
    
    private $yml_label_attributes;

//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------    
    private function slugify($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

      // trim
      $text = trim($text, '-');

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // lowercase
      $text = strtolower($text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      if (empty($text))
      {
        return 'n-a';
      }

      return $text;
    }

    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(); //words per paragraph
        $this->setUid(uniqid ('menu-item-'));
        $this->setLabel(trim($lorem_ipsum->getContent(rand(1, 3), 'txt', false)));
        $this->setUri($this->slugify($this->getLabel()));
        $this->setYmlAttributes('{}');
        $this->setYmlLinkAttributes('{}');
        $this->setYmlLabelAttributes('{}');
    }

    public function __toString()
    {
        $cut_in = 60;
        $string_to_cut = $this->label;
        return (strlen($string_to_cut) > $cut_in) ? substr($string_to_cut, 0, $cut_in).'...' : $string_to_cut ;
    }

    public function getParsedAttributes()
    {
        try{
            return Yaml::parse($this->getYmlAttributes());    
        }
        catch (Exception $e) {
            return array('error' => 'parsing yml_attributes');
        }     
    }
    
    public function getParsedLinkAttributes()
    {               
      return Yaml::parse($this->getYmlLinkAttributes());
    }
    
    public function getParsedLabelAttributes()
    {
      return Yaml::parse($this->getYmlLabelAttributes());
    }
    
//------------------------------------------------------------------------------

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setYmlAttributes($yml_attributes)
    {
        $this->yml_attributes = $yml_attributes;
    }

    public function getYmlAttributes()
    {
        return $this->yml_attributes;
    }
    
    public function setYmlLinkAttributes($yml_link_attributes)
    {
        $this->yml_link_attributes = $yml_link_attributes;
    }

    public function getYmlLinkAttributes()
    {
        return $this->yml_link_attributes;
    }
    
    public function setYmlLabelAttributes($yml_label_attributes)
    {
        $this->yml_label_attributes = $yml_label_attributes;
    }

    public function getYmlLabelAttributes()
    {
        return $this->yml_label_attributes;
    }   

}

