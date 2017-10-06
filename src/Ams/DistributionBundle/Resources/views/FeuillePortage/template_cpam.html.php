<!DOCTYPE html>
<html>
    <head> 
        <meta charset='utf-8'> 
        <title> Feuille Emargement CPAM </title>
        <style>
            table{width:100%; border: solid 1px;border-collapse: collapse;}
            table th{ background-color: #D3DCE3;font-size: 16px;text-align:center;border: 1px solid ;font-weight: normal;padding: 8px 0px 8px 0px;	}
            table td{border:solid 1px;font-size:11px;}
         </style>
    </head>
    <body>  
        <BR><BR>
        <table>
            <tr>
                <th colspan="3"> Quantité par titre </th>
                <th> Quantité</th>
            </tr>
            <tr>
                <td height=25px;>CPAM</td>
                <td>Quotidien</td>
                <td> Nouvelle Attitude_CPAM</td>
                <td style="width:50px;"> <?php echo $total; ?></td>
            </tr>
            <tr>
                <td colspan="3" height=25px;>Total</td>
                <td> <?php echo $total; ?></td>
            </tr>
        </table>
        <BR><BR>

        <table>
            <tr>
                <th width=10px>Ex</th>
                <th width=25px>Titre </th>
                <th width=100px>Adresse </th>
                <th width=50px>Ville </th>
                <th width=250px>Client </th>
                <th>Information accés et dépot </th>
                <th width=10px>Ordre </th>
            </tr><?php
            foreach ($query as $key => $data) {
                echo
                '<tr>' .
                '<td valign=top> ' . $data['qte'] . '</td>' .
                '<td valign=top> ' . $data['produit_libelle'] . ' </td>' .
                '<td valign=top> ' . $data['adresse'] . ' </td>' .
                '<td valign=top> ' . $data['ville'] . ' </td>' .
                '<td valign=top> ' . $data['vol1'] . ' ' . $data['vol2'] . '</td>' .
                '<td valign=top> ' . $data['valeur'] . ' </td>' .
                '<td valign=top> ' . $data['point_livraison_ordre'] . ' </td>' .
                '</tr>';
            }
            ?> 

        </table>
    </body>
    </html>

