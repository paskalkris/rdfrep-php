<?php
    include_once("./www/StoreRDF.php");
    
    if ($_POST['one'] and $_POST['two']){
        echo $_POST['one']+$_POST['two'];
    }
    
    if ($_POST['subject'] and $_POST['predicate'] and $_POST['object']){
        $store = new StoreRDF();
        $store->setObject($_POST['subject'], $_POST['predicate'], $_POST['object']);
        echo 1;
    }
?>