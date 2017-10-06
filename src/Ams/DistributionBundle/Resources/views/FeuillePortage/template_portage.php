<?php

if(empty($this->aDates))
{
	echo "Les donnees sont vides pour ces conditions";
}
else 
{
	
	$aTypesDetails = array(
							"arretes_suspendus" => "LES ARRETES / SUSPENDUS",
							"nouveaux" => "LES NOUVEAUX",
							"tournee" => "LA TOURNEE"
							);
	
	//$iNbCarMaxNomPrenomParLigne = 18;
	//$iNbCarMaxInfosPortageParLigne = 26;
	
	$iHautMaxPage = 268; // En millimetre. La hauteur max. allouee au listing
	$iHaut_br = 5; // En vrai, c'est 4.5mm
	$iHautTypeDetail = 11.5; // Avec les "margin"
	$iHautEnteteDetail = 4; // Avec les bordures
	$iHautVille = 5.5; // En realite, c'est 5.5
	$iHautEspDevantVille = 2; // Espace devant le bloc de ville 
	$iHautEspDevantRue = 2; // Espace devant le bloc de "rue"
	$iHautRue = 6; // Avec bordure haut. Sans la bordure "haut", c'est 5mm.
	$iHautNomPrenomParLigne = 4.75; // En realite, 3 lignes equivalent a 13.6mm => une ligne <=> 4.xxmm
	$iHautInfosPortageParLigne = 4.2;
	$iHautBordure = 0.5; // Bordure bas d'un bloc "rue". En realite, c'est 0.5mm
	
	$iHautLivraisonMin = 6.5; // Quand il n'y a que une seule ligne dans toutes les colonnes
	$iHautLivraisonMoy = 18;
	$iHautOccupeTmp = 0;
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<style type="text/css">
		table
		{
			width: 99%; 
		}
		table.id_tournee
		{
			background: #FFFFFF; 
			text-align: center;
			margin: 0px 0px 0px 0px;
		}
		table.id_tournee td
		{
			padding: 0px 0px 15px 0px;
		}
		td.date
		{
			font-weight: bold;
			width: 50%;
		}
		td.centre
		{
			font-size: 10px;
			width: 50%;	
		}
		td.tournee
		{
			font-size: 10px;
			width: 50%;	
		}
		td.porteur
		{
			font-size: 10px;
			width: 50%;	
		}
		td.centre b
		{
			font-size: 14px;
		}
		td.porteur b, td.centre b, td.tournee b
		{
			font-size: 16px;
		}
		table.resume_feuille
		{
			border-collapse: collapse;
		}
		table.resume_feuille th
		{
			background-color: #aaa;	
			font-size: 10px;	
			text-align:	center;
			border: 1px solid #fff;
			color: #fff;
			font-weight: bold;
			padding: 8px 0px 8px 0px;	
			
		}
		table.resume_feuille tbody tr td
		{
			text-align:	center;
			border: 1px solid #aaa;
			padding: 4px 0px 4px 0px;	
			font-size: 18px;	
		}
		table.resume_feuille tbody tr td.total
		{
			font-weight:bold; 
			background-color:#dddddd;	
		}
		table.resume_feuille tbody tr td.total2
		{
			font-weight:bold; 	
		}
		table.rapport_distrib
		{
			border-collapse: collapse;
		}
		table.rapport_distrib thead th
		{
			background-color: #aaa;
			font-size: 13px;	
			text-align:	center;
			border: 1px solid #fff;
			color: #fff;
			font-weight: bold;
			padding: 8px 0px 8px 0px;	
			
		}
		table.rapport_distrib tbody td
		{
			text-align:	center;
			border: 1px solid #aaa;
			padding: 8px 0px 8px 0px;	
		}
		
		
		/*******************************************************************/		
		
		
		div.saut_page
		{
			font-size: 1px;
			page-break-after: always;
		}	
				
		div.div_h_table
		{
			 display:table; 
			 border-collapse:collapse; 
			 width:100%; 
		}		
		div.div_h_tr { 
		    display:table-row; 
		}
		div.div_h_td 
		{ 
			display: table-cell; 
			font-size: 10px;
			color: #aaa;
			text-align:	center;
			border: 1px solid #aaa;
		}
		div.div_h_typedetail
		{
			font-size: 19px;
			font-weight: bold;
			color: #000;
			text-align:	center;
			border: 0px;
			margin: 12px 0px 5px 0px;
			padding: 3px 0px 3px 0px;
		}
		div.div_h_rang
		{
			width: 5%;	
		}
		div.div_h_num_voie
		{
			width: 5%;	
		}
		div.div_h_volet1_2
		{
			width: 25%;	
		}
		div.div_h_num_abonne
		{
			width: 12%;	
		}
		div.div_h_logo
		{
			width: 13%;	
		}
		div.div_h_nbex
		{
			width: 5%;	
		}
		div.div_h_infos_portage
		{
			width: auto;	
		}				
		div.div_ville
		{
			font-weight:	bold;
			font-size: 14px;	
			background-color: #aaa;
			text-align: center;
			padding: 3px 0px 3px 0px;
		}			
		div.div_espace_devant_ville
		{
			font-size: 6px;
		}	
		div.div_espace_devant_rue
		{
			font-size: 6px;
		}	
		div.div_par_rue, div.div_par_rue_susp_nouv, div_par_rue_2
		{
			
		}
		div.div_par_rue, div.div_par_rue_susp_nouv
		{
			
		}
		div.div_par_rue_susp_nouv
		{
			border-bottom:2px solid #555;
		}
		div.div_par_rue_bas
		{
			border-bottom:2px solid #555;
		}
		div.nom_rue
		{
			font-weight: bold;
			font-size: 14px;	
			text-align: center;
			background-color: #ddd;
			padding:2px 2px 2px 2px;
			border-top:2px solid #555;
			border-right:2px solid #555;
			border-left:2px solid #555;	
		}
		div.div_table 
		{ 
		        display:table; 
		        /* Joindre les bords des cellules */
		        border-collapse:collapse; 
		        /* Forcer le tableau à prendre la largeur ecran */
		        width:100%; 
		}
		div.div_tr { 
		    display:table-row; 
		}
		div.div_td 
		{ 
			display: table-cell; 
			vertical-align: middle;
			text-align: center;
			font-size: 12px;
			padding:2px 2px 2px 2px;
		}
		div.rang_livraison, div.rang_livraison_2
		{
			width: 5%;
			border-right:2px solid #555;
			font-style: italic;
			font-size: 10px;
			font-weight: bold;
		}
		div.rang_livraison
		{
			border-top: 1px solid #000; 
		}
		div.rang_livraison_2
		{
			
		} 
		div.num_voie, div.num_voie_2
		{
			width: 5%;	
			border-right: 1px solid #000;
			border-left:2px solid #555;
			font-size: 13px;
			font-weight: bold;
		}
		div.num_voie
		{
			border-top: 1px solid #000;
		}
		div.volet1_2, div.volet1_2_nouveau_abo
		{
			width: 25%;	
			border-top: 1px solid #000;
			border-right: 1px solid #000;
			font-size: 14px;
			font-weight: bold;
		}
		div.volet1_2_nouveau_abo
		{
			text-align: left;
		}
		div.num_abonne, div.nouveau_abo, div.arret_suspendu
		{
			width: 12%;	
			border-top: 1px solid #000;
			border-right: 1px solid #000;
			font-size: 11px;
			font-weight: bold;
		}
		div.volet1_2_nouveau_abo, div.nouveau_abo
		{
			background-color: #aaa;
		}
		div.arret_suspendu
		{
			background-color: #888;
			color: #fff;
		}
		div.logo
		{
			width: 13%;	
			border-top: 1px solid #000;
			border-right: 1px solid #000;
		}
		div.nbex
		{
			width: 5%;	
			border-top: 1px solid #000;
			border-right: 1px solid #000;
		}
		div.infos_portage
		{
			width: auto;
			border-top: 1px solid #000;
			text-align: left;
			font-weight: bold;
			border-right: 1px solid #000;
		}	
		
		</style>
	</head>
	<body>
<?php
	//echo "<pre>";print_r($this->sRequete_Donnees_Brutes);echo "</pre>";echo "<hr/>";
	foreach($aDonnees as $sDateYmdK => $aCentre)
	{
		foreach($aCentre as $sCdK => $aTournees)
		{
			foreach($aTournees as $sTourneeK => $aRang_CD_Tournee_Insee)
			{
				$iRangAdresse = 0;
				//echo "<pre>";print_r($this->aNbAdressesTitre);echo "</pre>";echo "<br/>";
				?>
				
					<table class="id_tournee">
						<tr>
							<td class="date" align="left"><?php echo $this->aDates[$sDateYmdK]["jour_mois_annee"];?></td>
							<td class="centre" align="right">Centre : <b><?php echo $this->get_nom_CD($sCdK);?></b></td>
						</tr>
						<tr>
							<td class="tournee" align="left">Tournee : <b><?php echo $this->get_nom_tournee($sCdK, $sTourneeK);?></b></td>
							<td class="porteur" align="right">Porteur : <b><?php echo $this->get_porteur_tournee($sCdK, $sTourneeK);?></b></td>
						</tr>
					</table>
					<?php 
					$iNbTitres = 0;
					if(isset($this->aNbAdressesTitre[$sDateYmdK][$sCdK][$sTourneeK]))
					{
					?>
					<table class="resume_feuille">
						<thead>
							<tr>
								<th style="width:20%">PRODUIT</th>
								<th style="width:16%">NOUVEAUX</th>
								<th style="width:16%">ARRETS</th>
								<th style="width:16%">NBRE ADRESSES</th>
								<th style="width:16%">QTES CLIENTS</th>
								<th style="width:16%">QTES JOURNAUX</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							foreach($this->aNbEvolutionsAbonnementsTitre[$sDateYmdK][$sCdK][$sTourneeK] as $sIdTitre => $aTypedisV)
							{
								$iNbTitres++;
								$sImg = str_replace("?", "", $sIdTitre);
								$sImg = trim($sImg);
							?>
							<tr>
								<td><?php echo (file_exists(__DIR__."/.././img/".$sImg.".jpg")?'<img src="'.__DIR__.'/.././img/'.$sImg.".jpg".'" width="100" />':$sIdTitre);?></td>
								<td><?php echo ($this->get_nb_nouveauxabos_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK)!=0)?$this->get_nb_nouveauxabos_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK):"&nbsp;";?></td>
								<td><?php echo ($this->get_nb_abos_arretes_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK)!=0)?$this->get_nb_abos_arretes_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK):"&nbsp;";?></td>
								<td><?php echo ($this->get_nb_adresses_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK)!=0)?$this->get_nb_adresses_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK):"&nbsp;";?></td>
								<td><?php echo ($this->get_nb_abos_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK)!=0)?$this->get_nb_abos_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK):"&nbsp;";?></td>
								<td><?php echo ($this->get_nb_exemplaires_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK)!=0)?$this->get_nb_exemplaires_titre($sCdK, $sTourneeK, $sIdTitre, $sDateYmdK):"&nbsp;";?></td>
							</tr>
							<?php 
							}
							?>
							<tr>
								<td class="total">TOTAUX</td>
								<td class="total"><?php echo $this->get_nb_nouveauxabos_total($sCdK, $sTourneeK, $sDateYmdK);?></td>
								<td class="total"><?php echo $this->get_nb_abos_arretes_total($sCdK, $sTourneeK, $sDateYmdK);?></td>
								<td class="total2"><?php echo $this->get_nb_adresses_total($sCdK, $sTourneeK, $sDateYmdK);?></td>
								<td class="total2"><?php echo $this->get_nb_abos_total($sCdK, $sTourneeK, $sDateYmdK);?></td>
								<td class="total"><?php echo $this->get_nb_exemplaires_total($sCdK, $sTourneeK, $sDateYmdK);?></td>
							</tr>
						</tbody>
					</table>
					<?php 
					}
					else 
					{
						echo "Aucun titre n'est distribue sur cette tournee";
					}
					?>
					<br />
					<table class="rapport_distrib">
						<thead>
							<tr>
								<th colspan="3" align="center">RAPPORT DE DISTRIBUTION</th>
							</tr>
							<tr>
								<th style="width:25%;" align="center">N° Abo / Diff</th>
								<th style="width:30%;" align="center">Nom</th>
								<th style="width:45%;" align="center">Commentaires de distribution</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							for($iT=0; $iT<18-$iNbTitres; $iT++)
							{
							?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				<div style="page-break-after:right"></div>
				
				   
				<?php 
				foreach($aTypesDetails as $sIdDetail => $sLibDetailV)
				{
					$aDetailsTmp = array();
					$bBr = false;
					$bTournee = false;
					switch ($sIdDetail)
					{
						case "arretes_suspendus":
							if(isset($this->aArretesSuspendusRegroupees[$sDateYmdK][$sCdK][$sTourneeK]))
							{
								$aDetailsTmp = $this->aArretesSuspendusRegroupees[$sDateYmdK][$sCdK][$sTourneeK];
								$bBr = true;
							}
							break;
						case "nouveaux":
							if(isset($this->aNouveauxRegroupees[$sDateYmdK][$sCdK][$sTourneeK]))
							{
								$aDetailsTmp = $this->aNouveauxRegroupees[$sDateYmdK][$sCdK][$sTourneeK];
								$bBr = true;
							}
							break;
						case "tournee":
							$aDetailsTmp = $aRang_CD_Tournee_Insee;
							$bTournee = true;
							$iRangAdresse = 0;
							break;
					}
					if(!empty($aDetailsTmp))
					{						
						if($iHautOccupeTmp+$iHautTypeDetail+$iHautEnteteDetail+$iHautEspDevantVille+$iHautVille+$iHautEspDevantRue+$iHautRue+$iHautLivraisonMoy > $iHautMaxPage)
						{
							echo '<div class="saut_page">&nbsp;</div>';
							$iHautOccupeTmp = 0;
						}
						?>
					    <div class="div_h_typedetail"><?php echo $sLibDetailV;?></div>
					    <?php 
					    $iHautOccupeTmp += $iHautTypeDetail;
					    ?>
					    <div class="div_h_table">				    	
					    	<div class="div_h_tr">
					    		<div class="div_h_td div_h_num_voie">N° rue</div>
					    		<div class="div_h_td div_h_volet1_2">Nom - Prenom</div>
					    		<div class="div_h_td div_h_logo">Produit</div>
					    		<div class="div_h_td div_h_nbex">Qtes</div>
					    		<div class="div_h_td div_h_infos_portage">Infos portage</div>
					    		<div class="div_h_td div_h_num_abonne">N° abonne</div>
					    		<div class="div_h_td div_h_rang">Ord.</div>
					    	</div>
					    </div>						
						<?php 
						$iHautOccupeTmp += $iHautEnteteDetail;
						foreach($aDetailsTmp as $iRang_CD_Tournee_InseeK => $aInsee)
						{
							foreach($aInsee as $sInseeK => $aRang_CD_Tournee_Insee_Voie)
							{
								if($iHautOccupeTmp+$iHautEspDevantVille+$iHautVille > $iHautMaxPage)
								{
									echo '<div class="saut_page">&nbsp;</div>';
									$iHautOccupeTmp = 0;
								}
								else 
								{
									echo '<div class="div_espace_devant_ville">&nbsp;</div>';
									$iHautOccupeTmp += $iHautEspDevantVille;
								}
								?>
								<div class="div_ville"><?php echo $this->get_ville($sInseeK);?></div>
								<?php 
								$iHautOccupeTmp += $iHautVille;
								foreach($aRang_CD_Tournee_Insee_Voie as $iRang_CD_Tournee_Insee_VoieK => $aLibVoie)
								{
									foreach($aLibVoie as $sLibVoieK => $aRang_CD_Tournee_Insee_Voie_NumVoie)
									{
										$iNbTitresParRue = 1;
										if($bTournee==true)
										{
											$iNbTitresParRue = $this->get_nb_titres_par_rue($sDateYmdK, $sCdK, $sTourneeK, $iRang_CD_Tournee_InseeK, $sInseeK, $iRang_CD_Tournee_Insee_VoieK);
										}
										$iL = 0;
										
										$iHautProchainLivraison = 0;
										foreach($aRang_CD_Tournee_Insee_Voie_NumVoie as $iRang_CD_Tournee_Insee_Voie_NumVoieK => $aNumVoie)
										{
											foreach($aNumVoie as $iNumVoie => $aRang_Abonne_Adresse)
											{
												foreach($aRang_Abonne_Adresse as $iRang_Abonne_Adresse => $aArrInfoAbo)
												{
													if($iHautProchainLivraison==0)
													{
														$iHautNomPrenomTmp = $aArrInfoAbo["NbLignesNomPrenom"] * $iHautNomPrenomParLigne;
														$iHautInfosPortageTmp = $aArrInfoAbo["NbLignesInfosPortage"] * $iHautInfosPortageParLigne;
														
														$iHautProchainLivraison = max($iHautNomPrenomTmp, $iHautInfosPortageTmp);
														$iHautProchainLivraison = max($iHautLivraisonMin, $iHautProchainLivraison);
													}
												}
											}
										}
										if($iHautOccupeTmp+$iHautEspDevantRue+$iHautRue+$iHautProchainLivraison > $iHautMaxPage)
										{
											echo '<div class="saut_page">&nbsp;</div>';
											echo '<div class="div_ville">'.$this->get_ville($sInseeK).'</div>';
											$iHautOccupeTmp = 0;
											$iHautOccupeTmp += $iHautVille;
										}
										?>	
										<div class="div_espace_devant_rue">&nbsp;</div>
										<?php 
										$iHautOccupeTmp += $iHautEspDevantRue;
										?>
										<div class="div_par_rue">
											<div class="nom_rue"><?php echo $sLibVoieK;?></div>
											<?php 
											$iHautOccupeTmp += $iHautRue;
											?>
											<div class="div_table">
												<?php 
												foreach($aRang_CD_Tournee_Insee_Voie_NumVoie as $iRang_CD_Tournee_Insee_Voie_NumVoieK => $aNumVoie)
												{
													foreach($aNumVoie as $iNumVoie => $aRang_Abonne_Adresse)
													{
														$iNbAbosAdr = count($aRang_Abonne_Adresse);
														foreach($aRang_Abonne_Adresse as $iRang_Abonne_Adresse => $aArrInfoAbo)
														{
															$iHautNomPrenomTmp = $aArrInfoAbo["NbLignesNomPrenom"] * $iHautNomPrenomParLigne;
															$iHautInfosPortageTmp = $aArrInfoAbo["NbLignesInfosPortage"] * $iHautInfosPortageParLigne;
															
															$iHautOccCetAbonne = max($iHautNomPrenomTmp, $iHautInfosPortageTmp);
															$iHautOccCetAbonne = max($iHautLivraisonMin, $iHautOccCetAbonne)+$iHautBordure;
																													
															if($iHautOccupeTmp+$iHautOccCetAbonne > $iHautMaxPage)
															{
																echo "</div></div>"; // Fermeture class="div_table" & class="div_par_rue_susp_nouv"
																echo '<div class="saut_page">&nbsp;</div>';
																echo '<div class="div_ville">'.$this->get_ville($sInseeK).'</div>';
																echo '<div class="div_espace_devant_rue">&nbsp;</div>';
																$iHautOccupeTmp = 0;
																$iHautOccupeTmp += $iHautVille;
																$iHautOccupeTmp += $iHautEspDevantRue;
																echo '<div class="div_par_rue">'; // Ouverture class="div_table" & class="div_par_rue" a la page suivante
																echo '<div class="nom_rue">'.$sLibVoieK.'</div>
																		<div class="div_table">'; // Remettre le nom de la rue
																$iHautOccupeTmp += $iHautRue;
															}
															$iL++;
															?>
															<div class="div_tr">
																<?php 
																$sCssRowspan = "";
																if($bTournee!=true)
																{
																	$iRangAdresseAAfficher = "&nbsp;";
																	$iNumVoieAAfficher = $iNumVoie;
																}
																else if($iRang_Abonne_Adresse==0)
																{
																	$iRangAdresse++;
																	$iRangAdresseAAfficher = "(".$iRangAdresse.")";
																	$iNumVoieAAfficher = $iNumVoie;
																}
																else
																{
																	$sCssRowspan = "_2";
																	$iRangAdresseAAfficher = "&nbsp;";
																	$iNumVoieAAfficher = "&nbsp;";
																}
																?>
																<div class="div_td num_voie<?php echo $sCssRowspan;?><?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo $iNumVoieAAfficher;?></div>
																<div class="div_td volet1_2<?php echo ($aArrInfoAbo["typedis"]=="NOUVEAUX")?"_nouveau_abo":"";?><?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo (($aArrInfoAbo["typedis"]=="NOUVEAUX")? "&#9733; ":"");?><?php echo $aArrInfoAbo["NomPrenom"];?></div>
																<?php 
																$sImg = str_replace("?", "", $aArrInfoAbo["idtitre"]);
																$sImg = trim($sImg);
																?>
																<div class="div_td logo<?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo (file_exists(__DIR__."/.././img/".$sImg.".jpg")?'<img src="'.__DIR__.'/.././img/'.$sImg.".jpg".'" width="62" height="20" />':$aArrInfoAbo["idtitre"]);?></div>
																<div class="div_td nbex<?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?><?php echo (($aArrInfoAbo["typedis"]=="NOUVEAUX")?' nouveau_abo':"");?>"><?php echo $aArrInfoAbo["nbex"];?></div>
																<div class="div_td infos_portage<?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo $aArrInfoAbo["InfosPortage"];?></div>
																<div class="div_td <?php echo (($aArrInfoAbo["typedis"]=="NOUVEAUX")?'nouveau_abo':"num_abonne");?><?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo $aArrInfoAbo["numabo"];?></div>
																<div class="div_td rang_livraison<?php echo $sCssRowspan;?><?php echo ((($iNbTitresParRue==$iL && $bTournee==true) || $bTournee==false)?" div_par_rue_bas":"");?>"><?php echo $iRangAdresseAAfficher;?></div>
																<?php 
																$sCssRowspan = "";
																?>
															</div>
															<?php 
															$iHautOccupeTmp += $iHautOccCetAbonne;
														}
													}
												}
												?>
											</div>
										</div>
									<?php 
										$iHautOccupeTmp += $iHautBordure;
									}
								}
							}
						}
						if($bBr == true)
						{
							echo '<br/><br/>';
							$iHautOccupeTmp += ($iHaut_br*2);
							$bBr = false;
						}
					}
				}
			}
		}
	}
	?>
	</body>
</html>
<?php 
}

