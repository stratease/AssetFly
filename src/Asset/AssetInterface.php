<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\AssetLoader;
interface AssetInterface
{
    /**
     * @param string $sourceFile Absolute path to source file
     * @param array $options
     */
    public function __construct($sourceFile, array $options = []);

    



    /**
     * @return mixed Define which file type we are
     */
    public function getFileType();
}