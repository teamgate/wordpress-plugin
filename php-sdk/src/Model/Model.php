<?php

namespace Teamgate\Model;

class Model 
{
    private $_transport;
    
    public function __construct($transport) 
    {
        $this->_transport = $transport;
    }
    
    public function get($arguments = []) 
    {
        if (is_array($arguments)) {
            return $this->_transport->request(strtolower($this->_getShortClassName()), 'get', $arguments);
        }
        return $this->_transport->request(strtolower($this->_getShortClassName()) . '/' . (int) $arguments, 'get');
    }
    
    public function create($arguments = [])
    {
        return $this->_transport->request(strtolower($this->_getShortClassName()), 'post', $arguments);
    }
    
    public function update($id, $arguments = [])
    {
        return $this->_transport->request(strtolower($this->_getShortClassName()) . '/' . (int) $id, 'put', $arguments);
    }
    
    public function delete($id)
    {
        return $this->_transport->request(strtolower($this->_getShortClassName()) . '/' . (int) $id, 'delete');
    }
    
    protected function _getShortClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}