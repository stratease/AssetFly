<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
use Symfony\Component\Process\ProcessBuilder;
abstract class ConsoleFilterBase implements FilterInterface
{
    use ConfiguratorTrait;
    /**
     * @var string The cli args
     */
    protected $options; 
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
   
    public function __construct(array $options = [])
    {
        $this->loadOptions($options);
    }
     /**
     * @param ProcessBuilder $value The symfony process builder that manages this filer.
     * @return $this
     */
    public function setProcessBuilder($value)
    {
        $this->processBuilder = $value;

        return $this;
    }

    /**
     * @return ProcessBuilder The symfony process builder that manages this filer.
     */
    public function getProcessBuilder()
    {
        // default to options passed if we weren't explicitly defined..
        if(!$this->processBuilder) {
            $this->setProcessBuilder(new ProcessBuilder(array_merge([$this->getShellCmd()], $this->getOptions())));
        }
        // mark our timeout
        $this->processBuilder->setTimeout($this->getTimeout());
        return $this->processBuilder;
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
    public function setOptions($args)
    {
        // stringify it...
        if(is_array($args)) {
            $args = implode(" ", $args);
        }
        $this->options = $args;
        
        return $this;
    }
}