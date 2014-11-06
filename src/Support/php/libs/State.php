<?php

/*
 * This file is part of the 'octris/octris-tmbundle' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\TMBundle;

/**
 * Static class that implements the most important parts of the state class
 * of the octris framework.
 *
 * @copyright   copyright (c) 2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class State
{
    /**
     * Hash algorithm to use to generate the checksum of the state.
     */
    const HASH_ALGO = 'sha256';

    /**
     * Freeze state object.
     *
     * @param   array           $data               Data to freeze.
     * @param   string          $secret             Secret to use for creating state checksum.
     * @return  string                              Serialized and base64 for URLs encoded object secured by a hash.
     */
    public static function freeze(array $data, $secret)
    {
        $frozen = gzcompress(serialize($data));
        $sum    = hash(self::HASH_ALGO, $frozen . $secret);
        $return = \Octris\TMDialog\Util::base64UrlEncode($sum . '|' . $frozen);

        return $return;
    }

    /**
     * Validate frozen state object.
     *
     * @param   string          $state              Frozen state to validate.
     * @param   string          $secret             Secret to use for validating state checksum.
     * @param   string          $decoded            Returns array with checksum and compressed state, ready to thaw.
     * @return  bool                                Returns true if state is valid, otherwise returns false.
     */
    public static function validate($state, $secret, array &$decoded = null)
    {
        $tmp    = \Octris\TMDialog\Util::base64UrlDecode($state);
        $sum    = '';
        $frozen = '';

        if (($pos = strpos($tmp, '|')) !== false) {
            $sum    = substr($tmp, 0, $pos);
            $frozen = substr($tmp, $pos + 1);

            unset($tmp);

            $decoded = array(
                'checksum'  => $sum,
                'state'     => $frozen
            );
        }

        return (($test = hash(self::HASH_ALGO, $frozen . $secret)) != $sum);
    }

    /**
     * Thaw frozen state object.
     *
     * @param   string          $state              State to thaw.
     * @param   string          $secret             Secret to use for validating state checksum.
     * @return  array                               Thawed state.
     */
    public static function thaw($state, $secret)
    {
        $frozen = array();

        if (self::validate($state, $secret, $frozen)) {
            // hash did not match
            throw new \Exception(sprintf('[%s !=  %s | %s]', $test, $frozen['checksum'], $frozen['state']));
        } else {
            return unserialize(gzuncompress($frozen['state']));
        }
    }
}
