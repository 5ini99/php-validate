<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-20
 * Time: 00:10
 */

namespace Inhere\Validate\Filter;

/**
 * Class UserFilters - user custom add global filters
 * @package Inhere\Validate\Filter
 */
final class UserFilters
{
    /** @var array user custom filters */
    private static $filters = [];

    /**
     * @param string   $name
     * @param callable $filter
     */
    public static function add(string $name, callable $filter)
    {
        if (!isset(self::$filters[$name])) {
            self::$filters[$name] = $filter;
        }
    }

    /**
     * @param string   $name
     * @param callable $filter
     */
    public static function set(string $name, callable $filter)
    {
        self::$filters[$name] = $filter;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$filters[$name]);
    }

    /**
     * @return array
     */
    public static function getFilters(): array
    {
        return self::$filters;
    }

    /**
     * @param array $filters
     */
    public static function addFilters(array $filters)
    {
        self::$filters = \array_merge(self::$filters, $filters);
    }

    /**
     * @param array $filters
     */
    public static function setFilters(array $filters)
    {
        self::$filters = $filters;
    }

    /**
     * @param string $name
     */
    public static function remove(string $name)
    {
        if (isset(self::$filters[$name])) {
            unset(self::$filters[$name]);
        }
    }

    /**
     * clear all filters
     */
    public static function removeAll()
    {
        self::$filters = [];
    }
}
