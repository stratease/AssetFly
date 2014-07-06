<?php
namespace stratease\AssetFly\Filter\Filters;
use stratease\AssetFly\Asset\AssetBase;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Filter\FilterBase;



class CssRelativeRewrite extends FilterBase
{
	
	
	/**
	 * Flag as precompiler so we are always run, since files get moved to new location regardless
	 */
    public static function isPrecompiler()
    {
        return true;
    }

	
    /**
     * @param AssetInterface $asset
     * @return AssetInterface
     * @throws \Exception
     */
    public function processAsset(AssetInterface $asset)
    {
        $content = $asset->getContent();
		
		$targetDir = $asset->getDumpDirectory();
		$sourceDir = str_replace($this->assetLoader->getWebDirectory(), '', dirname($asset->getSourcePath())); // strip web dir off
		
		// iterate and cleanup the relative paths
		$regexs = ['/url\((["\']?)(?P<url>.*?)(\\1)\)/',
						'/@import (?:url\()?(\'|"|)(?P<url>[^\'"\)\n\r]*)\1\)?;?/',
						'/src=(["\']?)(?P<url>.*?)\\1/'];
		foreach($regexs as $regex) {
			$content = preg_replace_callback($regex,
				function($matches) use ($sourceDir) {
					// if it's relative, just prepend with our source dir
					if(substr($matches['url'], 0, 1) != '/'
						&& substr($matches['url'], 0, 5) != 'http:'
						&& substr($matches['url'], 0, 5) != 'data:') {

						return str_replace($matches['url'], $sourceDir.'/'.$matches['url'], $matches[0]);
					}
					
					return $matches[0];
				}, $content);
		}
		
        return $asset->setContent($content);
    }
}