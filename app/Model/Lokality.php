<?php

App::uses('AppModel', 'Model');

class Lokality extends AppModel {

    public $useTable = 'lokality';
    
    public $belongsTo = array(
        'Brumit4' => array(
            'className' => 'Brumit4',
            'foreignKey' => 'id_brumit4'
        )
    );
    
    public $hasOne = array(
        'Udaj' => array(
            'className' => 'Udaj',
            'foreignKey' => 'id_lok'
        )
    );
    
    /*
    public function locations() {
        $this->unbindModel('belongsTo' => array('Brumit4'));
        $this->find('all', array(
            'Lokality.'
        ));
    }
    */
}
