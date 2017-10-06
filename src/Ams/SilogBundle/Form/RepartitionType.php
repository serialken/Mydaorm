<?php

namespace Ams\SilogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Ams\ProduitBundle\Repository\SocieteRepository;

class RepartitionType extends AbstractType {

    private $dpts;
    private $urlBase;

    public function __construct($dpts, $sUrlBase) {
        $this->dpts = $dpts;
        $this->urlBase = $sUrlBase;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
//        $depotIds = array_keys($this->depots);

        $builder
//            ->add('date', 'text', 
//                    array(
//                        'required' => true, 
//                        'attr'=>array('class' => 'date')
//                        )
//                )
                ->add('dpt', 'choice', array(
                    'label' => 'Département',
                    'required' => true,
                    'choices' => $this->dpts,
                    'multiple' => false,
                    'expanded' => false,
                    'empty_value' => 'Dpt'
                        )
                )
                ->add('societe', 'entity', array(
                    'label' => 'Société',
                    'class' => 'AmsProduitBundle:Societe',
                    'property' => 'libelle',
                    'empty_value' => 'Société',
                    'query_builder' => function(SocieteRepository $sr) {
                        return $sr->getSocietesLibelles();
                    },
                    'multiple' => false
                        )
                )
                ->add('flux', 'entity', array(
                    'class' => 'AmsReferentielBundle:RefFlux',
                    'property' => 'libelle',
                    'required' => false,
                    'empty_value' => 'Tous les flux',
                    'multiple' => false
                        )
                )
                ->add('save', 'button', array(
                    'attr' => array(
                        'class' => 'btn btn-primary', 
                        'data-feed-exception-url' => $this->urlBase."/admin/repartition/schemas/feedexceptions/",
                        'data-feed-produits-url' => $this->urlBase."/admin/repartition/schemas/feedprodste",
                        'data-feed-depots-url' => $this->urlBase."/admin/repartition/schemas/feeddepots",
                        'data-feed-rules-url' =>  $this->urlBase."/admin/repartition/schemas/feedglobal/",
                        'data-feed-flux-url' =>  $this->urlBase."/admin/repartition/schemas/feedflux",
                        'data-del-excep-url' =>  $this->urlBase."/admin/repartition/schemas/delexception",
                        'data-store-excep-url' =>  $this->urlBase."/admin/repartition/schemas/setexception",
                        'data-get-prod-excep-url' =>  $this->urlBase."/admin/repartition/schemas/getexceptions",
                        )
                    )
                )
        ;
    }

    public function getName() {
        return 'ams_silogbundle_repartitiontype';
    }

}
