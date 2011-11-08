<?php

namespace MuchoMasFacil\InPageEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Yaml\Yaml;

class Content
{
//------------------------------------------------------------------------------
//Custom code
//------------------------------------------------------------------------------
    public function getParsedParams()
    {
      return Yaml::parse($this->getYmlParams());
    }

    public function getEditorRolesAsArray()
    {
        return explode(',', preg_replace('/\s*/m', '', $this->getEditorRoles() ));
    }

    public function getAdminRolesAsArray()
    {
        return explode(',', preg_replace('/\s*/m', '', $this->getAdminRoles() ));
    }
//------------------------------------------------------------------------------

    /**
     * @var string $handler
     */
    private $handler;

    /**
     * @var text $yml_params
     */
    private $yml_params;

    /**
     * @var text $editor_roles
     */
    private $editor_roles;

    /**
     * @var text $admin_roles
     */
    private $admin_roles;

    /**
     * @var string $entity_class
     */
    private $entity_class;

    /**
     * @var string $render_template
     */
    private $render_template;

    /**
     * @var boolean $is_collection
     */
    private $is_collection;

    /**
     * @var integer $collection_length
     */
    private $collection_length;


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
     * Set yml_params
     *
     * @param text $ymlParams
     */
    public function setYmlParams($ymlParams)
    {
        $this->yml_params = $ymlParams;
    }

    /**
     * Get yml_params
     *
     * @return text
     */
    public function getYmlParams()
    {
        return $this->yml_params;
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
     * Set admin_roles
     *
     * @param text $adminRoles
     */
    public function setAdminRoles($adminRoles)
    {
        $this->admin_roles = $adminRoles;
    }

    /**
     * Get admin_roles
     *
     * @return text
     */
    public function getAdminRoles()
    {
        return $this->admin_roles;
    }


    /**
     * Set entity_class
     *
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entity_class = $entityClass;
    }

    /**
     * Get entity_class
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entity_class;
    }

    /**
     * Set render_template
     *
     * @param string $renderTemplate
     */
    public function setRenderTemplate($renderTemplate)
    {
        $this->render_template = $renderTemplate;
    }

    /**
     * Get render_template
     *
     * @return string
     */
    public function getRenderTemplate()
    {
        return $this->render_template;
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
     * Set collection_length
     *
     * @param integer $collectionLength
     */
    public function setCollectionLength($collectionLength)
    {
        $this->collection_length = $collectionLength;
    }

    /**
     * Get collection_length
     *
     * @return integer
     */
    public function getCollectionLength()
    {
        return $this->collection_length;
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
