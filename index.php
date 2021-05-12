<?php

require 'vendor/autoload.php';

use App\Utils\TemplateMail;
$objTemplate = new TemplateMail;

// demo
$content = ['name'=>'Smith', 'vorname'=>'John', 'age'=>'24 ans',
'destination'=>'california','flight'=>'212','title'=>['Smith', '212', 'San Francisco']];
// demo

// set
if(!empty($_POST)){
    $option = [$_POST['option'], $_POST['option2']];
    if($_POST['option'] == 'code'){
        $objTemplate->set($_POST["text"], $_POST['name'], $option);
    }elseif(!empty($_FILES)){
        echo $objTemplate->set($_FILES, $_POST['name'], $option);
    }
}

// get
$get = "";
if(!empty($_POST['get'])){
    $get = $objTemplate->get($_POST['get'], $content);
}

// delete
if(!empty($_POST['suppression'])){
    $objTemplate->remove($_POST['suppression']);
}

// demo
$html = '<table name="Welcome M. (.*) aboard flight (.*) out of (.*)">
    <tbody>
        <tr>
            <td name="name"></td>
            <td name="vorname"></td>
            <td name="destination"></td>
            <td name="flight"></td>
        </tr>
    </tbody>
</table>';

$read = scandir('src/Templates');
if(!empty($read)){
    foreach ($read as $line):
        $list .= $line.'<br/>';
    endforeach;
}
// demo 
echo $get; ?>
<form enctype="multipart/form-data" action="" method="POST">
    Paramètres
    <input type="radio" name="option" value="code" checked="checked" /> Code
    <input type="radio" name="option" value="html" /> Import
    <br/>
    Type :
    <input type="text" style="width:50px;" name="option2" value="s" />
    <br/>
    Nom de l'e-mail :
    <input type="text" style="width:100%;" name="name" value="newsletter2" />
    <br/>
    <textarea name="text" style="height:300px;width:100%;" placeholder="Enter your code"><?= $html; ?></textarea>
    <br/>
    ou
    <br/>
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Transfère le fichier <input type="file" name="template_file" />
    <input type="submit" />
</form>

<b>
    Get a template
</b>
<br/><br/>

<form action="" method="POST">
    Nom de l'e-mail :
    <input type="text" style="width:100%;" name="get" value="newsletter2" />
    <input type="submit" />
</form>

<b>
    Delete a template
</b>
<br/><br/>

<form action="" method="POST">
    Nom de l'e-mail :
    <input type="text" style="width:100%;" name="suppression" value="newsletter2" />
    <input type="submit" />
</form>
<?= $list; ?>