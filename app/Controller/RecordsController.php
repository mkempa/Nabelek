<?php

//require_once 'D:\Apache Software Foundation\xampp\htdocs\Nabelek\app\Lib\dBug.php';
App::uses('AppController', 'Controller');
App::uses('Utility', 'Utility');

class RecordsController extends AppController {

    public $uses = array('Brumit1', 'Brumit2', 'Brumit3', 'Brumit4', 'Genus',
        'Herbar', 'HerbarPolozky', 'MenaTaxonov', 'Udaj', 'UdajComment', 'SkupRev');
    public $components = array('Paginator', 'Records');
    public $helpers = array('Html', 'Form', 'Format');

    public function search($type = '') {
        $params = $this->request->query;
        //$this->set(compact('params'));
        if (!empty($type) && !empty($params)) {
            $conditions = array();
            if ($type == 'advanced') {
                $lat = Utility::orientation($params['latOrientation']) * Utility::coordinates($params['latDegrees'], $params['latMinutes'], $params['latSeconds']);
                $lon = Utility::orientation($params['lonOrientation']) * Utility::coordinates($params['lonDegrees'], $params['lonMinutes'], $params['lonSeconds']);
                $conditions = $this->Records->advancedsearchConditions($params['genus'], $params['species'], $params['fullname'], $params['collnum'], $params['locality'], $lat, $lon, doubleval(Utility::emptyToZero($params['range'])), $params['country']);
            } else if ($type == 'quick') {
                $conditions = $this->Records->quicksearchConditions($params['type'], $params['search-term']);
            }
            $conditions['Udaj.project'] = 'nabelek';

            $this->Udaj->unbindModel(array('hasMany' => 'SkupRevRev'));
            $this->Paginator->settings = array('Udaj' => array(
                    'fields' => array('DISTINCT Udaj.id', 'HerbarPolozky.cislo_ck_full', 'HerbarPolozky.cislo_zberu',
                        'ListOfSpeciesOriginal.meno', 'ListOfSpeciesOriginal.autori',
                        'ListOfSpeciesRev.meno', 'ListOfSpeciesRev.autori',
                        'Lokality.opis_lokality', 'B4.meno'),
                    'joins' => $this->Records->joins(),
                    'conditions' => $conditions,
                    'limit' => 5,
                    'order' => array('HerbarPolozky.cislo_ck_full')
            ));
            $udajs = $this->Paginator->paginate('Udaj');
            $this->set(compact('udajs'));
        }
                
        //------ hardcoded countries -----------
        /*
         * 198 = Bahrain
         * 283 = Iran
         * 284 = Iraq
         * 459 = Israel
         * 460 = Jordan
         * 316 = Lebanon
         * 317 = Syria
         * 554 = Turkey
         */
        $ids = array(198, 283, 284, 459, 460, 316, 317, 554);
        //--------------------------------------
        
        $countries = $this->Brumit4->getList($ids);
        $this->set(compact('countries', 'udajs'));
    }

    public function view($id) {
        if (!$id) {
            throw new NotFoundException(__('Record not found'));
        }
        $this->Udaj->unbindModel(array('hasMany' => array('Comments')));
        $udaj = $this->Udaj->find('first', array(
            'conditions' => array(
                'Udaj.project' => 'nabelek',
                'HerbarPolozky.cislo_ck_full' => $id
            )
        ));
        if (empty($udaj)) {
            throw new NotFoundException(__('Record not found'));
        }
        //$comments = $this->UdajComment->generateTreeList(array('UdajComment.id_udaj' => $udaj['Udaj']['id']), null, null, '_');
        $comments = $this->UdajComment->findCompleteTree($udaj['Udaj']['id']);

        $this->Brumit4->unbindModel(array('hasMany' => 'Lokality'));
        $worlds = $this->Brumit4->find('all', array(
            'fields' => array('Brumit4.meno', 'Brumit3.meno', 'Brumit2.meno', 'Brumit1.meno'),
            'joins' => $this->Records->worldsJoin(),
            'conditions' => array('Brumit4.id' => $udaj['Lokality']['id_brumit4'])));
        $udaj['Worlds'] = $worlds[0];
        $this->Herbar->unbindModel(array('hasMany' => 'HerbarPolozky'));
        $udaj = Set::merge($udaj, $this->Herbar->findById($udaj['HerbarPolozky']['id_herbar']));
        //$udaj['SkupRevOrig']['MenaTaxonovOrig'] = $this->_identification($udaj['SkupRevOrig']);
        $udaj['SkupRevOrig'] = Set::merge($udaj['SkupRevOrig'], $this->_identification($udaj['SkupRevOrig']));
        $countSRR = count($udaj['SkupRevRev']);
        if ($countSRR > 0) {
            foreach ($udaj['SkupRevRev'] as &$rev) {
                $rev = Set::merge($rev, $this->_identification($rev));
            }
        }
        $this->set(compact('udaj', 'comments'));
    }

    private function _identification($skupRev) {
        $this->MenaTaxonov->unbindModel(array('hasOne' => 'SkupRev'));
        $mena = $this->MenaTaxonov->findById($skupRev['id_meno_rast_prirad']);
        $this->Genus->unbindModel(array('hasMany' => 'ListOfSpecies'));
        $genus = $this->Genus->findById($mena['ListOfSpecies']['id_genus']);
        $this->SkupRev->unbindModel(array('belongsTo' => array('Udaj', 'MenaTaxonov')));
        $detBy = $this->SkupRev->findById($skupRev['id']);
        $res = Set::merge($mena, $genus, $detBy);
        return $res;
    }

    /*
    protected function _searchQuick($type, $term) {
        //find basic ids by conditions
        $conditions = $this->Records->quicksearchConditions($type, $term);
        $conditions['Udaj.project'] = 'nabelek';
        $this->Udaj->unbindModel(array('hasMany' => 'SkupRevRev'));
        $udajs = $this->Udaj->find('all', array(
            'fields' => array('DISTINCT Udaj.id', 'HerbarPolozky.cislo_ck_full', 'HerbarPolozky.cislo_zberu',
                'ListOfSpeciesOriginal.meno', 'ListOfSpeciesOriginal.autori',
                'ListOfSpeciesRev.meno', 'ListOfSpeciesRev.autori',
                'Lokality.opis_lokality', 'B4.meno'),
            'joins' => $this->Records->joins(),
            'conditions' => $conditions,
            //'limit' => 5,
            'order' => array('HerbarPolozky.cislo_ck_full')
        ));
        return $udajs;
    }

    protected function _searchAdvanced($genus, $species, $full, $collnum, $locality, $lat, $lon, $range) {
        $conditions = $this->Records->advancedsearchConditions($genus, $species, $full, $collnum, $locality, $lat, $lon, $range);
        $conditions['Udaj.project'] = 'nabelek';
        $this->Udaj->unbindModel(array('hasMany' => 'SkupRevRev'));
        $udajs = $this->Udaj->find('all', array(
            'fields' => array('DISTINCT Udaj.id', 'HerbarPolozky.cislo_ck_full', 'HerbarPolozky.cislo_zberu',
                'ListOfSpeciesOriginal.meno', 'ListOfSpeciesOriginal.autori',
                'ListOfSpeciesRev.meno', 'ListOfSpeciesRev.autori',
                'Lokality.opis_lokality', 'B4.meno'),
            'joins' => $this->Records->joins(),
            'conditions' => $conditions,
            //'limit' => 5,
            'order' => array('HerbarPolozky.cislo_ck_full')
        ));

        return $udajs;
    }
*/
}
