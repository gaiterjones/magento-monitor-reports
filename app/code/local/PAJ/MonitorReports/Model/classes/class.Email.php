<?php
/**
 *  GaiterJones - http://blog.gaiterjones.com
 *  
 *  Email class.
 *  
 *  
 *  
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
 *  @package    MonitorReports/class
 *  @license    http://www.gnu.org/licenses/ GNU General Public License
 * 
 *
 */

class GaiterMail{
    var $to = null;
    var $from = null;
    var $subject = null;
    var $body = null;
    var $headers = null;
	var $attachment = null;
	var $filename = null;

     function GaiterMail($to,$from,$subject,$body,$attachment,$filename){
        $this->to      = $to;
        $this->from    = $from;
        $this->subject = $subject;
        $this->body    = $body;
		$this->attachment    = $attachment;
		$this->filename    = $filename;
    }

    function send(){
		$uid = md5(uniqid(time()));
		$fqdn_hostname="dev.gaiterjones.com";
		$sMessageId="<" . sha1(microtime()) . "@" . $fqdn_hostname . ">";
		$this->addHeader('From: '.$this->from."\r\n");
        $this->addHeader('Reply-To: '.$this->from."\r\n");
        $this->addHeader('Return-Path: '.$this->from."\r\n");
		$this->addHeader("Message-ID: " .$sMessageId. "\r\n");
		$this->addHeader('X-mailer: GaiterMail 1.0'."\r\n");
		$this->addHeader("MIME-Version: 1.0\r\n");
	    $this->addHeader("Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n" );
		$this->addHeader("This is a multi-part message in MIME format.\r\n");
		$this->addHeader("--".$uid."\r\n");
		$this->addHeader("Content-type:text/plain; charset=iso-8859-1\r\n");
		$this->addHeader("Content-Transfer-Encoding: 7bit\r\n\r\n");
		$this->addHeader($this->body."\r\n\r\n");
		$this->addHeader("--".$uid."\r\n");
		$this->addHeader("Content-Type: application/octet-stream; name=\"".$this->filename."\"\r\n");
		$this->addHeader("Content-Transfer-Encoding: base64\r\n");
		$this->addHeader("Content-Disposition: attachment; filename=\"".$this->filename."\"\r\n\r\n");
		$this->addHeader($this->attachment."\r\n\r\n");
		$this->addHeader("--".$uid."--");		

		
		
        mail($this->to,$this->subject,"",$this->headers);
    }

    function addHeader($header){
        $this->headers .= $header;
    }

}
?>