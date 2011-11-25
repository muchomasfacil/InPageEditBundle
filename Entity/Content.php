<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Yaml\Yaml;

class Content
{
//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function getEditorRolesAsArray()
    {
        return explode(',', preg_replace('/\s*/m', '', $this->getEditorRoles() ));
    }
//------------------------------------------------------------------------------

    /**
     * @var string $handler
     */
    private $handler;

    /**
     * @var string $content_entity_class
     */
    private $content_entity_class;

    /**
     * @var text $editor_roles
     */
    private $editor_roles;

    /**
     * @var boolean $is_collection
     */
    private $is_collection;

    /**
     * @var integer $max_collection_length
     */
    private $max_collection_length;

    /**
     * @var array $content
     */
    private $content;


    /**
     * Set handler
     *
     * @param string $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Get handler
     *
     * @return string 
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set content_entity_class
     *
     * @param string $contentEntityClass
     */
    public function setContentEntityClass($contentEntityClass)
    {
        $this->content_entity_class = $contentEntityClass;
    }

    /**
     * Get content_entity_class
     *
     * @return string 
     */
    public function getContentEntityClass()
    {
        return $this->content_entity_class;
    }

    /**
     * Set editor_roles
     *
     * @param text $editorRoles
     */
    public function setEditorRoles($editorRoles)
    {
        $this->editor_roles = $editorRoles;
    }

    /**
     * Get editor_roles
     *
     * @return text 
     */
    public function getEditorRoles()
    {
        return $this->editor_roles;
    }

    /**
     * Set is_collection
     *
     * @param boolean $isCollection
     */
    public function setIsCollection($isCollection)
    {
        $this->is_collection = $isCollection;
    }

    /**
     * Get is_collection
     *
     * @return boolean 
     */
    public function getIsCollection()
    {
        return $this->is_collection;
    }

    /**
     * Set max_collection_length
     *
     * @param integer $maxCollectionLength
     */
    public function setMaxCollectionLength($maxCollectionLength)
    {
        $this->max_collection_length = $maxCollectionLength;
    }

    /**
     * Get max_collection_length
     *
     * @return integer 
     */
    public function getMaxCollectionLength()
    {
        return $this->max_collection_length;
    }

    /**
     * Set content
     *
     * @param array $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return array 
     */
    public function getContent()
    {
        return $this->content;
    }
}
