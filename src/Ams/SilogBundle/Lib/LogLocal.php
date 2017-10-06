<?php 
namespace Ams\SilogBundle\Lib;

/**
 * 
 * Stockage des Logs
 * @author aandrianiaina
 *
 */
class LogLocal
{
	private $bFileLogInfo;
	private $bFileLogErr;
	private $bMailLogErr;
	
	private $sPathLogInfo;
	private $sPathLogErr;
	public  $sRecipientMailLogErr;
	
	private $sIdFileLog;
	private $sFileLogInfo;
	private $sFileLogErr;
	
	private $oLogInfo;
	private $oLogErr;
	
	static private $_instance = null;
	
	private $aMsgLog;
	private $iNbErr;
	
	private $aErrorType;
	
	private $LN;
	
	/**
	 * 
	 * $_bFileLogInfo=true si on veut stocker dans un fichier les messages d'info 
	 * $_bFileLogErr=true si on veut stocker dans un fichier les messages d'erreur
	 * $_bMailLogErr=true si on veut envoyer par mail les messages d'erreur
	 * @param boolean $_bFileLogInfo
	 * @param boolean $_bFileLogErr
	 * @param boolean $_bMailLogErr
	 */
	public function __construct($_bFileLogInfo=false, $_bFileLogErr=true, $_bMailLogErr=true)
	{
		$this->LN	= "\n";
		$this->aMsgLog = array();
		// http://php.net/manual/fr/errorfunc.constants.php
		$this->aErrorType = array (
					                E_ERROR             => 'E_ERROR', /*Erreur entrainant l'interruption d'execution du script*/
					                E_WARNING           => 'E_WARNING',
					                E_PARSE             => 'E_PARSE',
					                E_NOTICE            => 'E_NOTICE',
					                E_CORE_ERROR        => 'E_CORE_ERROR',
					                E_CORE_WARNING      => 'E_CORE_WARNING',
					                E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
					                E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
					                E_USER_ERROR        => 'E_USER_ERROR',
					                E_USER_WARNING      => 'E_USER_WARNING',
					                E_USER_NOTICE       => 'E_USER_NOTICE',
					                E_STRICT            => 'E_STRICT',
					                E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
					                E_ALL 				=> 'E_ALL',
					                "EXCEPTION"			=> 'EXCEPTION'
					                );
					                
		$this->bFileLogInfo = $_bFileLogInfo;
		$this->bFileLogErr	= $_bFileLogErr;
		$this->bMailLogErr	= $_bMailLogErr;
		
		$this->sPathLogInfo	= "./";
		$this->sPathLogErr	= "./";
		$this->sIdFileLog	= "Log";
		$sDateYmdHis = date("Ymd_His");
		$sUniqid = "_".rand(0, 999999);
		//$sUniqid = "";
		$this->sFileLogInfo	= $this->sIdFileLog."_".$sDateYmdHis.$sUniqid.".log";
		$this->sFileLogErr	= $this->sIdFileLog."_err_".$sDateYmdHis.$sUniqid.".log";
		$this->oLogInfo = "";
		$this->oLogErr = "";
		$this->iNbErr	= 0;
		$this->sRecipientMailLogErr = "ydieng@lpmanagement.net"; //à commenter en Prod
//		$this->sRecipientMailLogErr = "log_it@proximy.fr"; //à décommenter en prod

	}
	
	/**
	 * 
	 * Capture les erreurs normales non "catch"-ees. Expl : Erreur de connexion a une base de donnees
	 * @param int $_iNumber
	 * @param string $_sMessage
	 * @param string $_sFile
	 * @param int $_iLine
	 */
	public function error_handler($_iNumber, $_sMessage, $_sFile, $_iLine)
	{
		$this->add_log($_sMessage, "error", (isset($this->aErrorType[$_iNumber])?$this->aErrorType[$_iNumber]:$_iNumber), $_sFile, $_iLine);
		//echo "error_handler($_iNumber, $_sMessage, $_sFile, $_iLine)<br>";
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param object $_oException
	 */
	public function exception_handler($_oException)
	{
		$this->add_log($_oException->getMessage(), "exception");
		//echo "<pre>";print_r($_oException);echo "</pre>";
		//echo "exception_handler<br>";
		//echo "<pre>"; print_r($_oException); echo "</pre>";
	}
	
	/**
	 * 
	 * Capture les erreurs "fatales" entrainant l'arret brutal d'execution du script.
	 */
	public function capture_shut_down()
	{
		$aError = error_get_last( );
	    if($aError) 
	    {
	    	$this->add_log($aError["message"], "fatal_error", (isset($this->aErrorType[$aError["type"]])?$this->aErrorType[$aError["type"]]:$aError["type"]), $aError["file"], $aError["line"]);
	    } 
		else 
		{
			//$this->add_log($this->aErrorType[1], "fatal_error", $this->aErrorType[1], "", "");
		}
		//echo "<pre>"; print_r($this->aMsgLog); echo "</pre>";
		
		$this->end_log();
	}
	
	public function info($_sMsg)
	{
		$this->add_log($_sMsg);
	}
	
	public function erreur($_sMsg, $_iErrType="", $_sErrFile="", $_sErrLine="")
	{
		$this->add_log($_sMsg, "error", $_iErrType, $_sErrFile, $_sErrLine);
		//trigger_error($_sMsg, $_iErrType);
	}
	
	public function getMsgLog()
	{
		return $this->aMsgLog;
	}
	
	private function add_log($_sMsg, $_sMsgType="info", $_iErrType="", $_sErrFile="", $_sErrLine="")
	{
		$aLog = array(	"date"		=> date("d/m/Y H:i:s"),
						"msg_type"	=> $_sMsgType,
						"err_type"	=> $_iErrType,
						"msg"		=> str_replace(array("\r\n","\n"), " ", $_sMsg),
						"err_file"	=> $_sErrFile,
						"err_line"	=> $_sErrLine
						);
		$this->aMsgLog[] = $aLog;
		if(!in_array($_sMsgType, array("info")))
		{
			$this->iNbErr++;
		}
		
		if($this->bFileLogInfo==true)
		{
			if($this->oLogInfo==="")
			{
				$this->oLogInfo = fopen($this->sPathLogInfo."/".$this->sFileLogInfo, "w+");
			}
			$this->write_log($this->oLogInfo, $aLog);
		}
	
		
		if($this->bFileLogErr==true && !in_array($_sMsgType, array("info")))
		{
			if($this->oLogErr==="")
			{
				$this->oLogErr = fopen($this->sPathLogErr."/".$this->sFileLogErr, "w+");
			}
			$this->write_log($this->oLogErr, $aLog);
		}
	}
	
	private function write_log ($_oFile, $_aArr)
	{
		$sStr = str_pad($_aArr["date"], 20, " ", STR_PAD_RIGHT)."|"
				. str_pad($_aArr["msg_type"], 20, " ", STR_PAD_RIGHT)."|"
				. str_pad($_aArr["err_type"], 30, " ", STR_PAD_RIGHT)."|"
				. str_pad(substr(str_replace(array("   ", "   ", "  ", "  ", "\t", "\r\n", "\n"), " ", $_aArr["msg"]), 0, 500), 500, " ", STR_PAD_RIGHT)."|"
				. str_pad($_aArr["err_file"], 200, " ", STR_PAD_RIGHT)."|"
				. str_pad($_aArr["err_line"], 10, " ", STR_PAD_RIGHT)."|"
				. str_pad((isset($_SESSION["login"])?$_SESSION["login"]:""), 50, " ", STR_PAD_RIGHT).$this->LN;
		fwrite($_oFile, $sStr);
                echo $_aArr["msg"].$this->LN;
	}
	
	/**
	 * 
	 * cree le repertoire $_sStr
	 * @param string $_sStr
	 * 
	 * @return string
	 */
	private function create_path($_sStr)
	{
		if(is_dir($_sStr))
		{
			return $_sStr;
		}
		else 
		{
			if(mkdir($_sStr, 0777, true))
			{
				return $_sStr;
			}
			else 
			{
				return "";
			}
		}
	}
	
	public function set_id_file_log($_sStr)
	{
		$this->sIdFileLog = $_sStr;
		$sDateYmdHis = date("Ymd_His");
		$sUniqid = "_".rand(0, 999999);
		//$sUniqid = "";
		
		$this->sFileLogInfo	= $this->sIdFileLog."_".$sDateYmdHis.$sUniqid.".log";
		$this->sFileLogErr	= $this->sIdFileLog."_err_".$sDateYmdHis.$sUniqid.".log";
	}
	
        public function get_file_log_info(){
            return $this->sFileLogInfo;
        }
        
        public function get_file_log_err(){
            return $this->sFileLogErr;
        }
        
	public function set_path_log_info($_sStr)
	{
		$this->sPathLogInfo = $this->create_path($_sStr);
	}
	
        public function get_path_log_info(){
            return $this->sPathLogInfo;
        }
        
	public function set_path_log_err($_sStr)
	{
		$this->sPathLogErr = $this->create_path($_sStr);
	}
	
        public function get_path_log_err(){
            return  $this->sPathLogErr;
        }
        
	public function set_recipient_mail($_sStr)
	{
		$this->sRecipientMailLogErr = $_sStr;
	}
	
        public function get_libelle_err_type($code){
            if (array_key_exists($code, $this->aErrorType)){
                return $this->aErrorType[$code];
            }else{
                return "ERROR";
            }
        }
	public function end_log()
	{
		$this->close_files_log();
		$this->send_mail();
	}
	
	private function close_files_log()
	{
		// Fichier de log
		if(isset($this->oLogInfo) && $this->oLogInfo!=="")
		{
			fclose($this->oLogInfo);
		}
		if(isset($this->oLogErr) && $this->oLogErr!=="")
		{
			fclose($this->oLogErr);
		}
	}
	
	private function send_mail()
	{
		if($this->bMailLogErr==true && $this->iNbErr>0)
		{
			echo "Envoi mail a ".$this->sRecipientMailLogErr;
		}
	}
	
	public function setLN($LN)
	{
		$this->LN	= "\n";
		$aFinLigne	= array(
							'\n' => "\n",
							'\r\n' => "\r\n",
							'\n\r' => "\n\r",
							);
		$this->LN	= (isset($aFinLigne[$LN]) ? $aFinLigne[$LN] : $this->LN);
		return $this->LN;
	}
}
/*
// Utilisation
$oLog = new LogLocal(true);
$oLog->set_id_file_log("test_id");
$oLog->set_path_log_info("./Log");
$oLog->set_path_log_err("./LogErr");
$oLog->info("Debut script");
set_error_handler(array($oLog, 'error_handler'));
set_exception_handler(array($oLog, 'exception_handler'));
register_shutdown_function(array($oLog, 'capture_shut_down'));

// Pour lancer une erreur utilisateur
trigger_error("Ceci est un message d'erreur", E_USER_ERROR);


try
{
	$db = mysql_connect('localhost', 'login', 'password'); 
	if($db)
	{

		// on selectionne la base 
		mysql_select_db('nom_de_la_base',$db); 
		
		// on cree la requete SQL 
		$sql = 'SELECT nom,prenom,statut,date FROM famille_tbl'; 
		
		// on envoie la requete 
		$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		echo "------------";
	}
	else {
		echo "erreur connexion.....";
	}
	
	echo "terst";
	echo 1+$a;
	echo "Apres somme";
	appel();
	echo "FinFin------------";
}
catch (Exception $e)
{
	echo "errreuuuur e : ".$e->getMessage();
}

var_dump($oLog->getMsgLog());
$oLog->end_log();

*/
