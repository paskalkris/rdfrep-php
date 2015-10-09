<?php
include_once("semantic/ARC2.php");

$config = array(
  // db 
  'db_name' => 'my_db',
  'db_user' => 'root',
  'db_pwd' => 'root',
  // store 
  'store_name' => 'arc_tests',
  // stop after 100 errors 
  'max_errors' => 100,
);
$store = ARC2::getStore($config);
if (!$store->isSetUp()) {
  $store->setUp();
}
$store->reset();

/* LOAD will call the Web reader, which will call the
format detector, which in turn triggers the inclusion of an
appropriate parser, etc. until the triples end up in the store. */
$store->query('LOAD <person.rdf>'); 

class Something {
  var $id;
  var $obj;
  var $store;

  function Something($store, $id, $obj) {
    $this->store = $store;
    $this->id = $id;
    $this->obj = $obj;
  }

  function getObj($rel, $pref = NULL) {
    if ($pref)
      $pref = 'PREFIX '.$pref;
    $q = $pref . '
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX foaf: <http://xmlns.com/foaf/0.1/>
    PREFIX s: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX view: <http://purl.org/aquarium/engine/MVC/>
    PREFIX tal: <http://xml.zope.org/namespaces/tal>

      SELECT ?obj WHERE {
        ' . $this->id . ' '. $rel .' ?obj .
      }
    ';
    $r = array();
    if ($rows = $this->store->query($q, 'rows')) {
      foreach ($rows as $row) {
        $r[] = $row['obj'];
      }
    }
    return $r;
  }

  function getTempl() {
    $q = '
      PREFIX view: <http://purl.org/aquarium/engine/MVC/> .
      PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
      SELECT ?obj WHERE {
        ' . $this->obj . ' view:pt ?obj .
      }
    ';
    $r = array();
    if ($rows = $this->store->query($q, 'rows')) {
      foreach ($rows as $row) {
        $r[] = $row['obj'];
      }
    }
    return $r;
  }

}

function getObj($subj, $rel, $pref = NULL) {
    if ($pref)
      $pref = 'PREFIX '.$pref;
    $q = $pref . '
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX foaf: <http://xmlns.com/foaf/0.1/>
    PREFIX s: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX view: <http://purl.org/aquarium/engine/MVC/>
    PREFIX tal: <http://xml.zope.org/namespaces/tal>

      SELECT ?obj WHERE {
        ' . $this->id . ' '. $rel .' ?obj .
      }
    ';
    $r = array();
    if ($rows = $this->store->query($q, 'rows')) {
      foreach ($rows as $row) {
        $r[] = $row['obj'];
      }
    }
    return $r;
}

function getTempl() {
    $q = '
      PREFIX view: <http://purl.org/aquarium/engine/MVC/> .
      PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
      SELECT ?obj WHERE {
        ' . $this->obj . ' view:pt ?obj .
      }
    ';
    $r = array();
    if ($rows = $this->store->query($q, 'rows')) {
      foreach ($rows as $row) {
        $r[] = $row['obj'];
      }
    }
    return $r;
}

function getSubjects($obj, $st) {
  $q = '
    PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
    SELECT ?obj WHERE {
      ?obj a ' . $obj . ' .
    }
  ';
  $r = array();
  $i = 0;
  if ($rows = $st->query($q, 'rows')) {
    foreach ($rows as $row) {
      $r[] = $row['obj'];
    }
  }
  return $r;
}
/* 
http://www.w3.org/People/Berners-Lee/card.rdf>');
*/
/* list names */
/*$q = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
  SELECT ?obj ?rel ?subj WHERE {
    ?obj ?rel ?subj .
    filter (!(lang(?subj) = "ru"))
  }
';
//$q = getObjects('foaf:Person', $store);
$r = '';
if ($rows = $store->query($q, 'rows')) {
  print_r($rows);
  foreach ($rows as $row) {
    $r .= '<li>' . $row['obj'] . ' - ' . $row['rel'] . ' - ' . $row['subj'] . ' - ' . $row['l'] . '</li>';
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
  }
}

echo $r ? '<ul>' . $r . '</ul>' : 'no named persons found';
*/
?>
