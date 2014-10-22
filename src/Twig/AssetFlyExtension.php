<?php
namespace stratease\AssetFly\Twig;
use stratease\AssetFly\AssetLoader;

use stratease\AssetFly\Asset\Assets\TextFile;
class AssetFlyExtension extends \Twig_Extension
{
    protected $assetLoader;

    public function getFunctions()
    {
        $funcs = [];
        // this will map to our predefined filters dynamically... flyAddSass, flyAddMyCustomSass etc..
        $funcs[] = new \Twig_SimpleFunction('assetfly_add_*', [$this, 'addAsset']);
        // this fetches urls based on filter group
        $funcs[] = new \Twig_SimpleFunction('assetfly_get_urls', [$this, 'getAssetUrls']);
        return $funcs;
    }
    public function addAsset($filterGroup, $webFile, $outputGroup)
    {
        $asset = new TextFile(AssetLoader::getWebDirectory().DIRECTORY_SEPARATOR.$webFile);
        
        AssetLoader::addAsset($filterGroup, $outputGroup, $asset);
    }
    public function getAssetUrls($outputGroup)
    {
        return AssetLoader::getAssetUrls($outputGroup);
    }
    public function getName()
    {
        return 'assetfly';
    }
}