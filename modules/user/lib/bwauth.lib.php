<?php
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */

require "auth.lib.php";

class MOD_bw_user_Auth extends MOD_user_Auth
{
    /**
     * @param string $sessionName The session key under which the user id may be found
     * @param string $tableName The user table name
     * @param int $authId The authentication id
     */
    public function __construct($sessionName = false, $tableName = false, $authId = false) 
    {
        parent::__construct($sessionName, $tableName);
        $this->authId = $authId;
    }
    
	protected function doLogin( $handle, $password ) 
 	{
 		$this->logout();
 	
        if (!isset($handle)||!isset($password))
            return false;
            
        $handle = trim($handle);
        $password = trim($password);
 	
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle))
            return false;

        if ($this->doBWLogin( $handle, $password ))
        {
        	parent::doLogin( $handle, $password);
        	$this->setupBWSession( $handle );
        	$this->updateUser( $handle, $password );
        	parent::doLogin( $handle, $password);
        	
       		// Sanity check
			if (!$this->isBWLoggedIn())
			{
				throw new PException('Login sanity check failed miserably!');
			}        	
        	
        	return true;        	
        }
        
		return true;
    }

    protected function doBWLogin( $handle, $password )
    {
    	global $_SYSHCVOL;
	
		//if (CountWhoIsOnLine() > $_SYSHCVOL['WhoIsOnlineLimit']) {
		//	refuse_login(ww("MaxOnlineNumberExceeded", $_SESSION['WhoIsOnlineCount']), $nextlink,"");
		//}
	
	
		// Deal with the username which may have been reused
//		$rr = LoadRow("select Username,ChangedId from members where Username='" . $Username . "'");
//		$count = 0;
//		while ($rr->ChangedId != 0) {
//			$rr = LoadRow("select Username,ChangedId from members where id=" . $rr->ChangedId);
//			$Username = $rr->Username;
//			$count++;
//			if ($count > 100) {
//				LogStr("Infinite loop in Login with " . $Username, "Bug");
//				break; // 
//			}
//		}
		// End of while with the username which may have been reused
	
		$query = "SELECT id,Status FROM members WHERE Username='" . $handle . "' and PassWord=PASSWORD('" . $password . "')";

    	$s = $this->dao->query($query);
		if (!$s) 
		{
			throw new PException('Weird shit!');
		}
		if (!$m = $s->fetch(PDB::FETCH_OBJ)) {
			return false;
		}		
		
		if (empty($m->id))
			return false;
					
		// Process the login of the member according to his status
		switch ($m->Status) {
			case "ChoiceInactive" :  // case an inactive member comes back
			case "Active" :
			case "ActiveHidden" :
				break;
	
			case "ToComplete" :
				LogStr("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				header("Location: ".bwlink("completeprofile.php"));
				exit(0);
	
			case "NeedMore" :
				header("Location: ".bwlink("updatemandatory.php"));
				exit(0);
				break;
	
			case "Banned" :
			case "TakenOut" :
			case "CompletedPending" :
			case "Pending" :
			case "SuspendedBeta" :
			default:
				return false;
		}
		
		return true;
    }    
    
    protected function setupBWSession( $handle )
    {
    	$query = "SELECT * FROM members WHERE Username='" . $handle . "'";
    	$s = $this->dao->query($query);
		if (!$s) 
			throw new PException('Weird stuff!');

		if (!$m = $s->fetch(PDB::FETCH_OBJ)) 
			throw new PException('Weird stuff!');
    
		// Set the session identifier
		$_SESSION['IdMember'] = $m->id;
		$_SESSION['Username'] = $m->Username;
		$_SESSION['Status'] = $m->Status;
		
		if ($_SESSION['IdMember'] != $m->id)
		{ // Check is session work of
			$this->logout();
			throw new PException('Login sanity check failed miserably!');
		}; // end Check is session work of
	
		$_SESSION['MemberCryptKey'] = crypt($m->PassWord, "rt"); // Set the key which will be used for member personal cryptation
		$_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']); // Set the key for checking id and LohCheck (will be restricted in future)
	
		$this->dao->query("update members set LogCount=LogCount+1,LastLogin=now() where id=" . $_SESSION['IdMember']); // update the LastLogin date
	
		// Load language prederence (IdPreference=1)
		$s = $this->dao->query("select memberspreferences.Value,ShortCode from memberspreferences,languages where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=1 and memberspreferences.Value=languages.id");
		if (!$s) 
		{
			throw new PException('Weird stuff!');
		}
		$langprefs = $s->fetch(PDB::FETCH_OBJ);
		
		if (isset ($langprefs->Value)) 
		{ // If there is a member selected preference set it
			$_SESSION["IdLanguage"] = $langprefs->Value;
			$_SESSION["lang"] = 	  $langprefs->ShortCode;
		}
		
    	// Process the login of the member according to his status
		switch ($m->Status) {
			case "ChoiceInactive" :  // case an inactive member comes back
				$this->dao->query("update members set Status='Active' where members.id=".$m->id." and Status='ChoiceInactive'") ;
				$_SESSION['Status'] = $m->Status='Active' ;
			case "Active" :
			case "ActiveHidden" :
				//if (HasRight("Words"))
				//	$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
				break;
		
			default:
				throw new PException('SetupBWSession Weird Status!');
				break;
		}
    }
    
    protected function updateUser($handle,$password)
    {
    	$pwenc = MOD_user::passwordEncrypt($password);
        $Auth = new MOD_user_Auth;
		$authId = $Auth->checkAuth('defaultUser');
		$query = '
REPLACE `user` 
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`) 
VALUES 
(
    '.$this->dao->nextId('user').', 
    '.(int)$authId.', 
    \''.$this->dao->escape($handle).'\', 
    \'\', 
    \''.$this->dao->escape($pwenc).'\', 
    1
)';
		$s = $this->dao->query($query);
    }
    
	function isBWLoggedIn() 
	{
		if (empty($_SESSION['IdMember'])) {
			return false;
		}
	
		if (empty($_SESSION['MemberCryptKey'])) {
			return false;
		}
	
		if ($_SESSION['LogCheck'] != Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember'])) 
		{
			$this->logout();
			return false;
		}
		return true;
	} 
    
	function logout()
	{
		if (isset($_SESSION['IdMember'])) 
		{
			// todo optimize periodically online table because it will be a gruyere 
			// remove from online list
			$query = "delete from online where IdMember=" . $_SESSION['IdMember'];
			$this->dao->query($query);
	
			unset($_SESSION['WhoIsOnlineCount']);
			unset($_SESSION['IdMember']);
			unset($_SESSION['IsVol']);
			unset($_SESSION['Username']);
			unset($_SESSION['Status']);
			unset($_SESSION["stylesheet"]) ;
		}
		if (isset($_SESSION['MemberCryptKey']))
			unset($_SESSION['MemberCryptKey']);
		
		parent::logout();
	}
	
 }
?>