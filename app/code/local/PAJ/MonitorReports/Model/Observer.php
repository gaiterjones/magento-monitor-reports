<?php
/**
 *  GaiterJones - http://blog.gaiterjones.com
 *  
 *  Monitor Magento var/reports folder and send email alerts
 *  
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com 22.05.2015 v0.03
 *
 *	This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @category   PAJ
 *  @package    MonitorReports
 *  @license    http://www.gnu.org/licenses/ GNU General Public License
 * 
 *
 */
class PAJ_MonitorReports_Model_Observer {

	public static function monitorfolder()
	{

		// declare variables
		$appRoot= Mage::getRoot();
		
		$DIRECTORY		= dirname($appRoot). "/var/report/";
		$EMAILTO		= Mage::getStoreConfig('monitorreports_section1/general/monitorreport_email_to');
		$EMAILFROM		= Mage::getStoreConfig('monitorreports_section1/general/monitorreport_email_from');
		$CACHEFILE		= Mage::getModuleDir('', 'PAJ_MonitorReports') . DS . 'Model/cache'. DS . md5($DIRECTORY);
		$EMAILSUBJECT	= "Magento var/report Folder Monitor Alert";
	
	
		try
		{
	
			// get file count
			if (glob("$DIRECTORY*") != false)
			{
				$iNewFileCount = count(glob("$DIRECTORY*"));
			} else {
				$iNewFileCount = 0;
			}

			// compare file counts
			if (file_exists($CACHEFILE)) {
				$iOldFileCount = file($CACHEFILE);
				$iOldFileCount = $iOldFileCount[0];
			} else {
				$iOldFileCount=NULL;
				// create cache file
				
				try
				{
					$fp = fopen($CACHEFILE, 'w');
					fwrite($fp, "0");
					fclose($fp);
				}	
				catch (Exception $e)
				{
					echo 'Error while creating new cache file.' . "\n";
				}
			}
	
			$newFiles = $iNewFileCount-$iOldFileCount;
			
			if ($iNewFileCount <> $iOldFileCount && $iOldFileCount <> NULL)
			{

					try // write new cache
					{
						$fp = fopen($CACHEFILE, 'w');
						fwrite($fp, $iNewFileCount);
						fclose($fp);
					}
					catch (Exception $e)
					{
						echo 'Error while writing cache file.' . "\n";
					}
					
				if ($newFiles > 0)
				{
					try // file count changed, get newest report and send alert
					{
						$dir = $DIRECTORY;
						$dh = opendir($dir);
						$last = 0;
						$filename = "";
						while (($file = readdir($dh)) !== false){
							if(is_file($dir.$file)){
								$mt = filemtime($dir.$file);
								if($mt > $last){
									$last = $mt;
									$filename = $file;
								}
							}
						}
						closedir($dh);

					}
					catch (Exception $e)
					{
						echo 'Error while checking file name and date.' . "\n";
					}

					$to = $EMAILTO;
					$from = $EMAILFROM;
					$subject = $EMAILSUBJECT;
					$fullfilename = $DIRECTORY. $filename;
					$attachment = chunk_split(base64_encode(file_get_contents($fullfilename))); 
					$body = "New files detected in Magento reports folder ". $DIRECTORY;

					if ($newFiles >= 1)
					{
						$body = $body. ". There are ". $newFiles. " new reports";
					}
					
					$body = $body. ". Latest report file ". $filename. " was created ". date("l, j F Y h:i:s A",filemtime($fullfilename)). " and is attached to this email - filename ". $fullfilename. ".";
					// classes
					$ExternalLibPath=Mage::getModuleDir('', 'PAJ_MonitorReports') . DS . 'Model/classes' . DS .'class.Email.php';
					require_once ($ExternalLibPath);					
					$oMail = new GaiterMail($to,$from,$subject,$body,$attachment,$fullfilename);
					// send email
					$oMail->send();
					
				}
			}
		}
		catch (Exception $e)
		{
			Mage::printException($e);
		}
	}
}
?>
