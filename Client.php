<?php
namespace CB\PhpRest;
use CB\PhpRest\ClientException;
/**
 * Rest Client for PHP
 * 
 * @author Clint Berry
 * @version 0.1.0
 */
class Client {
    
    /**
     * The Url of the service API
     * @var string
     */
    protected $baseUrl;
    
    /**
     * The type of data expected (json or xml);
     * @var string
     */
    protected $type;
    
    /**
     * The cURL handler for all connections
     * @var resource
     */
    protected $ch;
    
    /**
     * Constants
     */
    const TYPE_JSON = 'json';
    const TYPE_XML = 'xml';
    
    /**
     * Set parameters and initialize cURL
     * 
     * @param string $url - The base URL of the API
     * @param string $type - The type of data (json or xml)
     */
    public function __construct($url, $type = 'json') {
        if (!function_exists('curl_init')) {
            throw new ClientException('cURL module not available! PhpRest requires cURL.');
        }
        //Set variables
        $this->baseUrl = $url;
        switch($type){
            case 'json':
                $this->type = self::TYPE_JSON;
                break;
            case 'xml':
                $this->type = self::TYPE_XML;
                break;
            default:
                $this->type = self::TYPE_JSON;
        }
        
        //Initialize Curl Resource
        $this->ch = curl_init();
    }
    
    /**
     * Close the cURL Session
     */
    public function __destruct() {
        curl_close($this->ch);
    }
    
    public function get($url){
        return $this->execute($url, 'GET');
    }
    
    public function put($url, $data){
        return $this->execute($url, 'PUT', $data);
    }
    
    public function post($url, $data){
        return $this->execute($url, 'POST', $data);
    }
    
    public function delete($url){
        return $this->execute($url, 'DELETE');
    }
    
    protected function prepareUrl($url){
        if (strncmp($url, $this->baseUrl, strlen($this->baseUrl)) != 0) {
            $url = $this->baseUrl . $url;
        }
        return $url;
    }
    
    protected function execute($url, $method = 'GET', $body = null, $expectedCode = 200){
        
        //Prepare the data for cURL
        $body = (is_array($body)) ? http_build_query($body) : $body;
        
        //Format URL correctly
        $fullUrl = $this->prepareUrl($url);
        //Set URL
        curl_setopt($this->ch, CURLOPT_URL, $fullUrl);
        
        //Set Headers
        if($this->type == self::TYPE_JSON) {
            $headers = array('Content-Type: application/x-www-form-urlencoded',
                             'Accept: application/json');
        }
        
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, '/dev/null'); // enables cookies
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        
        if($body != null){
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        }
        
        $response = curl_exec($this->ch);
        
        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($statusCode != $expectedCode) {
            throw new ClientException('Invalid response. Expected HTTP status code \'' . $expectedCode . '\' but received \'' . $statusCode . '\'', $statusCode);
        }
        
        return $this->processBody($response);
    }
    
    protected function processBody($body){
        
        //For JSON
        if($this->type == self::TYPE_JSON){
            $object = json_decode($body, true);
        }
        //For XML
        else if($this->type == self::TYPE_XML){
            libxml_use_internal_errors(true);
            if (empty($body) || preg_match('/^\s+$/', $body))
                return null;
            
            $xml = simplexml_load_string($body);
            
            if (!$xml) {
                $err = "Couldn't parse XML response because:\n";
                foreach(libxml_get_errors() as $xml_err)
                    $err .= "\n    - " . $xml_err->message;
                $err .= "\nThe response was:\n";
                $err .= $body;
                throw new ClientException($err);
            }
            
            $object = $xml;
        }
        
        return $object;
    }
}
