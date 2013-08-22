<?php

namespace MuchoMasFacil\InPageEditBundle\Faker;

use Symfony\Component\HttpFoundation\Request;

class Generator extends \Faker\Generator
{
    //adapted from Faker\Factory.php
    const DEFAULT_LOCALE = 'en_US';

    private static $defaultProviders = array('Person', 'Address', 'PhoneNumber', 'Company', 'Lorem', 'Internet', 'DateTime', 'Miscellaneous', 'UserAgent', 'Uuid', 'File', 'Color');

    private $doctrine = null;

    private $orm_doctrine_connection_name = null;


    public function initOrmDoctrineFaker($request_or_locale = self::DEFAULT_LOCALE, $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->create($request_or_locale);
    }

    public function setOrmDoctrineConnectionName($connection_name)
    {
        $this->orm_doctrine_connection_name = $connection_name;
    }

    public function getOrmDoctrineConnectionName()
    {
        return (is_null($this->orm_doctrine_connection_name))? $this->doctrine->getDefaultConnectionName() : $this->orm_doctrine_connection_name;
    }

    public function ORMDoctrinePopulate($entityName, $number = 1, $customColumnFormatters = array(), $customModifiers = array(), $generateId = false)
    {
        $em = $this->doctrine->getManager($this->getOrmDoctrineConnectionName());
        $populator = new \Faker\ORM\Doctrine\Populator($this, $em);
        $populator->addEntity($entityName, $number, $customColumnFormatters, $customModifiers, $generateId);

        return $populator->execute();
    }
    //it should be easy to prepare this class for
    // a initOrmPropelFaker
    // and a initOrmMandangoFaker
/*
    public function ORMDoctrinePopulateGroupedSortedMappedEntity($entityName, $number = 1, $ipe_handler)
    {
        $customColumnFormatters = array('ipe_handler' => $ipe_handler, 'ipe_position' => null);
        return $this->ORMDoctrinePopulate($entityName, $number, $customColumnFormatters);
    }
*/
    public function findOrFakeGroupedSortedMappedEntity($entityName, $ipe_handler, $number = 1, $fake_if_empty = false, $ipe_handler_field = 'ipe_handler', $ipe_position_field = 'ipe_position')
    {
        $repository = $this->doctrine->getRepository($entityName);
        $find_by = array($ipe_handler_field => $ipe_handler);
        $order_by = array('ipe_position' => 'ASC');
        $rs = $repository->findBy($find_by, $order_by);
        if (($fake_if_empty) && (empty($rs))) {
            $customColumnFormatters = $find_by;
            // next line ipe_position = null to avoid faker to act
            $customColumnFormatters['ipe_position'] = null;
            $this->ORMDoctrinePopulate($entityName, $number, $customColumnFormatters);
            $rs = $repository->findBy($find_by, $order_by);
        }
        return $rs;
    }

    public function findOrFakeSingleGroupedMappedEntity($entityName, $ipe_handler, $fake_if_empty = false, $ipe_handler_field = 'ipe_handler', $ipe_position_field = 'ipe_position')
    {
        $repository = $this->doctrine->getRepository($entityName);
        $find_by = array($ipe_handler_field => $ipe_handler);
        $rs = $repository->findOneBy($find_by);
        if (($fake_if_empty) && (empty($rs))) {
            $number = 1;
            $customColumnFormatters = $find_by;
            // next line ipe_position = null to avoid faker to act
            $customColumnFormatters['ipe_position'] = null;
            $this->ORMDoctrinePopulate($entityName, $number, $customColumnFormatters);
            $rs = $repository->findOneBy($find_by);
        }
        return $rs;
    }

    //adapted from Faker\Factory.php
    public function create($request_or_locale = self::DEFAULT_LOCALE)
    {
        $this->resetGenerator();
        if ($request_or_locale instanceof Request) {
            $locale = $request_or_locale->getLocale();
        }
        else {
            $locale = $request_or_locale;
        }

        foreach (static::$defaultProviders as $provider) {
            $providerClassName = self::getProviderClassname($provider, $locale);
            $this->addProvider(new $providerClassName($this));
        }
    }

    //cloned from Faker\Factory.php
    private static function getProviderClassname($provider, $locale = '')
    {
        if ($providerClass = self::findProviderClassname($provider, $locale)) {
            return $providerClass;
        }
        // fallback to default locale
        if ($providerClass = self::findProviderClassname($provider, static::DEFAULT_LOCALE)) {
            return $providerClass;
        }
        // fallback to no locale
        $providerClass = self::findProviderClassname($provider);
        if (class_exists($providerClass)) {
            return $providerClass;
        }
        throw new \InvalidArgumentException(sprintf('Unable to find provider "%s" with locale "%s"', $provider, $locale));
    }

    //cloned from Faker\Factory.php
    private static function findProviderClassname($provider, $locale = '')
    {
        $providerClass = 'Faker\\' . ($locale ? sprintf('Provider\%s\%s', $locale, $provider) : sprintf('Provider\%s', $provider));
        if (class_exists($providerClass, true)) {
            return $providerClass;
        }
    }

    private function resetGenerator()
    {
        $this->providers = array();
        $this->formatters = array();
    }


}