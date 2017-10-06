<?php
namespace Ams\WebserviceBundle\Lib;
use \SoapClient;

// extend of __call thats adds a retry to handle the occasional 'Could not connect to host' exceptions
class SoapClientLocal extends SoapClient
{

  public function __call($function_name, $arguments)
  {
    $result = false;
    $max_retries = 200;
    $retry_count = 0;
    
    while(! $result && $retry_count < $max_retries)
    {
      try
      {
        $result = parent::__soapCall($function_name, $arguments);
      }
      catch(SoapFault $fault)
      {
        if($fault->faultstring != 'Could not connect to host')
        {
          //throw $fault;
        }
        
       	sleep(1);
     	$retry_count ++;
      }
      //if($retry_count > 0)echo 'Attente effectuee : '.$retry_count.'s';
    }
    if($retry_count == $max_retries)
    {
      throw new SoapFault('1', 'Could not connect to host after '.$max_retries.' attempts');
    }
    return $result;
  }
}