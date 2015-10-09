<?php
include_once("semantic/ARC2.php");

class StoreRDF {

    protected $config = array(
      // db 
      'db_name' => 'my_db',
      'db_user' => 'root',
      'db_pwd' => 'root',
      // store 
      'store_name' => 'arc_tests',
      // stop after 100 errors 
      'max_errors' => 100,
    );
    
    protected $store;
    protected $ns;

    public function __construct() {
        $this->store = ARC2::getStore($this->config);
        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }
        //$this->store->reset();

        //$this->store->query('LOAD <person.rdf>');
        //$this->store->query('LOAD <http://xmlns.com/foaf/0.1/>');
        
        $this->ns = array('rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'foaf'=> 'http://xmlns.com/foaf/0.1/',
            's'   => 'http://www.w3.org/2000/01/rdf-schema#',
            'view'=> 'http://purl.org/aquarium/engine/MVC/',
            'tal' => 'http://xml.zope.org/namespaces/tal');
        
    }

    private function getURI($v) {
        $re = '/^([a-z0-9\_\-]+)\:([a-z0-9\_\-\.\%]+)$/i';
        if (!preg_match($re, $v, $m)) return $v;
        if (!isset($this->ns[$m[1]])) return $V;
        return $this->ns[$m[1]] . $m[2];
    }

    private function getValues($name = 'subject', $query = '') {
        $q = '
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
            PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
            PREFIX s: <http://www.w3.org/2000/01/rdf-schema#> .
            PREFIX view: <http://purl.org/aquarium/engine/MVC/> .
            PREFIX tal: <http://xml.zope.org/namespaces/tal> .

            SELECT ?' . $name . ' WHERE {
                ' . $query . ' .
            }
        ';
        //FILTER(lang(?'.$name.')="ru")
        /*MINUS { ' . $query . ' .
                        FILTER (lang(?' . $name . ') != "ru" ) }
                MINUS { ' . $query . ' .
                        ' . $name . ' view:version ?x . }*/
        $r = array();
//        echo $q . "\n";
        if ($rows = $this->store->query($q, 'rows')) {
            //print_r($rows);
            foreach ($rows as $row) {
                if (!($row[$name . ' lang']) || ($row[$name . ' lang'] == "ru"))
                $r[] = $row[$name];
            }
        }
        return $r;
    }

    public function test() {
        $q = '
            insert into <http://www.example.com/People/II/contact#me>
            {<http://www.example.com/People/II/contact#me> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.example.com/People/VERS2> .
             <http://www.example.com/People/VERS2> <http://purl.org/aquarium/engine/MVC/version> "17.09.2015" .
            }
        ';
        //$this->store->query($q, 'rows');
        $q = '    SELECT ?o ?vers WHERE {
                 <http://www.example.com/People/II/contact#me> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?o .
                 OPTIONAL { ?o <http://purl.org/aquarium/engine/MVC/version> ?x }
            }
        ';
        //FILTER (lang(?o)="ru")
        if ($rows = $this->store->query($q, 'rows')) 
            print_r($rows);

    }

    public function subjects($predicate = NULL, $object = NULL) {
        return $this->getValues('subject', '?subject ' . 
                         ($predicate ? $predicate : '?predicate') . ' ' . 
                         ($object ? $object : '?object'));
    }

    public function objects($subject = NULL, $predicate = NULL) {
        return $this->getValues('object', ($subject ? $subject : '?subject') 
                         . ' ' .
                         ($predicate ? $predicate : '?predicate') . ' ' .
                         '?object');
    }

    public function object($subject = NULL, $predicate = NULL) {
        return $this->objects("'" . $subject . "'", $predicate)[0];
    }

    public function predicates($subject = NULL, $object = NULL) {
        return $this->getValues('predicate', 
                         ($subject ? $subject : '?subject') 
                         . ' '  .  '?predicate ' .
                         ($object ? $object : '?object'));
    }

    public function getTempl($subject) {
        return $this->objects($subject, 'view:pt')[0];
    }

    public function setObject($subject, $predicate, $object) {
        if (!$subject or !$predicate or !$object)
            return "Пусто";
        $s = '<' . $this->getURI($subject) . '>';
        $p = '<' . $this->getURI($predicate) . '>';
        $o = '<' . $this->getURI($object) . '>';
        $pref =' 
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
            PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
            PREFIX s: <http://www.w3.org/2000/01/rdf-schema#> .
            PREFIX view: <http://purl.org/aquarium/engine/MVC/> .
            PREFIX tal: <http://xml.zope.org/namespaces/tal> .
        ';
        $qDel = $pref . '
            DELETE 
             { ' . $s . ' ' . $p . ' ?o }
            Where { ' . $s . ' ' . $p . ' ?o }
        ';
        $qIns = $pref . '

            INSERT INTO ' . $s  .'
             { ' . $s . ' ' . $p . ' ' .  $o . ' }
        ';
        //$this->store->query($qDel, 'rows');
        $this->store->query($qIns, 'rows');
/*        if ($rows = $this->store->query('select ?s ?p ?o where { ?s ?p ?o }', 'rows')){
            print_r($rows);
        }
*/
    }
    
    
    
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
