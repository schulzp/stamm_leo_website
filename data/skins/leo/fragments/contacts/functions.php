<?php

function check_address($config) {
    $ok = false;
    foreach (split(',','address,city,state,zip,phone') as $key) {
        if (!empty($config["contact_$key"])) return true;    
    }
    return false;
}

function the_contact($contacts, $config) {
    global $Core;
    global $Filter;
    $contact = $Core->SelectObj($contacts, $Filter->get($_POST, 'cid', null));
    return isset($contact->email) ? $contact->email : $config['contact_email'];
}

function the_form_action() {
    global $Router;
    global $Filter;
    return $Router->GetLink($Filter->get($_GET, 'pid', ''));
}

function the_message() {
    global $Filter;
    $message = $Filter->get($_SESSION, 'contact_form_message', array());
    if (empty($message)) return null;
    unset($_SESSION['contact_form_message']);
    return get_message_string($message);
}

function get_message_string($message) {
    global $Filter;
    return 
    "<div class=\"" . $Filter->get($message, 'class', 'none') . "\">\n" . 
    "<h2>" . $Filter->get($message, 'title', null) . "</h2>\n" . 
    "<p>" . $Filter->get($message, 'string', null) . "</p>\n" .
    "</div>\n";
}

function the_action() {
    global $Core;
    global $Filter;
    return strToLower($Filter->get($_POST, 'action', ''));
}

function set_message($class, $title, $string) {
     $_SESSION['contact_form_message'] = array(
         'class'  => $class,
         'title'  => $title,
         'string' => $string
     );
}

function handle_contact_form($mailto) {
    global $Core;
    global $Filter;
    
    $form = array();
    $form['name']        = $Filter->get($_POST, 'name', '');
    $form['email']       = $Filter->get($_POST, 'email', '');
    $form['subject']     = $Filter->get($_POST, 'subject', '');
    $form['message']     = $Filter->get($_POST, 'message', '');
    $form['cc']          = $Filter->get($_POST, 'cc', FALSE);
    $form['mailinglist'] = $Filter->get($_POST, 'mailinglist', 0); 
        
    $errors = array();
    foreach ($form as $k=>$v) {
      if ($v == '') array_push($errors, $k);
    }
    if (count($errors)) {
        set_message(
            'error',
            'Ihre Nachricht kann nicht versandt werden.<br />Bitte fuellen Sie folgende Felder aus:',
            implode(', ', $errors)
        );
    } 
    else {
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
        
        FileSystem::write_file(
            'data/email/~'.$form['email'].'.'.time().'.txt', 
            $txtvers
        );

        if (bashMail($form['subject'], $txtvers, $mailto)) {
            set_message(
				'Erfolg',
				'Ihre Nachricht wurde versandt.',
				'Wir melden uns in Kuerze.'
			);
        }
        else {
            set_message(
				'Fehler',
				'Ihre Nachricht konnte nicht versandt werden.',
				'Ein Fehler ist aufgetreten.'
			);
        }
        
        if ($form['mailinglist'] == '1') {
            bashMail(
                'Mailing List', 
                 $form['name'] . " (" . $form['email'] . ") Moechte den Newsletter erhalten\n", 
                 $mailto
           );
        }
    }
}

function bashMail($sbj, $msg, $to, $cc='', $bc='') {
    $cmd = 'echo "'.$msg.'" | mail -s "'.$sbj.'" '.$to;
    exec($cmd, $err);
    $res = count($err) == 0 ? 1 : 4 ;
    return $res;
}

?>
