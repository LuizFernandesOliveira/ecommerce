<?php
/**
 * Created by PhpStorm.
 * User: Luiz Fernandes
 * Date: 13/04/2019
 * Time: 20:33
 */

namespace Hcode;

use Rain\Tpl;
use PHPMailer;

class Mailer
{
    const USERNAME = "professorluizfernandesoliveira@gmail.com";
    const PASSWORD = "<?ziul.sednanref?>";
    const NAME_FROM = "Luiz Fernandes";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {
        $config = array(
            "base_url" => null,
            "tpl_dir" => $_SERVER['DOCUMENT_ROOT'] . "/views/email/",
            "cache_dir" => $_SERVER['DOCUMENT_ROOT'] . "/views-cache/",
            "debug" => false
        );

        Tpl::configure($config);

        $tpl = new Tpl();

        foreach ($data as $key => $value){
            $tpl->assign($key, $value);
        }
        $html = $tpl->draw($tplName, true);

        $this->mail = new PHPMailer;


        $this->mail->isSMTP();

        $this->mail->SMTPDebug = 0;

        $this->mail->Debugoutput = 'html';

        $this->mail->Host = 'smtp.gmail.com';


        $this->mail->Port = 587;

        $this->mail->SMTPSecure = 'tls';

        $this->mail->SMTPAuth = true;

        $this->mail->Username = Mailer::USERNAME;


        $this->mail->Password = Mailer::PASSWORD;


        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

//$this->mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);

//Set the subject line
        $this->mail->Subject = $subject;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);

//Replace the plain text body with one created manually
        $this->mail->AltBody = 'This is a plain-text message body';

//Attach an image file
//$this->mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors


    }

    public function send()
    {
        if (!$this->mail->send()) {
            echo "Mailer Error: " . $this->mail->ErrorInfo;
        } else {
            return "Message sent!";
            //Section 2: IMAP
            //Uncomment these to save your message in the 'Sent Mail' folder.
            #if (save_mail($this->mail)) {
            #    echo "Message saved!";
            #}
        }
    }


    function save_mail($mail)
    {
        //You can change 'Sent Mail' to any other folder or tag
        $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";

        //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
        $imapStream = imap_open($path, $mail->Username, $mail->Password);

        $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
        imap_close($imapStream);

        return $result;
    }


}


