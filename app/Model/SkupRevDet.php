<?php

App::uses('AppModel', 'Model');

class SkupRevDet extends AppModel {

    public $useTable = 'skup_rev_det';
    
    public $belongsTo = array(
        'SkupRev' => array(
            'className' => 'SkupRev',
            'foreignKey' => 'id_skup_rev'
        ),
        'MenaZberRev' => array(
            'className' => 'MenaZberRev',
            'foreignKey' => 'id_meno_rev'
        )
    );
    
}