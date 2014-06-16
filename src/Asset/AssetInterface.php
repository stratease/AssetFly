<?php
namespace stratease\AssetFly\Asset;
interface AssetInterface
{
     /**
     * @param string $sourceFile Absolute path to source file
     * @param array $options
     */
    public function __construct($sourceFile, array $options = []);
    /**
     * Are we a precompiler like Sass, Less, Coffeescript etc.. ?
     * @return bool
     */
    public static function isPrecompiler();
    



    /**
     * @return mixed Define which file type we are
     */
    public function getFileType();
}