<?php

App::uses('AppModel', 'Model');

class HerbarPolozky extends AppModel {

    public $useTable = 'herbar_polozky';
    
    public $belongsTo = array(
        'Herbar' => array(
            'className' => 'Herbar',
            'foreignKey' => 'id_herbar'
        )
    );
    
    public $hasOne = array(
        'Udaj' => array(
            'className' => 'Udaj',
            'foreignKey' => 'id_herb_polozka'
        )
    );
    
}
