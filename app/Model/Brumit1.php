<?php

App::uses('AppModel', 'Model');

class Brumit1 extends AppModel {

    public $useTable = 'brumit1';
    
    public $hasMany = array(
        'Brumit2' => array(
            'className' => 'Brumit2',
            'foreignKey' => 'id_parent'
        )
    );
}
