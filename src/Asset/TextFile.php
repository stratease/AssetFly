<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Asset\AssetBase;
class TextFile extends AssetBase
{
	protected $content;
   
    public function setContent($content)
    {
    	$this->content = $content;

    	return $this;
    }


    public function getContent()
    {
    	if(!$this->content) {

    		return file_get_contents($this->getSourcePath());
    	} else {
	
	    	return $this->content;
	    }
    }
}