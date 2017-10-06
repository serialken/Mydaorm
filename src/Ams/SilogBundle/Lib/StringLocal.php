<?php 
namespace Ams\SilogBundle\Lib;

use Ams\SilogBundle\Lib\String;

/**
 * 
 * Divers methodes concernant les String
 * 
 * @author aandrianiaina
 *
 */
class StringLocal extends String
{
	public function setString($sStr)
    {
    	if(!is_null($sStr))
    	{
        	$this->_string = (string)$sStr;
    	}
    }
    
	/**
	 * 
	 * Suppression des quotes
	 * @param string $sStr
	 * @return string
	 */
	public function supprQuotes($sStr=null)
    {
    	if(is_null($sStr))
    	{
    		$sStr	= $this->_string;
    	}
    	if(is_string($sStr))
    		return str_replace(array("'", '"'), array("", ""), $sStr);
    	else 
    		return $sStr;
    }
    
    
	/**
	 * 
	 * Suppression des accents
	 * @param string $sStr
	 * @return string
	 */
	public function supprAccents($sStr=null)
    {
    	if(is_null($sStr))
    	{
    		$sStr	= $this->_string;
    	}
    	$aARemplacer = array(	'Š', 'Œ', 'š', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 
			            		'Å', 'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 
			            		'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô', 'Ò', 'Ø', 'Õ', 'Ö', 
			            		'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å', 
			                	'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 
			                	'ì', 'ï', 'ñ', 'ó', 'ô', 'ò', 'ø', 'õ', 'ö', 'ß', 
			                	'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '£', '°');
        $aValRemplacement = array(	'S', 'OE', 's', 'oe', 'Y', 'E', 'AE', 'A', 'A', 'A', 
				            		'A', 'A', 'A', 'C', 'D', 'E', 'E', 'E', 'E', 'I', 
				            		'I', 'I', 'I', 'N', 'O', 'O', 'O', '0', 'O', 'O', 
				            		'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'ae', 'a', 'a', 
				                	'a', 'a', 'c', 'e', 'e', 'e', 'd', 'e', 'i', 'i', 
				                	'i', 'i', 'n', 'o', 'o', 'o', '0', 'o', 'o', 'B', 
				                	'u', 'u', 'u', 'u', 'y', 'y', 'L', ' ');

        if(is_string($sStr))
        	return str_replace($aARemplacer, $aValRemplacement, $sStr);
        else 
            return $sStr;
        
    }	
    
	/**
	 * 
	 * Suppression des quotes, accents et puis mise en majuscule
	 * @param string $sStr
	 * @return string
	 */
	public function supprQuotesAccents_Majuscule($sStr=null)
    {
    	if(is_null($sStr))
    	{
    		$sStr	= $this->_string;
    	}
    	if(is_string($sStr))
    	{
    		$sStr	= $this->supprQuotes($sStr);
    		$sStr	= $this->supprAccents($sStr);    		
    		return strtoupper($sStr);
    	}
    	else 
    		return $sStr;
    }
    
    
	/**
	 * 
	 * Verifie la validation d'un identifiant utilise dans l'application M-ROAD
	 * @param string $sID
	 * @return boolean
	 */
	public function isIdValide($sID=null)
    {
    	if(is_null($sID))
    	{
    		$sID	= $this->_string;
    	}
        $sRegexMailId	= "/^[0-9a-z]([-_.]?[0-9a-z])*$/i";
        if(preg_match($sRegexMailId, $sID))
        {
        	return true;
        }
    	return false;
    }	
	/**
	 * 
	 * Verifie la validation d'un email M-ROAD
	 * @param string $sMail
	 * @return boolean
	 */
	public function isMailValide($sMail=null)
    {
    	if(is_null($sMail))
    	{
    		$sMail	= $this->_string;
    	}
		if (filter_var($sMail, FILTER_VALIDATE_EMAIL)) 
		{
		    return true;
		}
    	return false;
    }
    
	/**
	 * 
	 * Les elements d'une date
	 * @param string $sFormat
	 * @param string $sDate
	 * @return array
	 */
	public function eltsDate($sFormat='JJ/MM/AAAA hh:mm:ss', $sDate=null)
	{
    	if(is_null($sDate))
    	{
    		$sDate	= $this->_string;
    	}
    	$aRetour	= array();
		if(strlen($sFormat)!=strlen($sDate))
    	{
    		return $aRetour;
    	}
		$aFormat 	= str_split($sFormat, 1);
    	$aDate		= str_split($sDate, 1);
    	$aRetour		= array('JOUR'=>'', 'MOIS'=>'', 'ANNEE'=>'', 'HEURE'=>'', 'MINUTE'=>'', 'SECONDE'=>'');
    	foreach($aFormat as $iC => $sV)
    	{
    		switch ($sV)
    		{
    			case 'J':
    				$aRetour['JOUR']	.= $aDate[$iC];
    				break;
    				
    			case 'M':
    				$aRetour['MOIS']	.= $aDate[$iC];
    				break;
    				
    			case 'A':
    				$aRetour['ANNEE']	.= $aDate[$iC];
    				break;
    			case 'h':
    				$aRetour['HEURE']	.= $aDate[$iC];
    				break;
    				
    			case 'm':
    				$aRetour['MINUTE']	.= $aDate[$iC];
    				break;
    				
    			case 's':
    				$aRetour['SECONDE']	.= $aDate[$iC];
    				break;
    		}
    	}
    	
    	if(strlen($aRetour['ANNEE'])==2)
    	{
    		$aRetour['ANNEE']	= '20'.$aRetour['ANNEE'];
    	}
    	
    	if($aRetour['JOUR']=='')
    	{
    		unset($aRetour['JOUR']);
    	}
    	if($aRetour['MOIS']=='')
    	{
    		unset($aRetour['MOIS']);
    	}
    	if($aRetour['ANNEE']=='')
    	{
    		unset($aRetour['ANNEE']);
    	}
    	if($aRetour['HEURE']=='')
    	{
    		unset($aRetour['HEURE']);
    	}
    	if($aRetour['MINUTE']=='')
    	{
    		unset($aRetour['MINUTE']);
    	}
    	if($aRetour['SECONDE']=='')
    	{
    		unset($aRetour['SECONDE']);
    	}
    	if(!isset($aRetour) || empty($aRetour))
    	{
    		return array();
    	}
		else 
		{
			return $aRetour;
		}
	}
    
	/**
	 * 
	 * Verifie la validation d'une date
	 * @param string $sFormat
	 * @param string $sDate
	 * @return boolean
	 */
	public function isDateValide($sFormat='JJ/MM/AAAA', $sDate=null)
    {
    	$bRetour	= false;
    	if(is_null($sDate))
    	{
    		$sDate	= $this->_string;
    	}
    	$aArr	= $this->eltsDate($sFormat, $sDate);
    	if(!isset($aArr['JOUR']) || !isset($aArr['MOIS']) || !isset($aArr['ANNEE']))
    	{
    		return $bRetour;
    	}
    	return checkdate( intval($aArr['MOIS']), intval($aArr['JOUR']), intval($aArr['ANNEE']) );
    }
    
    /**
     * 
     * Transforme une date en d'autre format. $sFormatAncien est le format A-M-J-h-m-s. $sFormatFutur : format PHP (d-m-y-Y-...)
     * @param string $sFormatAncien
     * @param string $sFormatFutur
     * @param string $sDate
     */
    public function changeFormatDate($sFormatAncien='JJ/MM/AAAA hh:mm:ss', $sFormatFutur='d/m/Y', $sDate=null)
    {
    	if(is_null($sDate))
    	{
    		$sDate	= $this->_string;
    	}
    	$aArr	= $this->eltsDate($sFormatAncien, $sDate);
    	$iJour	= (isset($aArr['JOUR']) ? intval($aArr['JOUR']) : 0);
    	$iMois	= (isset($aArr['MOIS']) ? intval($aArr['MOIS']) : 0);
    	$iAnnee	= (isset($aArr['ANNEE']) ? intval($aArr['ANNEE']) : 0);
    	$iHeure	= (isset($aArr['HEURE']) ? intval($aArr['HEURE']) : 0);
    	$iMinute = (isset($aArr['MINUTE']) ? intval($aArr['MINUTE']) : 0);
    	$iSeconde = (isset($aArr['SECONDE']) ? intval($aArr['SECONDE']) : 0);
    	
    	return date($sFormatFutur, mktime($iHeure, $iMinute, $iSeconde, $iMois, $iJour, $iAnnee));
    }
    
	/**
     * Retourne le nombre de lignes si le nombre de caracteres par ligne est limite
     * @param string $sStr
     * @param int $_iNbCharPerLine
     * 
     * @return int
     */
    public function nbLines ($sStr=null, $_iNbCharPerLine = 75)
    {
    	if(is_null($sStr))
    	{
    		$sStr	= $this->_string;
    	}
    	$sBreak = "{|}";
    	$_sStr = wordwrap($sStr, $_iNbCharPerLine, $sBreak);
    	$aStr = explode($sBreak, $_sStr);
    	return count($aStr);
    }
    
    /**
     * 
     * Renommer un fichier en vue de son sauvegarde 
     * @param string $sStr
     * @return string
     */
    public function renommeFicDeSvgrde($sStr=null, $sDateCourantYmd=null, $sHeureCourantYmd=null)
    {
    	if(is_null($sStr))
    	{
    		$sStr	= $this->_string;
    	}
    	if(is_null($sDateCourantYmd))
    	{
    		$sDateCourantYmd	= date("Ymd");
    	}
    	if(is_null($sHeureCourantYmd))
    	{
    		$sHeureCourantYmd	= date("His");
    	}
    	
    	$sHeureCourant = $sDateCourantYmd.$sHeureCourantYmd;
		$aTab = explode(".", $sStr);
		$sRetour = "";
		if(count($aTab)==1)
		{
			$sRetour = $aTab[0]."_".$sHeureCourant;
		}
		else 
		{
			$aRetourTmp = array();
			for($iI=0; $iI<(count($aTab)-1); $iI++)
			{
				$aRetourTmp[]	= $aTab[$iI];
			}
			$sRetour	.= implode(".", $aRetourTmp);
			$sRetour	.= "_".$sHeureCourant;
			$sRetour	.= ".".$aTab[(count($aTab)-1)];
		}
		return $sRetour;    	
    }
		
	/**
	 * @author Andry
	 * Transformation d'un nom en un autre format.  
	 * 
	 * Normalement, $sRegexFormat doit etre de format ****=>xyz{{[0-9]}}abcd{{[0-9]}}...	
	 * 	où : 	**** : regex du nom d'entree. On ne prend que les noms repondant a ce regex 
	 * 			=> : séparateur obligatoire
	 * 			xyz{{[0-9]}}abcd{{[0-9]}}... : nom de sortie
	 * 
	 * Expl : 	si $sNomFichierEntree="feuille_portage_20130124_035.pdf"; $sRegexFormat = "(feuille_portage)_([0-9]{8})_([0-9]{3})(\.pdf)=>{{3}}_FeuillePortage{{2}}.pdf";
	 * 			A la sortie, on a renommeAvecRegex($sNomFichierEntree, $sRegexFormat) => "035_FeuillePortage20130124.pdf"
	 *
	 * @param string $sNomFichierEntree
	 * @param string $sRegexFormat
	 * @return string
	 */
	public function renommeAvecRegex ($sNomFichierEntree=null, $sRegexFormat = '')
	{
		if(is_null($sNomFichierEntree))
    	{
    		$sNomFichierEntree	= $this->_string;
    	}
		$aBornesRegex = array("/", "#");
		$sSepReginRegout = "=>"; // Separateur Regex d'entree et de Regex de sortie
		if (trim($sRegexFormat)=="")
		{
			return str_replace("./", "",$sNomFichierEntree);
		}
		else if(!preg_match("/.+".$sSepReginRegout.".+/i", $sRegexFormat))
		{
			return str_replace("./", "",$sNomFichierEntree);
		}
		else
		{
			$sRegexFormat = $this->transformeRegex($sRegexFormat); // transforme les parametres de type `date_dmY_<<XX>>_<<YY>>` en un vrai regex
			$a_regexFormat	= (explode($sSepReginRegout, $sRegexFormat));
			$sRegexEntree	= $a_regexFormat[0];
			$sFormat0Sortie	= $a_regexFormat[1];
			
			if(!in_array(substr($sRegexEntree, 0, 1), $aBornesRegex))
			{
				$sRegexEntree = "/".$sRegexEntree."/i";
			}
			
			// recuperation des valeurs des param du nom de fichier d'entree
			preg_match($sRegexEntree, $sNomFichierEntree, $aArrValRegEntree); // Si $sRegexEntree="/(feuilleportage)([0-9]{8})([0-9]{3})(\.pdf)/" et $sNomFichierEntree="feuilleportage20130124029.pdf" 
			
			// Format nom sortie
			// -- Si on doit mettre la date courante par exemple
			$sRegDateSortie = "/{{date_.+}}/";
			preg_match_all($sRegDateSortie, $sFormat0Sortie, $aRegDateSortie);			
			if(isset($aRegDateSortie[0][0]))
			{
				$sFormatDateSortie = str_replace("{{date_", "", $aRegDateSortie[0][0]);
				$sFormatDateSortie = str_replace("}}", "", $sFormatDateSortie);
				$sFormat0Sortie = str_replace($aRegDateSortie[0][0], date($sFormatDateSortie), $sFormat0Sortie);
			}
			
			$sRegexParamSortie = "/{{[0-9]+}}/"; // Ceci afin de recuperer les chiffres entre "{{" et "}}"
			
			preg_match_all($sRegexParamSortie, $sFormat0Sortie, $aArrRegSortie);
			
			$aReplace = array();
			foreach($aArrRegSortie as $aArrParamV)
			{
				foreach($aArrParamV as $sV)
				{
					$iV = str_replace(array("{{", "}}"), "", $sV);
					$aReplace[$sV] = (isset($aArrValRegEntree[$iV])?$aArrValRegEntree[$iV]:$sV);
				}
			}
				
			$sNomFichierSortie	= $sFormat0Sortie;
			foreach($aReplace as $sK => $sV)
			{
				$sNomFichierSortie = str_replace($sK, $sV, $sNomFichierSortie);
			}
			
			return $sNomFichierSortie;
		}
	}
	

	/**
	 * @author Andry
	 * Transformation d'un regex avec des variables supplementaires en un vrai regex. Les variables supplementaires sont mis entre "`". 
	 * Les variables supplementaires traitees sont : 
	 * 	- `date_dmY_<<XX>>_<<YY>>` ou <<XX>> et <<Y>> sont un nombre de jours 
	 *  	Exemple :   `date_dmY_-1_1` <=> dates de format "dmY" entre J-1 et J+1 incluses
	 * 
	 * @param string $sRegexFormat
	 */
	function transformeRegex ($sRegexFormat=null)
	{
		if(is_null($sRegexFormat))
    	{
    		$sRegexFormat	= $this->_string;
    	}
		$sRetour	= "";
		$aRegex0	= explode('`', $sRegexFormat);
		$aRegexTmp	= array ();
		foreach ($aRegex0 as $sPartRegex)
		{
	
			if (preg_match('/^date\_[a-z]+\_[\-0-9]+\_[\-0-9]+$/i', $sPartRegex))	// expl : date_Ymd_1_12 => signifie dates de format "Ymd" entre J+1 et J+12 incluses
			{
				$aDateTmp	= array ();
				$aPartRegex	= explode ('_', $sPartRegex);
				$sFormatDate	= $aPartRegex[1];
				$iJMin	= intval($aPartRegex[2]);
				$iJMax	= intval($aPartRegex[3]);
				for ($iI=$iJMin; $iI<=$iJMax; $iI++)
				{
				$aDateTmp[]	= date($sFormatDate, (time() + ($iI * 24 * 60 * 60)));
				}
				$aRegexTmp[]	= '('.implode('|', $aDateTmp).')';
			}
			else
			{
			$aRegexTmp[]	= $sPartRegex;
			}
		}
					
		$sRetour	= implode ('', $aRegexTmp);
		$sRetour = str_replace("((", "(", $sRetour); // necessaire pour supprimer les "(" et ")" supplementaires dus aux dates date_Ymd_***. Expl : (feuille_portage)_(`date_Ymd_1_1`)_([0-9]{3})(\.pdf) transforme en (feuille_portage)_((20130212))_([0-9]{3})(\.pdf) => Les "((" perturbent la recuperation de valeurs respectant le regex 
		$sRetour = str_replace("))", ")", $sRetour);
			
		return $sRetour;
	}
}
