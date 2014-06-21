<?php
/**
 * Created by PhpStorm.
 * User: edaniels
 * Date: 6/13/14
 * Time: 1:48 PM
 */

namespace stratease\AssetFly\Filter;


interface FilterInterface {

     /**
     * Are we a precompiler like Sass, Less, Coffeescript etc.. ?
     * @return bool
     */
    public static function isPrecompiler();
} 