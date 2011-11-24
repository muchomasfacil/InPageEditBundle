<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use MuchoMasFacil\InPageEditBundle\Util\LoremIpsumGenerator;

class Foo
{
    public function loremIpsum()
    {
        $lorem_ipsum = new LoremIpsumGenerator(20); //words per paragraph
        $this->setText(trim($lorem_ipsum->getContent(rand(4,10), 'txt', false)));
        $this->setMultiLineText(trim($lorem_ipsum->getContent(rand(21,42), 'txt', false)));
    }
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var string $multi_line_text
     */
    private $multi_line_text;

    /**
     * @var text $file_list
     */
    private $file_list;

    /**
     * @var string $file
     */
    private $file;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set multi_line_text
     *
     * @param string $multiLineText
     */
    public function setMultiLineText($multiLineText)
    {
        $this->multi_line_text = $multiLineText;
    }

    /**
     * Get multi_line_text
     *
     * @return string
     */
    public function getMultiLineText()
    {
        return $this->multi_line_text;
    }

    /**
     * Set file_list
     *
     * @param text $fileList
     */
    public function setFileList($fileList)
    {
        $this->file_list = $fileList;
    }

    /**
     * Get file_list
     *
     * @return text
     */
    public function getFileList()
    {
        return $this->file_list;
    }

    /**
     * Set file
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}

