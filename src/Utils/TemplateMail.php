<?php

namespace App\Utils;

use App\Interfaces\TemplateMailInterface;
use PHPMailer\PHPMailer\PHPMailer;
use \DOMDocument;
use \Exception;

class TemplateMail implements TemplateMailInterface
{
    private $DOMDocument;
    protected $paramPhpMailer;
    protected $extensions;

    /**
     * Define your own property in the construct's function
     */
    public function __construct(){
        $this->DOMDocument = new DOMDocument;
        $this->Template = dirname(__DIR__).'/Templates/'; // define templates folder's
        $this->seeder = 'no-reply@mail.fr'; // define seeder
        $this->sendType = 1; // define send manual function OR PHPMAILER
        $this->paramPhpMailer = array('Host'=>'', 'Port'=>'465', 'Username'=>$this->seeder, 'Password'=>'', 'setFrom'=>'');
        $this->extensions = array("html"); // extension allowed
    }

    /** insert a new template
     * $template string || array
     * string for code (ex: Wysiwyg)
     * array (POST file);
     *     [required] input name="template_file"
     *     <input type="text" name="option[]" value="code" />
     *     <input type="text" name="option[]" value="s" />
     *     <input type="file" name="template_file" />
     * 
     * $option [param1, param2] 
     *     param1 = 'code' or 'file'
     *     param2 = 's: standard, f: erase and rewrite'
     */
    public function set($template, string $name, array $option=['code', 's']):? string
    {
        try{
            $file = $this->Template.'template_'.$name.'.html';
            if($option[0] == 'code'){
                // if @param $template is write with WYSIWYG
                if(file_exists($file) && $option[1] != "f"){
                    return 'File exist to overwrite use --option "f"';
                }elseif(!file_exists($file) OR file_exists($file) && $option[1] == "f"){
                    $f = fopen($file, "wa+");
                    fputs($f, stripslashes($template));
                    fclose($f);
                }
            }else{
                // if @param $template is a file :: upload
                if(file_exists($file) && $option[1] != "f"){
                    return 'File exist to overwrite use --option "f"';
                }elseif(!file_exists($file) OR (file_exists($file) && $option[1] == "f")){
                    if(file_exists($file)){
                        unlink($file);
                    }
                    $PATH = $this->Template.'/'.$template['file']['name'];
                    if(!empty($template['template_file']['name']) ){
                        $extension = pathinfo($template['template_file']['name'], PATHINFO_EXTENSION);
                        if(in_array($extension, $this->extensions)){
                            // if any error
                            if(isset($template['template_file']['error']) && UPLOAD_ERR_OK === $template['template_file']['error']){
                                // upload file
                                if(move_uploaded_file($template['template_file']['tmp_name'], $this->Template.'template_'.$name.'.html')){
                                    return 'Success';
                                }else{
                                    // Can't upload
                                    throw new Exception("FAILED Can't upload");
                                }
                            }else{
                                throw new Exception("FAILED Error file");
                            }
                        }else{
                            // Extension error
                            throw new Exception("FAILED Unauthorized extension");
                        }
                    }else{
                        throw new Exception("FAILED file is empty");
                    }
                }
            }
            
        }catch(\Exception $e){
            echo "<b>Template Mail Error</b> : ",  $e->getMessage(), "\n";
        }

        return '';
    }
    
    public function remove(string $name):?int
    {
        try{
            $file = $this->Template.'template_'.$name.'.html';
            if(file_exists($file)){
                unlink($file);
                return true;
            }elseif(file_exists($this->Template.$name)){
                unlink($this->Template.$name);
                return true;
            }else{
                throw new Exception("FAILED delete, Template ".$name.".html doesn't exist in ".$this->Template.$name.".html");
            }
        }catch (\Exception $e) {
            echo "<b>Template Mail Error</b> : ",  $e->getMessage(), "\n";
        }

        return null;
    }

    /**
     * [Required To send] To send an email require email string||array
     * Without email return the template
     * 
     * To fill in the template require param $content = array(name=>value)
     * For $content = array('age'=>12, name=>"Smith")
     * (new TemplateMail)->get('newsletter-travel', $content);
     * Template ex : <td name="name"></td><td name="age"></td>
     * Result :: <td>Smith</td><td>12</td>
     * 
     * [Required To send] To add Title add an attribut [name] on table, @title string||array
     * To add dynamic value ex : name="Welcome M. (.*) aboard flight (.*) out of (.*)"
     * and send an array value $content[title] = ['Smith', '212','San Francisco']
     * Result :: Welcome M. Smith aboard flight 212 out of San Francisco
     */
    public function get(string $name, array $content=null):? string
    {
        try{
            $pj = ""; // attached file define to empty
            $file = $this->Template.'template_'.$name.'.html';
            if(!file_exists($file)){
                $file = $this->Template.$name;
            }
            if(file_exists($file)){
                $this->DOMDocument->loadHTMLFile($file);
                $reader = ['table','tr','td']; // Have a look on
                if(!empty($reader)){
                    foreach ($reader as $balise):
                        $tags = $this->DOMDocument->getElementsByTagName($balise);
                        if(!empty($tags)){
                            foreach ($tags as $tag):
                                $attribut = $tag->getAttribute('name');
                                if(!empty($attribut)){
                                    if($balise == 'table'){
                                        if(!empty($content['title']) && is_array($content['title'])){
                                            $getValue = explode('(.*)', $attribut);
                                            if(!empty($getValue)){
                                                $i = 0;
                                                $title = "";
                                                foreach($getValue as $value):
                                                    $title .= $value.$content['title'][$i];
                                                    $i++;
                                                endforeach;
                                            }
                                        }elseif(!empty($content['title'])){
                                            $title = $content['title'];
                                        }elseif(!empty($content['email']) && empty($content['title'])){
                                            throw new Exception('Require attribut name=[title] on table');
                                        }
                                        if(!empty($content[$attribut])){
                                            $addValue = $content[$attribut];
                                        }
                                    }elseif(!empty($content[$attribut])){
                                        $addValue = $content[$attribut];
                                    }
                                    $element = $this->DOMDocument->createTextNode($addValue);
                                    $tag->appendChild($element);
                                    $tag->removeAttribute('name');
                                }
                            endforeach;
                        }
                    endforeach;
                }
                $mail = $this->DOMDocument->saveHTML();
                if(!empty($content['email']) && !empty($title)){
                    if(!empty($content['file'])){
                        $pj = $content['file'];
                    }
                    $this->send($mail, $title, $content['email'], $pj);
                    return 'success';
                }
            }else{
                throw new Exception("Template ".$name.".html doesn't exist in ".$this->Template.$name.".html");
            }
        }catch (\Exception $e) {
            echo "<b>Template Mail Error</b> : ",  $e->getMessage(), "\n";
        }

        return $mail;
    }

    /**
     * Default use send manual function INT 0||1
     * #1 Will use PHPMAILER
     * 
     */
    public function send(string $mail, string $recipient, string $title, $path=null):int
    {
        if($this->sendType == 0){
            $saut_ligne = "\r\n";
            $message = "";
            $retour_chariot = "\n";
            $separator = md5(time());

            // define header
            $headers = 'From: '.$this->seeder.$saut_ligne;
            $headers .= 'MIME-Version: 1.0'.$retour_chariot;
            $headers .= 'X-Mailer: PHP 7.0'.$retour_chariot;
            $headers .= 'X-Priority: 1' .$retour_chariot;
            $headers .= 'Mime-Version: 1.0'.$retour_chariot;

            if(!empty($path) && !empty($filename)){
                $file = $path;
                $content = file_get_contents($file);
                $content = chunk_split(base64_encode($content));
                $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"".$saut_ligne;
                $headers .= "Content-Transfer-Encoding: 7bit".$saut_ligne;
                $headers .= 'Date:'.date('D, d M Y H:i:s').$retour_chariot;
                $message = "--".$separator.$saut_ligne;
                $message .= "Content-Type: text/html; charset=\"UTF-8\"".$saut_ligne;
                $message .= "Content-Transfer-Encoding: 8bit".$saut_ligne;
            }else{
                $headers .= 'Content-Transfer-Encoding: 8bit'.$retour_chariot;
                $headers .= 'Content-type: text/html; charset=UTF-8'.$retour_chariot;
                $headers .= 'Date:'.date('D, d M Y H:i:s').$retour_chariot;
            }

            $message .= $mail.$saut_ligne;
            // define attachements
            if(!empty($path) && !empty($filename)){
                $message .="--".$separator.$saut_ligne;
                $message .="Content-Type: application/octet-stream; name=\"".$filename."\"".$saut_ligne;
                $message .="Content-Transfer-Encoding: base64".$saut_ligne;
                $message .="Content-Disposition: attachment".$saut_ligne;
                $message .= $content.$saut_ligne;
                $message .= "--".$separator."--";
            }

            if(is_array($recipient)){
                foreach($recipient as $email){
                        mail($email, $title, $message, $headers);
                }
            }else{
                mail($recipient, $title, $message, $headers);
            }
        }else{
            $paramPhpMailer = $this->phpMailer;
            //Create a new PHPMailer instance
            $content = new PHPMailer;
            //Tell PHPMailer to use SMTP
            //$content->isQMAIL();
            $content->CharSet = 'UTF-8';
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            $content->SMTPDebug = 2;
            //Ask for HTML-friendly debug output
            $content->Debugoutput = 'html';
            $content->SMTPSecure = 'ssl';
            //Set the hostname of the mail server
            $content->Host = $paramPhpMailer['Host'];
            //Set the SMTP port number - likely to be 25, 465 or 587
            $content->Port = $paramPhpMailer['Port'];
            //Whether to use SMTP authentication
            $content->SMTPAuth = true;
            //Username to use for SMTP authentication
            //echo EMAIL_USER.' ';
            $content->Username = $paramPhpMailer['Username'];
            //Password to use for SMTP authentication
            //echo EMAIL_PASSWORD.' ';
            $content->Password = $paramPhpMailer['Password'];
            //Set who the message is to be sent from
            //echo EMAIL_FROM.' ';
            $content->setFrom($paramPhpMailer['Username'], $paramPhpMailer['setFrom']);
            //Set who the message is to be sent to
            $content->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            if(is_array($recipient)){
                foreach($recipient as $address){
                    $content->addAddress($address);
                }
            }else{
                $content->addAddress($recipient);
            }

            //Set the subject line
            $content->Subject = $title;
            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $content->msgHTML($mail);
            //Replace the plain text body with one created manually
            //$content->AltBody = 'This is a plain-text message body';

            if (!$content->send()) {
                $result = FALSE;
                echo "Mailer Error: " . $content->ErrorInfo;
                return $result;
            } else {
                $erreur = 'ok';
                return $erreur;
            }
        }

        return TRUE;
    }
}
?>