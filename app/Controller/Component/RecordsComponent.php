<?php

App::uses('Component', 'Controller');

class RecordsComponent extends Component {

    public $uses = array('Utils');

    public function worldsJoin() {
        $joins = array(
            array(
                'table' => 'brumit2',
                'alias' => 'Brumit2',
                'type' => 'LEFT',
                'conditions' => array(
                    'Brumit2.id = Brumit3.id_parent'
                )
            ),
            array(
                'table' => 'brumit1',
                'alias' => 'Brumit1',
                'type' => 'LEFT',
                'conditions' => array(
                    'Brumit1.id = Brumit2.id_parent'
                )
            )
        );
        return $joins;
    }

    public function joins() {
        $joins = array(
            array(
                'table' => 'mena_taxonov',
                'alias' => 'MenaTaxonovOrig',
                'type' => 'LEFT',
                'conditions' => array('SkupRevOrig.id_meno_rast_prirad = MenaTaxonovOrig.id')
            ),
            array(
                'table' => 'list_of_species',
                'alias' => 'ListOfSpeciesOriginal',
                'type' => 'LEFT',
                'conditions' => array('MenaTaxonovOrig.id_std_meno = ListOfSpeciesOriginal.id')
            ),
            array(
                'table' => 'genus',
                'alias' => 'GenusOriginal',
                'type' => 'LEFT',
                'conditions' => array('ListOfSpeciesOriginal.id_genus = GenusOriginal.id')
            ),
            array(
                'table' => 'v_skup_rev_najnovsie_revizie',
                'alias' => 'LatestRev',
                'type' => 'LEFT',
                'conditions' => array('Udaj.id = LatestRev.id_udaj')
            ),
            array(
                'table' => 'skup_rev',
                'alias' => 'SkupRevRev',
                'type' => 'LEFT',
                'conditions' => array('SkupRevRev.id = LatestRev.id')
            ),
            array(
                'table' => 'mena_taxonov',
                'alias' => 'MenaTaxonovRev',
                'type' => 'LEFT',
                'conditions' => array('SkupRevRev.id_meno_rast_prirad = MenaTaxonovRev.id')
            ),
            array(
                'table' => 'list_of_species',
                'alias' => 'ListOfSpeciesRev',
                'type' => 'LEFT',
                'conditions' => array('MenaTaxonovRev.id_std_meno = ListOfSpeciesRev.id')
            ),
            array(
                'table' => 'genus',
                'alias' => 'GenusRev',
                'type' => 'LEFT',
                'conditions' => array('ListOfSpeciesRev.id_genus = GenusRev.id')
            ),
            array(
                'table' => 'brumit4',
                'alias' => 'B4',
                'type' => 'LEFT',
                'conditions' => array('Lokality.id_brumit4 = B4.id')
            )
        );
        return $joins;
    }

    public function quicksearchConditions($type, $term) {
        $tterm = trim($term);
        $conditions = array();
        if ($type == 'collnum') {
            $cn = '%' . strtolower($tterm) . '%';
            $conditions['HerbarPolozky.cislo_zberu ILIKE'] = $cn;
        }
        if ($type == 'name') {
            $name = '%' . strtolower($tterm) . '%';
            $conditions[] = array('OR' => array('ListOfSpeciesOriginal.meno ILIKE' => $name, 'ListOfSpeciesRev.meno ILIKE' => $name));
        }
        if ($type == 'authors') {
            $authors = array_map('trim', preg_split('/[\s,]+/', $tterm));
            $ors = array();
            foreach ($authors as $author) {
                $a = '%' . strtolower($author) . '%';
                $ors = Set::merge($ors, array('ListOfSpeciesOriginal.autori ILIKE' => $a, 'ListOfSpeciesRev.autori ILIKE' => $a));
            }
            $conditions[] = array('OR' => $ors);
        }
        if ($type == 'barcode') {
            $barcode = '%' . strtoupper($tterm) . '%';
            $conditions['HerbarPolozky.cislo_ck_full LIKE'] = $barcode;
        }
        if ($type == 'free') { //search in all of the above, locality description, collection number, brummit4
            $t = '%' . strtolower($tterm) . '%';
            $conditions[] = array('OR' => array('ListOfSpeciesOriginal.meno ILIKE' => $t, 'ListOfSpeciesOriginal.autori ILIKE' => $t,
                    'ListOfSpeciesRev.meno ILIKE' => $t, 'ListOfSpeciesRev.autori ILIKE' => $t,
                    'HerbarPolozky.cislo_ck_full ILIKE' => $t, 'HerbarPolozky.cislo_zberu' => $t,
                    'Lokality.opis_lokality ILIKE' => $t, 'B4.meno ILIKE' => $t));
        }
        //return array('AND' => array('HerbarPolozky.cislo_ck_full LIKE' => 'SAV%', $conditions));
        return $conditions;
    }

    public function advancedsearchConditions($genus, $species, $full, $collnum, $locality, $lat, $lon, $range, $country) {
        if (!empty($lat) && !is_numeric($lat)) {
            throw new InvalidArgumentException('Latitude is not a number');
        }
        if (!empty($lon) && !is_numeric($lon)) {
            throw new InvalidArgumentException('Longitude is not a number');
        }
        if (!empty($range) && !is_numeric($range)) {
            throw new InvalidArgumentException('Range is not a number');
        }
        $name = array();
        if (!empty($genus) || !empty($species)) {
            $name = array(
                'OR' => array(
                    array(
                        'ListOfSpeciesOriginal.meno ILIKE' => "%$species%",
                        'GenusOriginal.meno ILIKE' => "%$genus%"
                    ),
                    array(
                        'ListOfSpeciesRev.meno ILIKE' => "%$species%",
                        'GenusRev.meno ILIKE' => "%$genus%"
                    )
                )
            );
        } else {
            $name = array(
                'OR' => array(
                    'ListOfSpeciesOriginal.meno ILIKE' => "%$full%",
                    'ListOfSpeciesRev.meno ILIKE' => "%$full%",
                )
            );
        }
        $conditions[] = $name;
        if (!empty($locality)) {
            $conditions[] = array('OR' => array('Lokality.opis_lokality ILIKE' => "%$locality%", 'B4.meno ILIKE' => "%$locality%"));
        }
        if (!empty($collnum)) {
            $conditions['HerbarPolozky.cislo_zberu ILIKE'] = '%' . strtolower(trim($collnum)) . '%';
        }
        if (empty($range)) {
            $range = 0.0;
        }
        if (!empty($lat) && !empty($lon)) {
            $conditions['Lokality.latitude <='] = $lat + $range;
            $conditions['Lokality.latitude >='] = $lat - $range;
            $conditions['Lokality.longitude >='] = $lon - $range;
            $conditions['Lokality.longitude <='] = $lon + $range;
        }
        if (!empty($country)) {
            if ($country == 999) { //palestine
                $conditions[] = array('OR' => array('Lokality.id_brumit4' => 459, 'Lokality.id_brumit4' => 460));
            } else {
                $conditions['Lokality.id_brumit4'] = $country;
            }
        }
        return $conditions;
    }

}
