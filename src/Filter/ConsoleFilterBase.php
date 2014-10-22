<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\Filter\FilterBase;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\AssetLoader;
use Symfony\Component\Process\ProcessBuilder;
abstract class ConsoleFilterBase extends FilterBase
{
    /**
     * @var string The cli args
     */
    protected $options = [];
    protected $timeout = 30;
    protected $processBuilder;
    public function getOptions()
    {
        return $this->options;
    }
    public function setTimeout($time)
    {
        $this->timeout = $time;
        return $this->timeout;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }


    /**
     * @return ProcessBuilder The symfony process builder that manages this filer.
     */
    public function getProcessBuilder()
    {
        $pb = new ProcessBuilder(array_merge([$this->getShellCmd()], $this->getOptions()));

        // mark our timeout
        $pb->setTimeout($this->getTimeout());

        // if debug add special stuff..
        if(AssetLoader::getDebug()) {
            if($callable = $this->getIfDebugCallable()) {
                call_user_func($callable, $pb);
            }
        }

        return $pb;
    }
    public function setShellCmd($cmd)
    {
        $this->shellCmd = $cmd;
        return $this;
    }
    public function getShellCmd()
    {
        return $this->shellCmd;
    }
}