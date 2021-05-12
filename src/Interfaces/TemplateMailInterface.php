<?php

namespace App\Interfaces;

interface TemplateMailInterface {

    public function set($template, string $name, array $type);

    public function remove(string $name);

    public function get(string $name, array $content);

    public function send(string $mail, string $recipient, string $titre, $files=null);

}?>