<?php

/**
* @version		1.1 RC1 2008-11-20 21:18:00 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

/**
* The PostMaster class is used for sending mail via the web server.
* postmaster provides two methods for sending mail:
* PHP's mail() function and the Linux sendmail process.
*
* The mail message is put together the same regardless of which method is
* used. Mail messages can optionally be written to a text file for backup
* purposes as well.
*
* @package SkyBlue
*/

class Postmaster {

    // Postmaster configuration settings
    
    var $errorcheck     = 0;       // 1 will return errors and write error log 
                                   // in email directory, 0 will not
    var $textversion    = 0;       // 1 will write a text backup of the email

    var $form           = NULL;    // This is the data posted by the HTML form
    var $recepient      = NULL;    // The email address to send mail to
    var $from           = NULL;    // The name of the sender
    var $replyto        = NULL;    // The email address of the sender
    var $cc             = NULL;    // Email addresses of additional recepients (comma-delimited)
    var $bc             = NULL;    // Email addresses of blind copies (comma-delimited)
    var $subject        = NULL;    // Mail subject
    var $message        = NULL;    // Mail body
    var $attachment     = NULL;    // Path to any attachments
    
    var $headers        = NULL;    // The complete Mail headers
    
    var $fulltext       = NULL;    // The full message, with headers for writing to text file
    var $path           = NULL;    // Only use this if you are writing mail to text file
    
    var $errorcode      = 1;       // Use this for monitoring success or failure
    var $errors         = array(); // An array of errors - also use for form validation
    
    // You should not modify these properties unless you
    // know what you are doing. Otherwise your mail may
    // not get sent by the mailer daemon.
    
    var $mimeversion     = '1.0';
    var $contenttype     = 'text/plain; charset=iso-8859-1';
    var $contenttransfer = '8bit';
    var $priority        = 1;
    var $msmailpriority  = 'High';
    var $mailer          = NULL;
    
	function __construct() {
        ;
    }
    
    function Postmaster() {
        $this->__construct();
    }    
    
    // FilterForm() will filter array key->value pairs from the posted form.
    // $omitkeys is an array of the key names to omit.
    //
    // Example: Using the $_POST array
    //
    // array(
    //        ['name']=>'Scott Lewis'
    //        ['email']=>'scott@catalystworkflow.com'
    //        ['subject']=>'Thank You'
    //        ['message']=>'Thanks for buying SSM!'
    //        ['submit']=>'Send'
    //     )
    //
    // $postmasert->filterForm(array('submit'));
    //
    // Will return:
    //
    // array(
    //        ['name']=>'Scott Lewis'
    //        ['email']=>'scott@catalystworkflow.com'
    //        ['subject']=>'Thank You'
    //        ['message']=>'Thanks for buying SSM!'
    //     )
    //
    // Additionally, filterForm() will trim leading and trailing
    // white space from form values.
    // You should run this filter on all forms even if you are not
    // planning to omit any key=>value pairs.
    
    function filterForm($omitkeys=array()) {
        $clean = array();
        foreach ($this->form as $k=>$v) {
            if (!in_array($k, $omitkeys)) {
                $clean[$k] = trim($v);
            }
        }
        $this->form = $clean;
    }
    
    function SetMessage($msg) {
        $this->message = str_replace('{date}', date("r"), wordwrap($msg, 70));
    }
    
    function buildHeaders() {
        $this->headers  = "MIME-Version: ".$this->mimeversion."\r\n";
        $this->headers .= "Content-type: ".$this->contenttype."\r\n";
        $this->headers .= "Content-Transfer-Encoding: ".$this->contenttransfer."\r\n";
        $this->headers .= "From: ".$this->replyto."\r\n";
        $this->headers .= "Date: ".date("r")."\r\n";
        $this->headers .= "Reply-To: ".$this->replyto."\r\n";
        $this->headers .= "X-Priority: ".$this->priority."\r\n";
        $this->headers .= "X-Mailer: PHP/".phpversion()."\r\n";
    }
    
    function buildTextVersion() {
        $this->fulltext  = date('d M\, Y l h:i:s A')."\n\n";
        $this->fulltext .= $this->headers."\n";
        $this->fulltext .= $this->message."\n";
    }
    
    function backup() {
        global $Core;
        if ($this->backup == 1 && file_exists($this->path)) {
            if (empty($this->headers)) {
                $this->buildHeaders();
            }
            $this->buildTextVersion();
            $file = $this->path.$this->replyto.'.'.rand().'.txt';
            $this->errorcode = $Core->writeFile($file, $this->fulltext, 1) == 1 ? 1 : -3 ;
        }
    }

    function ssmMail() {
        $this->buildHeaders();
        $this->backup();
        if ($this->errorcheck && count($this->errors)) {
            $log  = '# Mail Errors'."\n\n";
            $log .= implode("\r\n\r\n", $this->errors);
            $Core->writeFile($this->path.'error.mail.log', $log, 1);
        }
		if (!empty($this->replyto)) {
		    ini_set('sendmail_from', $this->replyto);
		}
		if (mail(
		    $this->recepient, 
		    $this->subject, 
		    $this->message, 
		    $this->headers
		)) {
		    $this->errorcode = 1;
		}
		else {
		    $this->errorcode = -2;
		}
	}

    function ssmSendMail() {
        $this->buildHeaders();
        $this->backup();
        $this->errorcode = mail($this->recepient, $this->subject, $this->message, $this->headers);
    }
    
    function Usage() {
		return str_replace('#','$',"<pre>Postmaster example usage:\r\n\r\n".
		"#Core->postmaster->errorcheck = 1;\r\n".
		"#Core->postmaster->backup     = 1;\r\n".
		"#Core->postmaster->path       = 'email/'; // Directory for text backups\r\n\r\n".
		"#Core->postmaster->from       = 'from@address.com';\r\n".
		"#Core->postmaster->replyto    = 'replyto@address.com';\r\n".
		"#Core->postmaster->recepient  = 'to@address.com';\r\n".
		"#Core->postmaster->subject    = 'The Email Subject';\r\n\r\n".
		"#Core->postmaster->SetMessage('The message body...');\r\n".
		"#Core->postmaster->ssmMail();\r\n</pre>");
	}

}

?>