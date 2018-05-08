<?php

App::uses('AppController', 'Controller');

App::uses('AppModel', 'Model');

class Herbar extends AppModel {

    public $useTable = 'herbar';

    public $hasMany = array(
        'HerbarPolozky' => array(
            'className' => 'HerbarPolozky',
            'foreignKey' => 'id_herbar',
        )
    );
    
}

