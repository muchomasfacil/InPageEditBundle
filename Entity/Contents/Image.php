<?php

namespace MuchoMasFacil\InPageEditBundle\Entity\Contents;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class Image
{

    private $file_list;

    public function setFileList($file_list)
    {
        $this->file_list = $file_list;
    }

    public function getFileList()
    {
        return $this->file_list;
    }

    public function getFileListAsArray()
    {
        return explode(',', preg_replace('/\s*/m', '', $this->getFileList()));
    }

    public function loremIpsum()
    {
        $this->setFileList('http://www.muchomasfacil.com/images/logo.png, http://www.muchomasfacil.com/images/logo.png');
    }

    public function __toString()
    {
        $cut_in = 60;
        $content = strip_tags($this->getFileList());
        return (strlen($content) > $cut_in) ? substr($content, 0, $cut_in).'...' : $content ;

    }
}
