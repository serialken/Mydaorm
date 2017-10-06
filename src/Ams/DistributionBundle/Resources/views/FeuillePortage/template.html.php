<!DOCTYPE html>
  <html>
    <head>
      <meta charset='utf-8'>
      <title> Feuille de portage </title>
      <!--<link href='http://fonts.googleapis.com/css?family=Monospace' rel='stylesheet' type='text/css'>-->
      <style>
/*        @font-face {
            font-family: Monospace;
            src: url(<?php //echo $font.'Tinos.ttf'?>);
        } */
        /*html,body{font-family: 'Tinos', serif !important;}*/
        html,body{font-family: Monospace !important; }
        /*body{font-family: "Monospace", serif;}*/
        table{width:100%; border: solid 1px;border-collapse: collapse;}
        table td,table th{border:solid 1px;font-size:12px;}
        table td{font-size:14px}
        .id_tournee td, .id_tournee{border:none}
        .no-border{border:none}
        .separe_rows{height:9px}
        .separe_rows td {border:solid 1px #FFF}
        .data{margin-bottom:0px}
        .separe_city{text-align:center;background-color:#D3DCE3;border:solid 2px #D3DCE3;font-size:18px;font-weight:bold}
        .separe_street{text-align:center;background-color:#EEEEEE;border:solid 2px #EEEEEE;font-size:18px;font-weight:bold}
        table.rapport_distrib{border-collapse: collapse;margin-top:20px;margin-bottom:40px}
        table.rapport_distrib thead th{
          background-color: #D3DCE3;font-size: 16px;text-align:center;border: 1px solid #aaa;
          font-weight: normal;padding: 8px 0px 8px 0px;
        }
        table.rapport_distrib tbody td{text-align:center;border: 1px solid #aaa; font-size: 14px; padding : 8px 0px 8px 0px}
        table.id_tournee{background: #FFFFFF;text-align: center;margin: 0px 0px 0px 0px;}
        table.id_tournee td{padding: 0px 0px 15px 0px;}
        td.date	{font-weight: bold;width: 50%;}
        td.centre{font-size: 12px;width: 50%;}
        td.tournee{font-size: 12px;width: 50%;}
        td.porteur{font-size: 12px;width: 50%;}
        td.centre b{font-size: 14px;}
        td.porteur b, td.centre b, td.tournee b{font-size: 16px;}
        .star{font-weight:bold;margin-right:2px;font-size:18px}
        .saut_page{page-break-before: always;} /** SAUT DE PAGE PDF**/
        table.resume_feuille th{font-size: 14px;text-align:center;font-weight: bold;}
	table.resume_feuille tbody tr td{text-align:center;border: 1px solid ;font-size: 18px;}
	table.resume_feuille tbody tr td.total{font-weight:bold; background-color:#dddddd;}

        table.data th{font-size: 16px;text-align:center;font-weight: bold;}
	table.data tbody tr td{text-align:center;border: 1px solid ;font-size: 12px; font-weight: bold;}

        table.legende tbody td{text-align:center;border: 1px solid #aaa; font-size: 12px;}
        #footer {
            width:100%;
            height:80px;
            position:relative;
            bottom:0;
            left:0;

      }

      td.legende {font-size: 10px;}

      </style>
    </head>
    <body> <?php
        if (!defined('SIZESHEET')) define('SIZESHEET', 100);
        if (!defined('SIZEDATA')) define('SIZEDATA', 5.4);
        if (!defined('SIZEHEADER')) define('SIZEHEADER', 7.8);
        if (!defined('SIZECITY')) define('SIZECITY', 2.95);
        if (!defined('SIZEADDRESS')) define('SIZEADDRESS', 1.95);
        if (!defined('SIZETITRE')) define('SIZETITRE', 4.3);
        if (!defined('SEPAREROW')) define('SEPAREROW', 0.9);
        if (!defined('SIZEHEADTABLE')) define('SIZEHEADTABLE', 2.3);
        if (!defined('SEPARESTREET')) define('SEPARESTREET', 2);
        $iCurrentSize = SIZEHEADER;
        $iSizeSheet = SIZESHEET ;
        $aCity = $aAddress = $tournee = $aAllAddress = array();
        $aStopAddress = $aStopCity = array();
        $aNewAddress = $aNewCity = array();
        if (!function_exists('breakPageNew')) {
            function breakPageNew(){ 
                            echo '</table>'
                                   .'<table class="data saut_page">'; 
            }
        }
        if (!function_exists('pageBreak')) {
            function pageBreak(&$var1,$var2,$iSizeSheet,$city,$adress,$changeAdress = false,$changeCity= false){

                if($changeCity){
                    $calc = $var1+$var2+SIZECITY+5.3;
                    if($calc >= $iSizeSheet){
                        echo '</table>'
                               .'<table class="data saut_page">'
                               .'<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>';
                        $var1 = SIZEHEADER;
                    }
                    else
                        $var1 = $var1+$var2;
                }
                else{
                    $calc = ($changeAdress)? $var1+$var2+5.3  :  $var1+$var2;

                    if($calc >= $iSizeSheet){
                        echo '</table>'
                               .'<table class="data saut_page">'
                               .'<tr class="separe_rows"> <td colspan="7" rowspan="1"></td> </tr>'
                               .'<tr class="separe_city"> <td colspan="7" rowspan="1"> '.current($city).'</td> </tr>'
                               .'<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>';
                        if(!$changeAdress)
                              echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.current($adress).'</td> </tr>' ;
                        $var1 = SIZEHEADER;
                    }
                    else
                        $var1 = $var1+$var2;
                }
            }
        }

        if (!function_exists('explode_adresse')) {
            function explode_adresse($adr){
              $retour = array('numVoie' => '', 'voie' => $adr);
              $regexAvecNumVoie = "/^([0-9]+\s?[a-z]\s|[0-9]+\s)(.+)$/i";
              if(preg_match_all($regexAvecNumVoie, $adr, $aArr))
              {
                $retour['numVoie']= $aArr[1][0];
                $retour['voie'] = $aArr[2][0];
              }
              return $retour;
            }
      }

      if (!function_exists('countAddress')) {
        function countAddress($aData){
         $aAdresse = array();
         foreach($aData as $data){
           $string =trim($data['adresse'].' '.$data['ville']);
           if (!array_key_exists(trim($string), $aAdresse)) {
             $aAdresse[$string] = 1;
           }
           else{
             $aAdresse[$string] = $aAdresse[$string] + 1;
           }
         }
         return $aAdresse;
        }
      }
    foreach($query as $key=>$data){
       $exAdresse = explode_adresse($data['adresse']);

       /** TRAITEMENT UNIQUE PAR TOURNEE **/
        if(!in_array($data['tournee_jour_code'], $tournee)){
            $tournee[] = $data['tournee_jour_code'];

            /** TABLEAU GENERAL TOURNEE **/
            echo '
            <table class="id_tournee saut_page">
                <tr>
                  <td class="date" align="left">'.$date.'</td>
                  <td class="centre" align="right">Centre : <b>'.$data['depot_libelle'].'</b></td>
                </tr>
                <tr>
                  <td class="tournee" align="left">Tournee : <b>'.$data['tournee_jour_code'].'</b></td>
                  <td class="porteur" align="right">Porteur :<b>'.$data['nom_porteur'].' '.$data['prenom_porteur'].'</b></td>
                </tr>
            </table>';

            /** TABLEAU DETAIL TOURNEE**/
            echo '
            <table class="resume_feuille">
                <thead>
                  <tr>
                      <th style="width:20%">PRODUIT</th>
                      <th style="width:15%">NOUVEAUX</th>
                      <th style="width:15%">ARRETS</th>
                      <th style="width:15%">NBRE POINT LIVRAISON</th>
                      <th style="width:15%">QTES CLIENTS</th>
                      <th style="width:15%">QTES JOURNAUX</th>
                  </tr>
                </thead>
                <tbody>';
                $nb_point_livaison=0;
                $nb_client=0;
                $nb_stop=0;
                foreach($countProductTournee[$data['tournee_jour_id']] as $key => $val){
                  if(!isset($val['img']))continue;
                  if(file_exists($path.$val['img']))
                    $img = '<img height="25px" src="'.$path.$val['img'].'" alt="" title="" />';
                  else $img = $val['produit_libelle'];
                  $nb_point_livaison = $nb_point_livaison + $val['Qte_PL'];
                  $nb_client = $nb_client + $val['Qte_C'];
                  $nb_stop = $nb_stop + $val['Qte_S'];
                  echo '
                  <tr>
                      <td style="width:100px;text-align:center"> '.$img.'</td>
                      <td style="text-align:center"> '.$val['Qte_N'].' </td>
                      <td style="text-align:center"> '.$val['Qte_S'].' </td>
                      <td style="text-align:center"> '.$val['Qte_PL'].' </td>
                      <td style="text-align:center"> '.$val['Qte_C'].'  </td>
                      <td style="text-align:center"> '.$val['Qte_J'].'  </td>
                  </tr> '
                  ;
                }
                echo '
                  <tr>
                    <td style="text-align:center" class="total">TOTAUX</td>
                    <td style="text-align:center" class="total1"> '.$countProductTournee[$data['tournee_jour_id']]['STATS']['TOTAL_N'].' </td>
                    <td style="text-align:center" class="total3"> '.$nb_stop.' </td>
                    <td style="text-align:center" class="total1"> '.$nb_point_livaison.' </td>
                    <td style="text-align:center" class="total2"> '.$nb_client.' </td>
                    <td style="text-align:center" class="total3"> '.$countProductTournee[$data['tournee_jour_id']]['STATS']['TOTAL_J'].' </td>
                  </tr>
                </tbody>
            </table>';


            /** TABLEAU RAPPORT DE DISTRIBUTION **/
            echo '
            <table class="rapport_distrib">
                <thead>
                <tr>
                  <th colspan="3" align="center">RAPPORT DE DISTRIBUTION</th>
                </tr>
                <tr>
                    <th style="width:25%;" align="center">N Abo / Diff</th>
                    <th style="width:30%;" align="center">Nom</th>
                    <th style="width:45%;" align="center">Commentaires de distribution</th>
                </tr>
                </thead>
                <tbody>';
                for($iT=0; $iT<6; $iT++){
                echo '
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                }
                echo '
                </tbody>
            </table>

         <div  id="footer">
                <table class="rapport_distrib">
                   <thead>
                        <tr>
                          <th colspan="9" align="center">L&Eacute;GENDE CODE INFOS PORTAGE</th>
                        </tr>
                   </thead>
                           <tr>
                                    <td  class="legende">BA : Vigik</td>
                                    <td  class="legende">CL : N° Clef </td>
                                    <td  class="legende">CO : Consigne </td>
                                    <td  class="legende">DI : Digi </td>
                                    <td  class="legende">HO : Horaires </td>
                                    <td  class="legende">LE : Bal </td>
                                    <td  class="legende">MS : Message</td>
                                    <td  class="legende">PA : Info </td>
                                    <td  class="legende">SP : SS Porte </td>
                                <tr>
                </table>
            </div>'; 
            if((isset($aNewAbonneTournee[$data['tournee_jour_code']])) || (isset($aStopAbonneTournee[$data['tournee_jour_code']]))){
                echo '<div style="page-break-after:right"></div>';
            }
            /** TABLEAU DES NOUVEAUX **/
            $size_count=SIZETITRE;
            if(isset($aNewAbonneTournee[$data['tournee_jour_code']])){
                echo '
                <h3 style="text-align:center;margin-top:10px"> LES NOUVEAUX </h3>
                <table class="data">
                    <tr>
                      <th> N Rue</th>
                      <th> Nom - Prenom </th>
                      <th> Produit </th>
                      <th> Qtes </th>
                      <th> Infos portage </th>
                      <th> N abonne </th>
                      <th> Ord </th>
                    </tr>';


              foreach($aNewAbonneTournee[$data['tournee_jour_code']] as $newData){
                    $newtext = trim($newData['valeur']);
                    if($newData['info_portage_livraison'] != ''){
                        if($newData['valeur'] != '')
                            $newtext.= ','.trim($newData['info_portage_livraison']);
                        else
                            $newtext.= trim($newData['info_portage_livraison']);
                    }

                $NewExAdresse = explode_adresse($newData['adresse']);
                if(!in_array($newData['cp'].$newData['ville'], $aNewCity)){
                    $aNewCity[] = $newData['cp'].$newData['ville'];
                    if(($size_count+ ((SEPAREROW * 2) + SIZECITY+SIZEDATA))>$iSizeSheet){
                        breakPageNew();
                        $size_count=0;
                    }
                    $size_count=$size_count+ (SEPAREROW * 2) + SIZECITY;
                     echo  ''
                    . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>'
                    . '<tr class="separe_city"> <td colspan="7" rowspan="1"> '.$newData['cp'].' '.$newData['ville'].'</td> </tr>'
                    . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>';
                }
                if(!in_array($NewExAdresse['voie'], $aNewAddress)){
                    $aNewAddress = array();
                    $aNewAddress[] = $NewExAdresse['voie'];
                    if(($size_count+(SEPARESTREET+SIZEDATA))>$iSizeSheet){
                        breakPageNew();
                        $size_count=0;
                    }
                    $size_count=$size_count + SEPARESTREET;
                    echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.$NewExAdresse['voie'].'</td> </tr>';                    
                }

                if(file_exists($path.$newData['path']))
                    $img = '<img height="25px" src="'.$path.$newData['path'].'" alt="" title="" />';
                else $img = $newData['libelle'];
                if(($size_count + SIZEDATA) >$iSizeSheet){
                    breakPageNew();
                    echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.$NewExAdresse['voie'].'</td> </tr>';
                    $size_count=0;
                }
                $size_count=$size_count + SIZEDATA;
                echo
                '<tr>
                    <td style="width:5%;text-align:center;"> '.$NewExAdresse['numVoie'] .'  </td>
                    <td style="width:27%;padding:10px;height:28px"> <span class="star"> * </span>'.$newData['vol1'].' '.$newData['vol2'].' </td>
                    <td style="width:8%;text-align:center"> '.$img.'  </td>
                    <td style="width:5%;text-align:center;"> '.$newData['qte'].' </td>
                    <td style="padding-left:10px;width:40%;"> '.$newtext.'  </td>
                    <td style="width:10%;text-align:center;"> '.$newData['num_abonne'].' </td>
                    <td style="width:5%;text-align:center">  </td>
                 </tr>
                ';
              }
              echo '</table>';
            }
            /** TABLEAU DES ARRETS **/
            if(isset($aStopAbonneTournee[$data['tournee_jour_code']])){
                $separ_new_stop=   SIZETITRE +SIZEHEADTABLE+ (SEPAREROW * 2) + SIZECITY + SEPARESTREET + SIZEDATA;
                if(($size_count + $separ_new_stop) >$iSizeSheet){
                    echo '<h3 style="text-align:center;margin-top:10px" class="saut_page"> LES ARRETS </h3>';
                    $size_count=SIZETITRE;
                }else{
                    echo '<h3 style="text-align:center;margin-top:10px"> LES ARRETS </h3>';
                    $size_count=$size_count+SIZETITRE;
                }
                echo '
                <table class="data">
                    <tr>
                      <th> N Rue</th>
                      <th> Nom - Prenom </th>
                      <th> Produit </th>
                      <th> Qtes </th>
                      <th> Infos portage </th>
                      <th> N abonne </th>
                      <th> Ord </th>
                    </tr>';

              $size_count=$size_count + SIZEHEADTABLE;
              foreach($aStopAbonneTournee[$data['tournee_jour_code']] as $stopData){
                $StopExAdresse = explode_adresse($stopData['adresse']);
                if(!in_array($stopData['ville'], $aStopCity)){
                    if(($size_count + ((SEPAREROW * 2) + SIZECITY + SIZEDATA))>$iSizeSheet){
                        breakPageNew();
                        $size_count=0;
                    }
                    $size_count=$size_count+(SEPAREROW * 2) + SIZECITY;
                    $aStopCity[] = $stopData['ville'];
                     echo  ''
                    . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>'
                    . '<tr class="separe_city"> <td colspan="7" rowspan="1"> '.$stopData['cp'].' '.$stopData['ville'].'</td> </tr>'
                    . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>';
                }
                if(!in_array($StopExAdresse['voie'], $aStopAddress)){
                    $aStopAddress = array();
                    $aStopAddress[] = $StopExAdresse['voie'];
                    if(($size_count+(SEPARESTREET+SIZEDATA))>$iSizeSheet){
                        breakPageNew();
                        $size_count=0;
                    }
                    $size_count=$size_count + SEPARESTREET;
                    echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.$StopExAdresse['voie'].'</td> </tr>';
                }

                if(file_exists($path.$stopData['path']))
                    $img = '<img height="25px" src="'.$path.$stopData['path'].'" alt="" title="" />';
                else $img = $stopData['libelle'];
                if(($size_count + SIZEDATA) >$iSizeSheet){
                    breakPageNew();
                    echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.$StopExAdresse['voie'].'</td> </tr>';
                    $size_count=0;
                }
                $size_count=$size_count + SIZEDATA;
                echo
                '<tr>
                    <td style="width:5%;text-align:center"> '.$StopExAdresse['numVoie'] .'  </td>
                    <td style="width:27%;padding:10px;height:28px"> '.$stopData['vol1'].' '.$stopData['vol2'].' </td>
                    <td style="width:8%;text-align:center"> '.$img.'  </td>
                    <td style="width:5%;text-align:center;"> '.$stopData['qte'].' </td>
                    <td style="padding-left:10px;width:40%;">  </td>
                    <td style="width:10%;text-align:center;"> '.$stopData['num_abonne'].' </td>
                    <td style="width:5%;text-align:center;">  </td>
                 </tr>
                ';
              }
              echo '</table>';
            }

            echo '
            <h4 class="saut_page" style="text-align:center;"> LA TOURNEE </h4>
            <table class="data">
              <tr>
                <th> N°</th>
                <th> Nom - Prenom </th>
                <th> Produit </th>
                <th> Qtes </th>
                <th> Infos portage </th>
                <th> N abonne </th>
                <th> Ord </th>
              </tr>';
        }
        /** FIN TRAITEMENT UNIQUE PAR TOURNEE **/

        if(!in_array($data['cp'].$data['ville'], $aCity)){
            $aAddress = array();
            pageBreak($iCurrentSize, SIZECITY,SIZESHEET,$aCity,$aAddress,false,true);
            $aCity = array();
            $aCity[] = $data['cp'].$data['ville'];
             echo  ''
            . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>'
            . '<tr class="separe_city"> <td colspan="7" rowspan="1"> '.$data['cp'].' '.$data['ville'].'</td> </tr>'
            . '<tr class="separe_rows"> <td colspan="7" rowspan="1"> </td> </tr>';
        }

        if(!in_array($exAdresse['voie'].'_'.$data['ville'], $aAddress)){
            $aAddress = array();
            $aAddress[] = $exAdresse['voie'].'_'.$data['ville'];
            pageBreak($iCurrentSize, SIZEADDRESS,SIZESHEET,$aCity,$aAddress,true);
            echo '<tr class="separe_street"> <td colspan="7" rowspan="1"> '.$exAdresse['voie'].'</td> </tr>';
        }     
        pageBreak($iCurrentSize, SIZEDATA,SIZESHEET,$aCity,$aAddress);

        $newtext = trim($data['valeur']);
        if($data['valeur'] != '') $newtext.= ',';
        $newtext.= trim($data['info_portage_livraison']);
        if(file_exists($path.$data['path']))
          $img = '<img height="25px" src="'.$path.$data['path'].'" alt="" title="" />';
        else $img = $data['produit_libelle'];
        echo
          '<tr>
             <td style="width:5%;text-align:center"> '.$exAdresse['numVoie'] .'</td>
             <td style="width:20%;padding:1px;height:25px"> '.$data['vol1'].' '.$data['vol2'].' </td>
             <td style="width:11%;text-align:center"> '.$img.' </td>
             <td style="width:4%;text-align:center"> '.$data['qte'].' </td>
             <td style="padding-left:5px;width:45%"> '.$newtext.' '.$data['vol3'].' </td>
             <td style="width:12%;text-align:center"> '.$data['num_abonne'].' </td>
             <td style="width:5%;text-align:center"> ('.$data['point_livraison_ordre'].') </td>
           </tr>
          ';
        }
        /** END FOREACH**/
  echo '
      </table>
    </body>
  </html>';
