<?php
namespace stratease\AssetFly\Util;
trait ConfiguratorTrait {
    
    public function loadOptions(array $options)
    {
        foreach($options as $field => $val) {
            // cleanup field to our method naming convention
            $func = 'set'.ucwords($field);
            // check if we have a setter func
            $callable = [$this, $func];
            if(is_callable($callable)) {
                call_user_func_array($callable, [$val]);
            } else {
                throw new \Exception("Invalid property ".__CLASS__.":'".$field."'! Either no setter defined, or the property doesn't exist.");
            }
        }
    }
}