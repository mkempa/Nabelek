<?php

App::uses('AppModel', 'Model');

class Udaj extends AppModel {

    public $useTable = 'udaj';
    
    public $belongsTo = array(
        'HerbarPolozky' => array(
            'className' => 'HerbarPolozky',
            'foreignKey' => 'id_herb_polozka',
            'conditions' => array('cislo_ck_full LIKE' => 'SAV%', 'project' => 'nabelek')
        ),
        'Lokality' => array(
            'className' => 'Lokality',
            'foreignKey' => 'id_lok'
        )
    );
    
    public $hasOne = array(
        'SkupRevOrig' => array(
            'className' => 'SkupRev',
            'foreignKey' => 'id_udaj',
            'conditions' => array('f_revizia' => false)
        )
    );

    public $hasMany = array(
        'SkupRevRev' => array(
            'className' => 'SkupRev',
            'foreignKey' => 'id_udaj',
            'conditions' => array('f_revizia' => true),
            'order' => 'datum, id'
        ),
        'Images' => array(
            'className' => 'UdajObrazky',
            'foreignKey' => 'id_udaj'
        )
    );
    
    public $hasAndBelongsToMany = array(
        'Collectors' => array(
            'className' => 'MenaZberRev',
            'joinTable' => 'udaj_zber_asoc',
            'foreignKey' => 'id_udaj',
            'associationForeignKey' => 'id_meno_zber',
            'fields' => array('meno', 'std_meno'),
            'order' => 'UdajZberAsoc.poradie'
        )
    );
    
    /*
    public $findMethods = array('nabelek' => true);
    
    protected function _findNabelek($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions']['Udaj.project'] = 'nabelek';
            $query['limit'] = 1;
            return $query;
        }
        return $results;
    }
    */
}

