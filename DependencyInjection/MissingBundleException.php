<?php

namespace Chill\MainBundle\DependencyInjection;

use Exception;

/**
 * Description of MissingBundleException
 *
 * @author julien
 */
class MissingBundleException extends Exception {
    
    public function __construct($missingBundleName) {
        $message = "The bundle $missingBundleName is missing.";
        
        parent::__construct($message, 500);
    }
    
}
