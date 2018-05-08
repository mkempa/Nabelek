<?php

App::uses('AppModel', 'Model');

class MenaTaxonov extends AppModel {

    public $useTable = 'mena_taxonov';
    
    public $belongsTo = array(
        'ListOfSpecies' => array(
            'className' => 'ListOfSpecies',
            'foreignKey' => 'id_std_meno'
        )
    );
    
    public $hasOne = array(
        'SkupRev' => array(
            'className' => 'SkupRev',
            'foreignKey' => 'id_meno_rast_prirad'
        )
    );
    
}
