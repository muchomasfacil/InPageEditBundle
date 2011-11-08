<?php

namespace MuchoMasFacil\InPageEditBundle\Util;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

        /*$custom = $mmf_iew;
        print_r($custom);
        $custom = $forUrlEncoder->encode($custom);
        print_r($custom);
        $custom = $forUrlEncoder->decode($custom);
        print_r($custom);*/


/**
 * Encodes data to transmit in url
 *
 * @author Alvaro Marcos <alvaro@muchomasfacil.com>
 */
class UrlSafeEncoder implements EncoderInterface, DecoderInterface
{

    private $transformations = array(
                                     '.' => '-d-',
                                     '~' => '-t-',
                                     '%' => '-p-',
                                    );
    /**
     * {@inheritdoc}
     */
    public function encode ($data, $format = '')
    {
        $return = $data;
        $return = rawurlencode(gzdeflate(serialize($data)));
        foreach ($this->transformations as $key => $val){
            $return = str_replace ($key, $val, $return);
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function decode ($data, $format = '')
    {
        $return = $data;
        foreach ($this->transformations as $key => $val){
            $return = str_replace ($val, $key, $return);
        }
        $return = unserialize(gzinflate(rawurldecode($return)));
        return $return;
    }
}

