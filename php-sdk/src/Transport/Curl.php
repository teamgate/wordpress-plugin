<?php

namespace Teamgate\Transport;

use Teamgate\Exception\TransportException;

class Curl implements Transport
{
    private $_instance;
    
    private $_baseUrl;
    
    private $_headers = array();
    
    public function __construct($baseUrl, $headers = array()) 
    {
        $this->_baseUrl = $baseUrl;
        foreach ($headers as $key => $value) {
			$this->_headers[] = $key . ': ' . $value;
		}
    }
    
    public function request($url, $method, $arguments = array())
    {
        $query = http_build_query($arguments);
        
        if ($method == Transport::GET && !empty($query)) {
            $url = $url . '?' . $query;
        }
            
        $this->_instance = curl_init($this->_baseUrl . $url);
        
        curl_setopt($this->_instance, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        
        curl_setopt($this->_instance, CURLOPT_HTTPHEADER, $this->_headers);
        
        curl_setopt($this->_instance, CURLOPT_USERAGENT, 'Teamgate-API-SDK/PHP');
        
        if ($method === Transport::POST || $method === Transport::PUT) {
            curl_setopt($this->_instance, CURLOPT_POST, 1);
            curl_setopt($this->_instance, CURLOPT_POSTFIELDS, $query);
        }
        
        curl_setopt($this->_instance, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_instance, CURLOPT_HEADER, 0);
        
        $responseBody = curl_exec($this->_instance);
        
        $responseCode = curl_getinfo($this->_instance, CURLINFO_HTTP_CODE);
        
        if ($responseCode !== 200) {
            throw new TransportException('Teamgate API cannot process the request.', $responseCode, $responseBody);
        }
        
        curl_close($this->_instance);

        return json_decode($responseBody);
    }
}