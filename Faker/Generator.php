<?php

namespace MuchoMasFacil\InPageEditBundle\Faker;

use Symfony\Component\HttpFoundation\Request;

class Generator extends \Faker\Generator
{
    //adapted from Faker\Factory.php
    const DEFAULT_LOCALE = 'en_US';

    private static $defaultProviders = array('Person', 'Address', 'PhoneNumber', 'Company', 'Lorem', 'Internet', 'DateTime', 'Miscellaneous', 'UserAgent', 'Uuid', 'File', 'Color');

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

    public function ORMDoctrinePopulate(\Doctrine\ORM\EntityManager $em, $entityName, $number = 1, $customColumnFormatters = array(), $customModifiers = array(), $generateId = false)
    {
        $populator = new \Faker\ORM\Doctrine\Populator($this, $em);
        $populator->addEntity($entityName, $number, $customColumnFormatters, $customModifiers, $generateId);

        return $populator->execute();
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