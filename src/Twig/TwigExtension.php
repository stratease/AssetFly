<?php
namespace stratease\AssetFly\Twig;
use stratease\AssetFly\AssetLoader;
class TwigExtension extends Twig_Extension
{
    protected $assetLoader;
    public function __construct(AssetLoader $assetLoader)
    {
        $this->assetLoader = $assetLoader;
    }
    public function initRuntime(Twig_Environment $environment)
    {
        // lets just add deferred
        $environment->addExtension(new Phive\Twig\Extensions\Deferred\DeferredExtension());
        
        // lets add our magic joo joo..
        
        // this will map to our predefined filters dynamically... flyAddSass, flyAddMyCustomSass etc..
        $function = new Twig_SimpleFunction('assetfly_add_*', [$this, 'addAsset']);
        $environment->addFunction($function);
    }
    
    public function getFunctions()
    {
        return ['assetfly_add_*'];
    }
    public function addAsset($filterName, $arguments)
    {
        //
        $this->assetLoader->add
    }
    public function getName()
    {
        return 'assetfly';
    }
}