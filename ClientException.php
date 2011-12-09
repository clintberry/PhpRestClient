<?php
namespace CB\PhpRest

/**
 * Exception class for PhpRest lib
 * 
 * @author Clint Berry
 */
class ClientException extends Exception {
    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
