<?php

defined('SKYBLUE') or die('Unauthorized file request');

if (empty($data)) return;

global $Filter;
global $Router;
global $Core;
global $config;

if (the_action() == "senden") {
    handle_contact_form(the_contact($data, $config));
}


?>
<!-- CONTACT FORM -->
<div id="contact_form_div">
    <?php echo the_message(); ?>
    <?php 
    echo '<h2>'.$config['site_name'].'</h2>'."\r\n";
    if ($contact_name = $Filter->get($config, 'contact_name', null)) {
        echo "<h3>{$contact_name}</h3>\n";
    }
    if (check_address($config)) {
        echo "<address>\n";
        echo empty($config['contact_address'])   ? null : "{$config['contact_address']}<br />\n" ;
        echo empty($config['contact_address_2']) ? null : "{$config['contact_address_2']}<br />\n" ;
        
        echo empty($config['contact_city'])      ? null : "{$config['contact_city']}" 
            . (empty($config['contact_state'])   ? null : ',&nbsp;');
        echo empty($config['contact_state'])     ? null : "{$config['contact_state']}&nbsp;&nbsp;" ;
        echo empty($config['contact_zip'])       ? null : "{$config['contact_zip']}\n" ;
        echo empty($config['contact_phone'])     ? null : "<br />Phone: {$config['contact_phone']}\n" ;
        echo "</address>\n";
    }
?>
<p>
    Alternativ können Sie das folgende Formular verwenden, um uns eine E-Mail zu senden.
</p>
    <form action="<?php echo the_form_action(); ?>" method="post" id="emailForm" class="yform">
        <fieldset>
            <legend>Ihre Angaben</legend>
            <div class="contact_email_contact">
                <?php if (count($data) > 1) : ?>
                <div class="type-select">
                    <label for="cid">An:</label>
                    <select name="cid" class="type-select">
                        <?php foreach ($data as $contact) : ?>
                        <option value="<?php echo $contact->id; ?>"><?php echo $contact->name; ?></option>
                        <?php endforeach; ?>
                    </select>

                </div>
                <?php endif; ?>
                <div class="type-text">
                    <label for="name">Name:</label>
                    <input type="text" name="name" value="" />
                </div>
                <div class="type-text">
                    <label for="email">E-Mail Adresse:</label>
                    <input type="text" name="email" size="47" value="" />
                </div>
                <div class="type-text">
                    <label for="subject">Betreff:</label>
                    <input type="text" name="subject" size="47" value="" />
                </div>
                <div class="type-text">
                    <label for="message">Nachricht:</label>
                    <textarea cols="44" rows="5" name="message"></textarea>
                </div>
                <div class="type-ceck" id="contact_mailing_list">
                    <label for="">
                        Wollen Sie useren Newsletter erhalten?
                    </label>
                    <input type="radio" name="mailinglist" value="1" />&nbsp;Ja&nbsp;
                    <input type="radio" name="mailinglist" value="0" checked="checked" />&nbsp;Nein&nbsp;
                </div>
                <input type="hidden" name="cc" value="0" />
            </div>
        </fieldset>
        <div class="type-buttons">
            <input type="reset" name="reset" id="reset" value="Zurücksetzen"/>
            <input type="submit" name="action" id="submit" value="Senden" />
        </div>
    </form>
</div>
<!-- END CONTACT FORM -->
