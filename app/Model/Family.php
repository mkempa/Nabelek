<?php

App::uses('AppModel', 'Model');

class Family extends AppModel {

    public $useTable = 'family';
    
    public $hasMany = array(
        'Genus' => array(
            'className' => 'Genus',
            'foreignKey' => 'id_family'
        )
    );
    
}
