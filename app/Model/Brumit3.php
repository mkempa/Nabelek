<?php

App::uses('AppModel', 'Model');

class Brumit3 extends AppModel {

    public $useTable = 'brumit3';
    
    public $belongsTo = array(
        'Brumit2' => array(
            'className' => 'Brumit2',
            'foreignKey' => 'id_parent'
        )
    );
    
    public $hasMany = array(
        'Brumit4' => array(
            'className' => 'Brumit4',
            'foreignKey' => 'id_parentid'
        )
    );
    
}
