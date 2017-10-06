<!DOCTYPE html>
  <html>
    <head> 
      <meta charset='utf-8'> 
      <title> Feuille de portage </title>
      <style>
        table{width:100%; border: solid 1px;border-collapse: collapse;}
        table td,table th{border:solid 1px;font-size:13px}
        table td{font-size:12px}
        h2,h3{margin:0}
        table, .no-border td{border:none}
        .saut_page{page-break-before: always;} /** SAUT DE PAGE PDF**/
      </style>
    </head>
    <body> 
        <?php $mainData = end($query); ?>
        <table class="data">
            <tr class="no-border">
                <td colspan="2"> <h2> <?php echo $mainData['depot_libelle'] ?> </h2></td>
                <td> Tournee :</td>
                <td> <h3> <?php echo $mainData['tournee'] ?> </h3></td>
            </tr>
            <tr class="no-border">
                <td colspan="2"> </td>
                <td> Jour :</td>
                <td> <h3> <?php echo $mainData['jour'] ?> </h3></td> 
            </tr>
            <tr class="no-border">
                <td colspan="2"> </td>
                <td> Date :</td>
                <td> <h3> <?php echo $date ?> </h3> </td> 
            </tr>
            <tr class="no-border"><td colspan="4"></td></tr>
            <tr class="no-border"><td colspan="4"> <?php echo $mainData['adresse'] ?> </td></tr>
            <tr class="no-border"><td colspan="4"> <?php echo $mainData['cp'].' '.$mainData['ville'] ?> </td></tr>
           <tr>
             <th> TITRE</th>
             <th> N CLIENT </th>
             <th> QUANTITE </th>
             <th> QUANTITE TOTALE </th>
           </tr><?php
           foreach($query as $key=>$data){
               echo 
               '<tr>'.
                    '<td style="width:100px;text-align:center"> <img src="'.$path.$data['path'].'" alt=""/> </td>'.
                    '<td> '.$data['numabo_ext'].' '.$data['vol1'].' '.$data['vol2'].' </td>'.
                    '<td style="text-align:center"> '.$data['qte'].' </td>';
               if(!$key) echo 
                   '<td style="text-align:center" rowspan="'.count($query).'"> '.$data['qte_total'].' </td>';
               echo
               '</tr>';
           }?> 
    
        </table>
    </body>
  </html>
      
