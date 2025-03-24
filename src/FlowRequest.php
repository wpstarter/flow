<?php

namespace WpStarter\Flow;

class FlowRequest implements \ArrayAccess
{
    public string $channel='public';
    protected $idResolver = 'id';
    protected $messageResolver = 'message';
    protected $data = [];
    protected $resolved = [];

    protected $arguments = [];

    protected $argumentNames=[];
    protected $argumentsParsed=false;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function withIdResolver(\Closure $resolver)
    {
        $this->idResolver = $resolver;
        return $this;
    }

    public function withMessageResolver(\Closure $resolver)
    {
        $this->messageResolver = $resolver;
        return $this;
    }

    /**
     * Get an attribute from the fluent instance.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getIdentifier()
    {
        if (!isset($this->resolved['identifier'])) {
            $idResolver = $this->idResolver;
            if ($idResolver instanceof \Closure) {
                $this->resolved['identifier'] = $idResolver($this);
            } else {
                $this->resolved['identifier'] = $this->get($idResolver);
            }
        }
        return $this->resolved['identifier'] ?? '';
    }

    public function getMessage()
    {
        if (!isset($this->resolved['message'])) {
            $messageResolver = $this->messageResolver;
            if ($messageResolver instanceof \Closure) {
                $this->resolved['message'] = $messageResolver($this);
            } else {
                $this->resolved['message'] = $this->get($messageResolver);
            }
        }
        return $this->resolved['message'] ?? null;
    }

    public function parseArguments($argNames=[]){
        $this->arguments=[];
        $arguments = explode(' ', trim($this->getMessage()));
        //First arguments is command, shift it off
        array_shift($arguments);
        $options=$numericArguments=$queryArguments=[];
        foreach ($arguments as $argument){
            if(substr($argument,0,1)=='-' && strpos($argument,'=')!==false){
                $options[]=$argument;
            }elseif(strpos($argument,'=')!==false){
                $queryArguments[]=$argument;
            }else{
                $numericArguments[]=$argument;
            }
        }
        //Parse options from --k=v or --k="v" into array
        foreach($options as $option){
            $option=explode('=',ltrim($option,'-'));
            $this->arguments[$option[0]]=trim($option[1],'"');
        }
        foreach ($queryArguments as $argument){
            parse_str($argument,$parsedQueryArguments);
            $this->arguments=array_merge($this->arguments,$parsedQueryArguments);
        }
        foreach ($argNames as $argName){
            $this->arguments[$argName]=array_shift($numericArguments);
        }
        $numericArguments=array_values($numericArguments);
        foreach ($numericArguments as $index=>$value){
            $this->arguments[$index]=$value;
        }
        $this->argumentsParsed=true;
    }

    public function getArguments(){
        if(!$this->argumentsParsed){
            $this->parseArguments(is_array($this->argumentNames)?$this->argumentNames:explode(' ',$this->argumentNames));
        }
        return $this->arguments;
    }
    public function getArgument($name=null){
        $arguments=$this->getArguments();
        if($name===null){
            return array_shift($arguments);
        }
        return $arguments[$name]??null;
    }
    public function argument($name=null){
        return $this->getArgument($name);
    }


    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     *
     * @param string $method
     * @param array $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->data[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

}