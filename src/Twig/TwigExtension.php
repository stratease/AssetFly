<?php
namespace stratease\AssetFly\Twig;
use stratease\AssetFly\AssetLoader;
use Phive\Twig\Extensions\Deferred\DeferredExtension;
use stratease\AssetFly\Asset\TextFile;
class TwigExtension extends \Twig_Extension
{
    protected $assetLoader;
    public function __construct(\Twig_Environment $environment, AssetLoader $assetLoader)
    {
        // lets just add deferred
        $environment->addExtension(new DeferredExtension());

        $this->assetLoader = $assetLoader;
    }
    public function getFunctions()
    {
        $funcs = [];
        // this will map to our predefined filters dynamically... flyAddSass, flyAddMyCustomSass etc..
        $funcs[] = new \Twig_SimpleFunction('assetfly_add_*', [$this, 'addAsset']);
        // this fetches urls based on filter group
        $funcs[] = new \Twig_SimpleFunction('assetfly_get_urls', [$this, 'getAssetUrls']);
        return $funcs;
    }
    public function addAsset($filterGroup, $webFile)
    {
        $asset = new TextFile($this->assetLoader->getWebDirectory().'/'.$webFile);
        $this->assetLoader->addAsset($filterGroup, $asset);
    }
    public function getAssetUrls($filterGroup)
    {
        return $this->assetLoader->getAssetUrls($filterGroup);        
    }
    public function getName()
    {
        return 'assetfly';
    }
}