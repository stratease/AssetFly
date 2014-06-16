<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Filter\ConsoleFilterBase;



class UglifyCss extends ConsoleFilterBase
{
	protected $shellCmd = 'uglifycss';
	

    public static function isPrecompiler()
    {
        return false;
    }
    /**
     * @param AssetInterface $asset
     * @throws \Exception
     * @return AssetInterface The updated asset object
     */
    public function processAsset(AssetInterface $asset)
    {
    	//Usage: uglifycss [options] file1.css [file2.css [...]] > output
        $pb = $this->getProcessBuilder();

        // file input
        $pb->add($asset->getSourcePath());
        $proc = $pb->getProcess();
        $code = $proc->run();

        // process err?
        if ($code !== 0) {
            throw new \Exception(__METHOD__." failed to filter '".$asset->getSourcePath(). "' - ".substr($proc->getOutput(), 0, 100), E_USER_WARNING);
        }
        // update our asset w/ minified css
        $asset->setContent($proc->getOutput());

        return $asset;
    }




}