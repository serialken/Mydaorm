<?php

namespace Ams\DistributionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
class CrmDetailType extends AbstractType
{
      protected $categoryId;
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryId = $this->categoryId;
        $startDate  = $this->startDate;
        $endDate    = $this->endDate;
        $depot      = $this->depot;
        $em         = $this->em;
        $listTournee = $this->listTournee;

        $builder->add('dateMin','date',array('label'=>'Date du',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime', 
                                            'format' => 'dd/MM/yyyy',
                                            'mapped'=>false,
                                            'attr'=>array('class'=>'dateDebut')
                                            )
                )
            ->add('dateCreat','date',array( 'label'=>'Date de création',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime', 
                                            'format' => 'dd/MM/yyyy H:mm:ss',
                                            'mapped'=>true,
                                            'read_only'=>true,
                                             //'data'=>new \datetime(),
                
//                                            'data' => ($options['data']!== false) ? $options['data']->getDateCreat() : new \datetime(),
                                            'attr'=>array('class'=>'dateDebut')
                                            ))

            ->add('dateMax','date',array(   'label'=>'Au',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime',
                                            'mapped'=>false, 
                                            'format' => 'dd/MM/yyyy',
                                            'attr'=>array('class'=>'dateDebut')
                                            ))
                
            ->add('dateDebut','datetime',array('label'=>'Date du',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime', 
                                            'format' => 'dd/MM/yyyy',
                                            'mapped'=>true,
                                            'attr'=>array('class'=>'dateDebut')
                                            ))
            ->add('dateFin','datetime',array('label'=>'Au',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime', 
                                            'format' => 'dd/MM/yyyy',
                                            'attr'=>array('class'=>'dateDebut')
                                            ))
                
                
            ->add('flux','entity', array(
                                           'class'=>'AmsReferentielBundle:RefFlux',
                                           'property'=>'libelle',
                                           'label'=>'Flux',
                                           'empty_value'=>'Toute(s)',
                                           'mapped'=>false))

           ->add('numaboExt','text',array('label'=>'Numéro client'))
           ->add('vol4', 'text', array('label'=>'Adresse'))
           ->add('vol5', 'text', array('label'=>'Lieur Dit','required'=>false))
            ->add('vol3', 'text', array('label'=>'Cplt Adresse','required'=>false))
           ->add('cp','text',array('label'=>'Code postal'))
           ->add('vol2','text',array('label'=>'cplt Nom','required'=>false))
           ->add('ville','text',array('label'=>'Ville','required'=>false,))
           ->add('societeId','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
           ->add('hiddenDateMax','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
           ->add('hiddenDateMin','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
           ->add('depotId','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
           ->add('communeId','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
           ->add('imputation','entity', array(
                                            'class'=>'AmsDistributionBundle:ImputationService',
                                            'property'=>'libelle',
                                            'label'=>'Imputation',
                                            'disabled'=>'disabled'))
           ->add('dateDemandeArbitrage','datetime', array(
                                                    'label'=>'date de la demande',
                                                    'widget'=> 'single_text',
                                                    'input' => 'datetime',
                                                    'mapped'=>true,
                                                    'format' => 'dd/MM/yyyy HH:mm:ss',
                                                    'empty_data'=>new \datetime(),
                                                    'empty_value'=>new \datetime(),
                                                   // 'expanded'=>true,
                                                    'read_only'=>true))
           ->add('dateReponseArbitrage','datetime', array(
                                                    'label'=>'date de réponse',
                                                    'widget'=> 'single_text',
                                                    'input' => 'datetime', 
                                                    'format' => 'dd/MM/yyyy HH:mm:ss',
                                                    'disabled'=>'disabled'))

            ->add('ipp', 'checkbox', array('label'=> 'Ipp'))
            ->add('nipp', 'checkbox', array('label'=> 'Nipp','required'  => false,'mapped'=>false))
            ->add('motif','entity', array(
                                            'empty_value'=>'Motif...',
                                            'class'=>'AmsDistributionBundle:DemandeArbitrage',
                                            'property'=>'libelle',
                                            'label'=>'Motif',
                                            ))
            ->add('cmtReponseArbitrage','textarea',array('label'=>'Commentaire','disabled'=>'disabled'))
            ->add('crId','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
            ->add('utlId','hidden',array('attr'=>array('type'=>'hidden'),'required'=>false,'mapped'=>false))
            ->add('utlDemandeArbitrage','text',array('label'=>'Demandeur','read_only'=>true,'mapped'=>false))
            ->add('utlReponseArbitrage','text',array('label'=>'Arbitre','disabled'=>true,'mapped'=>false))
            
            ->add('commune','entity', array(
                                            'class'=>'AmsAdresseBundle:Commune',
                                            'property'=>'libelle',
                                            'label'=>'Commune'))
            ->add('vol1','text',array('label'=>'Nom'))
            ->add('cmtReponse','textarea',array('label'=>'Réponse'))
            ->add('cmtDemande','textarea',array('label'=>'Commentaire'))
            ->add('crmDemande','entity',array(
                                                'class'=>'AmsDistributionBundle:CrmDemande',
                                                'query_builder' => function(EntityRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                              ->join('c.crmCategorie','crmCategorie')
                                                                              ->where('crmCategorie = '.$this->categoryId)
                                                                              ->orderBy('c.libelle', 'ASC');},
                                                'property'=>'libelle',
                                                'label'=>'Demande',
                                                'empty_value' => 'Toute(s)',
                                               // 'data' => 'Toute(s)',
                                                ))

              ->add('crmReponse','entity',array(
                                                'class'=>'AmsDistributionBundle:CrmReponse',
                                                 'query_builder' => function(EntityRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                              ->join('c.crmCategorie','crmCategorie')
                                                                              ->where('crmCategorie = '.$this->categoryId)
                                                                              ->orderBy('c.libelle', 'ASC');},
                                                'property'=>'libelle',
                                                'label'=>'Type Réponse',
                                                 'empty_value' => 'Choisissez...',
                                                ))
            ->add('crmCategorie','entity',array(
                                                'class'=>'AmsDistributionBundle:crmCategorie',
                                                'property'=>'libelle',
                                                'label'=>'Categorie',
                                                'mapped'=>false,
                                                'disabled'=>'disabled',
                                                ))
            ->add('response','choice',array(
                                        'label'=>'Réponse',
                                        'data'=>'Toute(s)',
                                        'choices'=>array(
                                       // '-1'=>'Toute(s)',
                                        '1'=>'Répondue(s)',
                                        '2'=>'Non répondue(s)',
                                        ), 'empty_value'=>'Toute(s)','mapped'=>false,
                                          ))
            ->add('status','choice',array(
                                        'label'=>'Etat',
                                        'choices'=>array(
                                        '-1'=>'Toutes',
                                        '1'=>'Validé',
                                        '0'=>'Non Validé',
                                        ),'mapped'=>false))
            ->add('depot', 'entity', array(
                                            'class'=>'AmsSilogBundle:Depot',
                                            'property'=>'libelle',
                                            'label'=>'Dépôt',
                                            'query_builder' => function(EntityRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                            ->orderBy('c.libelle', 'ASC');},
                                            'empty_value' => 'Tous les dépôts',
                                          ))

            ->add('societe','entity',array(
                                            'class'=>'AmsProduitBundle:Societe',
                                            'property'=>'libelle',
                                            'label'=>'Société',
                                            'query_builder' => function(EntityRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                              ->where('c.active = 1')
                                                                              ->orderBy('c.libelle', 'ASC');},
                                            'empty_value' => 'Toute(s)',
                                           // 'data' => 'Toute(s)',
                                           
                                          
                                         ))
            ->add('tournee', 'choice', array(
                                                
                                                'label'=>'Tournée',
                                                'choices'=>$this->listTournee,
                                                'mapped'=>false,
                                                'empty_value'=>'Toute(s)',
                                               // 'data' => 'Toute(s)',
                                                ))
            ->add('dateImputationPaie','datetime',array('label'=>'Date imputation paie',
                                            'widget'=> 'single_text',
                                            'input' => 'datetime', 
                                            'format' => 'dd/MM/yyyy',
                                            'attr'=>array('class'=>'imp-paie')
                                            ))
            ->add('cmtImputationPaie','textarea',array('label'=>'Commentaire'))
          /*  ->add('imputationPaie', 'radio', array('label'=> 'Imputation paie','attr'=>array('name'=>'test')))
            ->add('nonImputationPaie', 'radio', array('label'=> 'Non Imputation paie','required'  => false,'mapped'=>false))*/
            ->add('demandeArbitrage','choice',array(
                                                  'label'=>'Demande arbitrage',
                                                  'required'=>false,
                                                  'mapped'=>false,
                                                  'choices'=>array(
                                                    '1'=>'Oui',
                                                    '2'=>'Non',
                                                  ), 
                                              )
            )

            ->add('imputationPaie','choice',array(
                                        'label'=>'Imputation paie',
                                        'choices'=>array(
                                        '1'=>'Oui',
                                        '0'=>'Non',
                                    
                                        ),'mapped'=>true))
                                            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ams\DistributionBundle\Entity\CrmDetail'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ams_distributionbundle_crmdetail';
    }

  

    public function __construct ($categoryId, $startDate = null, $endDate = null, $depot = null, $em=null, $listTournee=null)
    {
        $this->categoryId = $categoryId;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->depot      = $depot;
        $this->em = $em;
        $this->listTournee = $listTournee;
       // $this->numAboExt      = $numAboExt;
    }
}
