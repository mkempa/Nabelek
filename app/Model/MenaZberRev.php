<?php

App::uses('AppModel', 'Model');

class MenaZberRev extends AppModel {

    public $useTable = 'mena_zber_rev';
    
    public $hasMany = array(
        'SkupRevDet' => array(
            'className' => 'SkupRevDet',
            'foreignKey' => 'id_meno_rev',
            'order' => 'poradie',
        )
    );
    
}
