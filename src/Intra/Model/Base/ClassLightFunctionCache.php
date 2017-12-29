<?php
namespace Intra\Model\Base;

/**
 * 모델 객체에대해 pk 기준으로 캐시를 보장하기위한 시스템
 */
trait ClassLightFunctionCache
{
    private static $cacheHashesByKey = [];
    private static $cache = [];

    /**
     * @param $domain_primary_keys array|string
     * @param $function callable
     * @return mixed
     */
    protected static function cachingCallback($domain_primary_keys, $function)
    {
        $class = get_called_class();
        if (!is_array($domain_primary_keys)) {
            $domain_primary_keys = [$domain_primary_keys];
        }
        sort($domain_primary_keys);

        self::cacheInit($class);

        $cache = self::findCacheByKey($class, $domain_primary_keys);
        if ($cache !== null) {
            return $cache;
        }
        $function_result = $function();
        self::insertCache($class, $domain_primary_keys, $function_result);

        return $function_result;
    }

    private static function cacheInit($class)
    {
        if (!isset(self::$cacheHashesByKey[$class]) || !is_array(self::$cacheHashesByKey[$class])) {
            self::$cacheHashesByKey[$class] = [];
        }
        if (!isset(self::$cache[$class]) || !is_array(self::$cache[$class])) {
            self::$cache[$class] = [];
        }
    }

    private static function findCacheByKey($class, $domain_primary_keys)
    {
        $hash_raw = implode('##', $domain_primary_keys);
        $hash = md5($hash_raw);
        if (isset(self::$cache[$class][$hash])) {
            return self::$cache[$class][$hash];
        }

        return null;
    }

    private static function insertCache($class, $domain_primary_keys, $value)
    {
        $hash_raw = implode('##', $domain_primary_keys);
        $hash = md5($hash_raw);

        self::$cache[$class][$hash] = $value;

        if (!self::$cacheHashesByKey) {
            return;
        }

        foreach ($domain_primary_keys as $domain_primary_key) {
            self::$cacheHashesByKey[$class][$domain_primary_key][] = $hash;
        }
    }

    protected static function invalidateCache($domain_primary_keys)
    {
        $class = get_called_class();
        if (!is_array($domain_primary_keys)) {
            $domain_primary_keys = [$domain_primary_keys];
        }

        if (!self::$cacheHashesByKey) {
            return;
        }

        foreach ($domain_primary_keys as $domain_primary_key) {
            $hashes = self::$cacheHashesByKey[$class][$domain_primary_key];
            if (is_array($hashes)) {
                foreach ($hashes as $hash) {
                    unset(self::$cache[$class][$hash]);
                }
            }
            unset(self::$cacheHashesByKey[$class][$domain_primary_key]);
        }
    }
}
