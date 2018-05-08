<?php

App::uses('AppModel', 'Model');

class Brumit2 extends AppModel {

    public $useTable = 'brumit2';
    
    public $belongsTo = array(
        'Brumit1' => array(
            'className' => 'Brumit1',
            'foreignKey' => 'id_parent'
        )
    );
    
    public $hasMany = array(
        'Brumit3' => array(
            'className' => 'Brumit3',
            'foreignKey' => 'id_parent'
        )
    );
}

