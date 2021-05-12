![Capture d’écran 2021-05-10 à 21 16 19](https://user-images.githubusercontent.com/76005455/117712736-fe25b800-b1d4-11eb-9d0e-7f10d8771bbd.png)

# PhpEmail-manager
Manage newsletter and email template in your project.
Send your code or a html template file and then when you need it send an array to fill your template.

PhpMailer and send mail manual function is include.



#1 Unless pdo connection, add it if you need it

Manual :: Configuration


Define your own property in the construct's function
<pre>
public function __construct(){
      $this->DOMDocument = new DOMDocument;
      $this->Template = dirname(__DIR__).'/Templates/'; // define templates folder's
      $this->seeder = 'no-reply@mail.fr'; // define seeder
      $this->sendType = 1; // define send manual function OR PHPMAILER
      $this->paramPhpMailer = array('Host'=>'', 'Port'=>'465', 'Username'=>$this->seeder, 'Password'=>'', 'setFrom'=>'');
      $this->extensions = array("html"); // extension allowed
}

</pre>

![Capture d’écran 2021-05-10 à 21 52 34](https://user-images.githubusercontent.com/76005455/117716582-10eebb80-b1da-11eb-877a-82600d64d60d.png)

<b>INSERT A TEMPLATE</b>

<pre>
      public function set($template, string $name, array $option=['code', 's']):? string
</pre>

$template string || array
string for code (ex: Wysiwyg)

array :

     [required] <input type="file" name="template_file" />
     <input type="text" name="option[]" value="code" />
     <input type="text" name="option[]" value="s" />


option [param1, param2] 
- param1 = 'code' or 'file'
- param2 = 's' or 'f' means 's: standard, f: erase and rewrite'


      // String
      // $html = '<table><tr><td></td> </tr></table';
      (new TemplateMail)->set($html, 'name-to-call');
      
      // Upload
      (new TemplateMail)->set($_FILES, 'name-to-call', $_POST['option']);



<b>GET A TEMPLATE</b>

<pre>
      public function get(string $name, array $content=null):? string
</pre>


[Required To send] email string || array
- Without email return the template

- To fill in the template require param $content array(name=>value)
<pre>
      $content = array('age'=>12, name=>"Smith", email=>"smith@gmail.com"); 
      (new TemplateMail)->get('newsletter-travel', $content);
</pre>

Template code ex : <td name="name"></td><td name="age"></td>

<table>
      <tbody>
            <td>Smith</td>
            <td>12</td>
      </tbody>
</table>
 
[Required To send] To add a Title's e-mail add an attribut [name] on table, @title string || array

To add Title :
 - name="Welcome aboard"
 
Result -> Welcome aboard

To add dynamic value ex : 
 - name="Welcome M. (.\*) aboard flight (.\*) out of (.\*)"

<pre>
      $content[title] = ['Smith', '212','San Francisco'];
</pre>
Result -> Welcome M. Smith aboard flight 212 out of San Francisco

<b>REMOVE A TEMPLATE</b>

<pre>
      public function remove(string $name):?int
</pre>

<pre>
      (new TemplateMail)->remove('newsletter2');
</pre>

<b>Composer config</b>

![Capture d’écran 2021-05-10 à 21 23 42](https://user-images.githubusercontent.com/76005455/117713540-1518da00-b1d6-11eb-8b01-16653232cc03.png)

