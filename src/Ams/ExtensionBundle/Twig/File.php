<?php
namespace Ams\ExtensionBundle\Twig;

class File extends \Twig_Extension
{

    /**
     * Return the functions registered as twig extensions
     * 
     * @return array
     */
    public function getFunctions()
    {
        
        return array(
            new \Twig_SimpleFunction('file_exists', array($this, 'file_exists')),
        );
    }

    public function getName()
    {
        return 'AmsFile';
    }
    
    public function file_exists($f)
    {
        return file_exists($f);
    }
}
