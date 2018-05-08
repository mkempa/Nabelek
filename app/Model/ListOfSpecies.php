<?php

App::uses('AppModel', 'Model');

class ListOfSpecies extends AppModel {

    public $useTable = 'list_of_species';
    
    public $belongsTo = array(
        'Synonyms' => array(
            'className' => 'ListOfSpecies',
            'foreignKey' => 'id_accepted_name'
        ),
        'Genus' => array(
            'className' => 'Genus',
            'foreignKey' => 'id_genus'
        )
    );
    
    public $hasOne = array(
        'Accepted' => array(
            'className' => 'ListOfSpecies',
            'foreignKey' => 'id_accepted_name'
        )
    );
    
    public $hasMany = array(
        'MenaTaxonov' => array(
            'className' => 'MenaTaxonov',
            'foreign_key' => 'id_std_meno',
            'order' => 'id'
        )
    );
    
}