<?php

defined('SKYBLUE') or die(basename(__FILE__));

global $Core;
global $config;
global $Router;

$button = $Core->GetVar($_POST, 'submit', '');
$button = strToLower($button);

$msg   = $Core->GetVar($_GET, 'msg', '');
$token = $Core->token;

switch ($msg) {
    case 1:
        $msg  = "<div class=\"success\">\n";
        $msg .= "    <h2>Thank you for your message.</h2>\n";
        $msg .= "    <p>We will be in touch shortly.</p>\n";
        $msg .= "</div>\n";
        break;
    case 2:
    case 3:
    case 4:
        $msg  = "<div class=\"error\">\n";
        $msg .= "<h2>Your message could not be sent. Error: $msg</h2>\n";
        $msg .= "<p>Please email me at <a href=\"mailto:" . 
            $config['contact_email'] . "\">" . $config['contact_email'] . "</a></p>\n";
        $msg .= "</div>\n";
        break;
}

$pid        = $Core->GetVar($_GET, 'pid', 0);
$cid        = $Core->GetVar($_REQUEST, 'cid', '');
$formaction = BASE_PAGE.'?pid='.$pid;
$formaction = $Router->GetLink($pid);

$fp = SB_XML_DIR . 'contacts.xml';
$contacts  = array();
if (file_exists($fp)) {
    $contacts  = $Core->xmlHandler->ParserMain($fp);
}

if (count($contacts) > 1) {
    $options   = array();
    $s = $cid == '' ? 1 : 0 ;
    foreach ($contacts as $con) {
        $s = $con->id == $cid ? 1 : 0 ;
        if (!empty($con->title)) {
            $con->name .= ': ' .$con->title;
        }
        $options[] = $Core->MakeOption($con->name, $con->id, $s);
    }
    $conSelector = $Core->SelectList($options, 'cid');
    
    $contact = $Core->SelectObj($contacts, $cid);
    $config['contact_email'] = isset($contact->email) ? $contact->email : $config['contact_email'];
    $config['contact_name']  = isset($contact->name) ? $contact->name : $config['contact_name'];
    $formaction = $Router->GetLink($pid);
}

if ($button == 'send') {

    $form            = array();
    $form['name']    = $Core->GetVar($_POST, 'name', '');
    $form['email']   = $Core->GetVar($_POST, 'email', '');
    $form['subject'] = $Core->GetVar($_POST, 'subject', '');
    $form['message'] = $Core->GetVar($_POST, 'message', '');
    $form['cc']      = $Core->GetVar($_POST, 'cc', FALSE);
    $form['mailinglist'] = $Core->GetVar($_POST, 'mailinglist', 0); 
    $form['name']     = trim($form['name']);
    $form['email']    = trim($form['email']);
    $form['subject']  = trim($form['subject']);
    $form['message']  = trim($form['message']);
    
    $errors = array();
    foreach ($form as $k=>$v) {
      if ($v == '') array_push($errors, $k);
    }
    if (count($errors)) {
        $msg  = '<h2 class="error">';
        $msg .= 'Your message cannot be sent.<br />Please complete the following fields:';
        $msg .= '</h2>'."\r\n";
        for ($i=0; $i<count($errors); $i++)
        {
            $msg .= $i != count($errors) - 1 ? $errors[$i].', ' : $errors[$i] ;
        }
    } 
    else {
        $mailto  = $config['contact_email'];
        
        $headers  = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
        $headers .= "From: ".$form['name']." <".$form['email'].">\n";
        $headers .= "Reply-To: <".$form['email'].">\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "X-MSMail-Priority: Low\n";
        $headers .= "X-Mailer: WebServer\n";
        
        $txtvers  = date('d M\, Y l h:i:s A')."\n\n";
        $txtvers .= 'To: '.$mailto."\n";
        $txtvers .= 'From: '.$form['name']."\n";
        $txtvers .= 'Email: '.$form['email']."\n\n";
        $txtvers .= 'Subject: '.$form['subject']."\n\n";
        $txtvers .= $form['message']."\n";
        
        $efile    = 'data/email/~'.$form['email'].'.'.time().'.txt';
        if (!$fp = fopen($efile, 'w+')) {
            $msgcode = 2;
        } 
        else {
            $msgcode = 3;
            if (@fwrite($fp, $txtvers)) {
                @fclose($fp);
                $msgcode = 1;
            }
        }
        
        $msgcode = bashMail($form['subject'], $txtvers, $mailto);
        
        if ($form['mailinglist'] == '1') {
            bashMail(
                'Mailing List', 
                 $form['name'] . " (" . $form['email'] . ") has requested to be added to the mailing list\n", 
                 $mailto
           );
        }
        $Core->SBRedirect($formaction.'&msg='.$msgcode);
        exit(0);
    }
}

function bashMail($sbj, $msg, $to, $cc='', $bc='') {
    $disabled = ini_get('disabled_functions');
    $disarr = explode(',', $disabled);
    if (!in_array('exec', $disarr))
    {
        $cmd = 'echo "'.$msg.'" | mail -s "'.$sbj.'" '.$to;
        exec($cmd, $err);
        $res = count($err) == 0 ? 1 : 4 ;
    }
    else
        $res = 4;

    return $res;
}


?>
<!-- CONTACT FORM -->
<div id="contact_form_div">
    <?php echo $msg; ?>
    <?php 
	echo '<h2>'.$config['site_name'].'</h2>'."\r\n";
	if (isset($config['contact_name']) && !empty($config['contact_name'])) {
		echo "<h3>{$config['contact_name']}</h3>\n";
	}
	$check = 
		$config['contact_address'] 
		. $config['contact_city'] 
		. $config['contact_state'] 
		. $config['contact_zip']
		. $config['contact_phone'];
	if (!empty($check)) {
		echo "<address>\n";
		echo empty($config['contact_address']) ? null : "{$config['contact_address']}<br />\n" ;
		echo empty($config['contact_address_2']) ? null : "{$config['contact_address_2']}<br />\n" ;
		echo empty($config['contact_city']) ? null : "{$config['contact_city']}" 
			. (empty($config['contact_state']) ? null : ',&nbsp;');
		echo empty($config['contact_state']) ? null : "{$config['contact_state']}&nbsp;&nbsp;" ;
		echo empty($config['contact_zip']) ? null : "{$config['contact_zip']}\n" ;
		echo empty($config['contact_phone']) ? null : "<br />Phone: {$config['contact_phone']}\n" ;
		echo "</address>\n";
	}
?>
  
  <form action="<?php echo $formaction; ?>" method="post" id="emailForm">
    <fieldset>
    <div class="contact_email_contact">
      <?php if (count($contacts) > 1) : ?>
      <label class="fieldlabel">To:</label>
      <?php echo $conSelector; ?>
      <?php endif; ?>
      <label class="fieldlabel">Name:</label>
      <input type="text" 
             name="name" 
             size="47" 
             class="inputbox" 
             value="" 
             />
      <label class="fieldlabel">Email Address:</label>
      <input type="text" 
             name="email" 
             size="47" 
             class="inputbox" 
             value="" 
             />
      <label class="fieldlabel">Subject:</label>
      <input type="text" 
             name="subject" 
             size="47" 
             class="inputbox" 
             value="" 
             />
      <label class="fieldlabel">Message:</label>
      <textarea cols="44" 
                rows="5" 
                name="message" 
                class="inputbox"></textarea>
      <label class="fieldlabel">
          Would you like to be added to our mailing list?
      </label>
      <input type="radio" name="mailinglist" value="1" checked="checked" />&nbsp;Yes&nbsp;
      <input type="radio" name="mailinglist" value="0" />&nbsp;No&nbsp;
      <input type="hidden" 
             name="cc"  
             value="0"
             />
        <p>
            <input type="submit" 
                   name="submit" 
                   value="Send" 
                   class="button" 
                   />
        </p>
    </div>
    <input type="hidden" name="formtoken" value="<?php echo $token; ?>" />
    </fieldset>
  </form>
</div>
<!-- END CONTACT FORM -->
