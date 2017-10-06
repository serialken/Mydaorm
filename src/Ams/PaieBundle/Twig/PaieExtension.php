<?php 

namespace Ams\PaieBundle\Twig;

class PaieExtension extends \Twig_Extension
{
 
    public function getFunctions(){
     return array(
          'addTime' => new \Twig_Function_Method($this, 'addTime'),
          'toTime' => new \Twig_Function_Method($this, 'toTime'),
     );
  }
 
    
     /**
     * Addition 2  time  
     * @param time $time1 00:00:00
     * @param time $time2 00:00:00
     * @return time 00:00:00
     */
    public function addTime($time1,$time2) {
        $secs = 0;
        $hours = 0;
        $mns = 0;   
        $tmp1 = explode(':', $time1);
        $tmp2 = explode(':', $time2);
        $hours = $tmp1[0] + $tmp2[0];
        $mns = $tmp1[1] + $tmp2[1];
        if(isset($tmp1[2]) && isset( $tmp2[2]))
            $secs = $tmp1[2] + $tmp2[2];
        elseif(isset($tmp1[2]))
            $secs = $tmp1[2];
        elseif(isset($tmp2[2]))
            $secs = $tmp2[2];
        else
            $secs = 0;
       
        $total = $hours * 3600 + $mns * 60 + $secs;
        $h = floor($total / 3600);
        $m = floor(($total % 3600) / 60);
        $s = $total - $h * 3600 - $m * 60;
        if ($h < 10) $h = '0' . $h;
        if ($m < 10) $m = '0' . $m;
        if ($s < 10) $s = '0'.$s;
        return $h . ":" . $m . ":" . $s;
     }
    
    public function toTime($time1) {
        $tmp1 = explode(':', $time1);
        return $tmp1[0] . ":" . $tmp1[1];
     }
    

    public function getName()
    {
        return 'pai_extension';
    }
}
