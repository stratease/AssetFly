<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Asset\AssetBase;
class TextFile extends AssetBase
{
    public function dump()
    {
        return file_get_contents($this->getSourceFile());
    }
}