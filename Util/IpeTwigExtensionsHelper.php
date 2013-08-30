<?php
namespace MuchoMasFacil\InPageEditBundle\Util;

class IpeTwigExtensionsHelper
{     
    public static function  getTitleHandler($requestUri, $baseUrl)
    {
        echo '---'. md5(str_replace($baseUrl, '', $requestUri)) . '__title_tag';
        return md5(str_replace($baseUrl, '', $requestUri)) . '__title_tag';

    }

    public static function  getTitleFindByParams($requestUri, $baseUrl, $collection_ipe_handler_field = 'ipe_handler')
    {        
        return  array($collection_ipe_handler_field => self::getTitleHandler($requestUri, $baseUrl));
    }

    public static function  getTitleFindParams($requestUri, $baseUrl, $collection_ipe_handler_field = 'ipe_handler')
    {
        return array(
                'entity_class' => 'MuchoMasFacilInPageEditBundle:GroupedSortedMappedString',
                'find_by' =>  self::getTitleFindByParams($requestUri, $baseUrl, $collection_ipe_handler_field),
                'order_by' => null,
                'is_collection' => false ,
            );
    }

    public static function createHashForObject($object)
    {
        return md5(serialize($object));
    }
}