<?php

App::uses('AppModel', 'Model');

class SkupRev extends AppModel {

    public $useTable = 'skup_rev';

    public $belongsTo = array(
        'Udaj' => array(
            'className' => 'Udaj',
            'foreignKey' => 'id_udaj'
        ),
        'MenaTaxonov' => array(
            'className' => 'MenaTaxonov',
            'foreignKey' => 'id_meno_rast_prirad'
        )
    );
    /*
    public $hasMany = array(
        'SkupRevDet' => array(
            'className' => 'SkupRevDet',
            'foreignKey' => 'id_skup_rev',
            'order' => 'poradie',
        )
    );*/
    
    public $hasAndBelongsToMany = array(
        'DeterminedBy' => array(
            'className' => 'MenaZberRev',
            'joinTable' => 'skup_rev_det',
            'foreignKey' => 'id_skup_rev',
            'associationForeignKey' => 'id_meno_rev',
            'fields' => array('meno', 'std_meno'),
            'order' => 'SkupRevDet.poradie'
        )
    );
    
}
