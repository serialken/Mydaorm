<!DOCTYPE html>
  <html>
    <head> 
      <meta charset='utf-8'> 
      <title> Feuille de portage </title>
      <style>
        @font-face {
            font-family: Tinos;
            src: url(<?php echo $font.'Tinos.ttf'?>);
        }
        table{width:100%; border: solid 1px;border-collapse: collapse;margin-bottom:50px}
        table td,table th{border:solid 1px;font-size:16px}
        table td{font-size:12px;text-align:center;}
        table span.libelle_tournee{border-right: 1px solid;display: block;float: left;width: 70%;font-size:24px;height:29px}
        table .qte{font-size:24px;font-weight:bold;}
        td.title{font-size:20px;font-weight:bold;text-transform : uppercase;text-align:center }
        h2,h3{margin:0}
        table, .no-border td{border:none}
        .saut_page{page-break-before: always;} /** SAUT DE PAGE PDF**/
        html,body{font-family: 'Tinos', serif !important;}
      </style>
    </head>
    <body> 
 <?php 
$aTournee = array();
$aProduct = array();
$count = '';
$aKey = array_keys($query);
$last_key = end($aKey);

$i = 0;
$NbrCol = 0;
$NbrLigne = 4;
$NbreData = count($tournees);


foreach($totalProduct as $idProduct=>$dataProduct){
    echo '</table><div class="saut_page"></div>';
    $count = $pageBreak = 0;
    while ($count < $NbreData) {
        if($count) echo '</table>';
        echo '<table>'
            .' <col width="200"> <col width="200"><col width="200"><col width="200">'
            .' <tr> <td colspan="4" class="title"> '.$dataProduct['libelle'].' ('.$dataProduct['quantite'].') </td> </tr>';
        
        for ($i=0; $i<$NbrLigne; $i++) {
            echo '<tr>';
            $j = 0;
            while ($j<4) {
                $k = ($i+($j*$NbrLigne)) + $count ;
                if($k > max(array_keys($tournees)))
                    echo  '<td class="qte"> <span class="libelle_tournee"> - </span> 0 </td>';
                else{
                    $code = substr($tournees[$k]['code'],3,-2);
                    $quantite = (isset($totalProductTournee[$tournees[$k]['id']][$idProduct])) ? $totalProductTournee[$tournees[$k]['id']][$idProduct] : 0;
                    echo '<td class="qte"> <span class="libelle_tournee">'.$code.'</span>'. $quantite.'</td>';
                }
                $j++;
            }

           echo '</tr>'; 
        }
        $count = $count +16;
        if(($pageBreak % 4) == 0)
            echo '<div class="saut_page"></div>';
        $pageBreak++;
    }
}
?>
 
    </body>
  </html>
      
