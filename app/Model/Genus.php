<?php

App::uses('AppModel', 'Model');

class Genus extends AppModel {

    public $useTable = 'genus';
    
    public $belongsTo = array(
        'Family' => array(
            'className' => 'Family',
            'foreignKey' => 'id_family'
        )
    );
    
    public $hasMany = array(
        'ListOfSpecies' => array(
            'className' => 'ListOfSpecies',
            'foreignKey' => 'id_genus',
            'order' => 'meno,autori'
        )
    );
    
}
