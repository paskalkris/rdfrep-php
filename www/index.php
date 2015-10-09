<?php
//require_once 'editor/edt.html';
//exit;

require_once 'www/StoreRDF.php';
require_once 'PHPTAL.php';

$store = new StoreRDF();
//$store->test();
//$store->setObject('http://www.example.com/People/II/contact#me', 'foaf:homepage', 'http://www.example.com/People/II');
//exit;
$subj = $store->subjects('a', 'foaf:Person')[0];
//$q = getSubjects('foaf:Person', $store);
//$subj = $q[0];

  
  /*foreach ($q as $row) {
    //$r .= '<li>' . $row['obj'] . ' - ' . $row['rel'] . ' - ' . $row['subj'] . '</li>';
    if ($rows = $row->getTempl()){
      foreach ($rows as $name) {
        $r .= '<li>' . $name . '</li>';
      }
    }
    if ($rows = $row->getObj('?rel')){
      foreach ($rows as $name) {
        $r .= '<li>' . $name . '</li>';
      }
    }
  }*/



// создать обработчик шаблонов
$template = new PHPTAL();
$template->setSource($store->getTempl('foaf:Person'));
$editortmpl = new PHPTAL('./editor/index.html');

//$template = new PHPTAL('/home/paskal/public/mytests/www/templ_.html');

// класс Person
class Person {
    public $name;
    public $phone;

    function Person($name, $phone) {
        $this->name = $name;
        $this->phone = $phone;
    }
    
    function getName($val) {
        return $val;
    }
}

// Создаем массив объектов для тестирования
$people = array();
$people[] = new Person("foo", "01-344-121-021");
$people[] = new Person("bar", "05-999-165-541");
$people[] = new Person("baz", "01-389-321-024");
$people[] = new Person("quz", "05-321-378-654");

// Передаем массив данных обработчику шаблонов
$template->title = 'Я Заголовок';
$template->subj = $subj;
$template->people = $people;
$template->store = $store;

// Выполняем обработку шаблона
try {
    $editortmpl->tmpl = $template->execute();
//    echo $editortmpl->execute();
    echo $template->execute();
}
catch (Exception $e){
    echo $e;
}


?>
