<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace model;
class TempCredentialsDAL {
	/**
	 * 
	 * @param  String $userName [description]
	 * @return TempCredentials           [description]
	 */
	public function load($userName) {
		if ( file_exists(self::getFileName($userName)) ) {
			$fileContent = file_get_contents(self::getFileName($userName));
			if ($fileContent !== FALSE)
			{
				return unserialize($fileContent);
			}
		}
		return null;
	}
	
	public function save(LoggedInUser $user, TempCredentials $t) {
		file_put_contents( self::getFileName($user->getUserName()), serialize($t) );
	}
	private function getFileName($userName) {
		//TODO: replace the addslashes with something that makes username safe for use in filesystem
		return \Settings::DATAPATH . addslashes($userName);
	}
}