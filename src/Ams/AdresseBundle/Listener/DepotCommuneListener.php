<?php

namespace Ams\AdresseBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Ams\AdresseBundle\Entity\DepotCommune;
use DateTime;

class DepotCommuneListener
{
    public function __construct()
    {
    }

    public function prePersist(DepotCommune $depotCommune, LifecycleEventArgs $event)
    {
        $t = new DateTime($depotCommune->getDateDebut()->format('d-m-Y'));
        $date_fin =  new DateTime('2078-12-31');
        if ( is_a($t, 'DepotCommune') ) {
            $commune = $depotCommune->getCommune();
            $em = $event->getEntityManager();
            $oldDepotCommune = $em->getRepository('AmsAdresseBundle:DepotCommune')->findOneBy(array('commune'=>$commune,'dateFin'=>$date_fin));
            $oldDepotCommune->setDateFin($t->modify('-1 DAY'));
            $em->flush();
        }
    }
}
