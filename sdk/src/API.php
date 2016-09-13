<?php

namespace Teamgate;

use Teamgate\Exception\ValidationException;

class API {

    private $_apiUrl = 'https://api.teamgate.com/v4/';
    private $_apiKey;
    private $_authToken;
    private $_options = array('transport' => 'curl');

    /**
     * @var Transport\Curl $_transport
     */
    private $_transport;

    /**
     * Create instance of \Teamgate\API
     *
     * @param array $options
     * @throws ValidationException
     */
    public function __construct($options)
    {
        if (!isset($options['apiKey']) || !isset($options['authToken'])) {
            throw new ValidationException('You need to provide apiKey and authToken in `options`');
        }
        $this->_apiKey = $options['apiKey'];
        $this->_authToken = $options['authToken'];

        if (isset($options['transport']) && $options['transport'] instanceof Transport) {
            $this->_transport = $options['transport'];
        }

        $this->_options = array_merge($this->_options, $options);
    }
    
    
    /**
     * Call resource by name from Teamgate API
     *
     * @param string $name
     * @throws ValidationException
     * @return \StdClass
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        $className = '\\' . __NAMESPACE__ . '\\Model\\' . ucfirst($name);
        if (class_exists($className)) {
            return new $className($this->_getTransport());
        }
        throw new ValidationException('The specified model does not exist.');
    }

    /**
     * Get option by name
     *
     * @param string $name
     * @return mixed|null
     */
    private function getOption($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    /**
     * Get transport, default transport is curl
     *
     * @return Transport\Transport|Transport\Curl
     */
    protected function _getTransport()
    {
        if (!($this->_transport instanceof Transport\Transport)) {
            $className = __NAMESPACE__ . '\\Transport\\' . ucfirst($this->getOption('transport'));
            $this->_transport = new $className(
                $this->_apiUrl, 
                [
                    'X-App-Key' => $this->_apiKey,
                    'X-Auth-Token' => $this->_authToken
                ]);
        }
        return $this->_transport;
    }

}