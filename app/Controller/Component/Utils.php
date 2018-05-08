<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Utils
 * @author Matus
 */
class Utils extends Component {

    public $components = array();

    public function wrap($word, $str) {
        return $str . $word . $str;
    }
    
    /**
     * Converts DMS to degrees.
     * @param type $deg
     * @param type $min
     * @param type $sec
     * @param type $orientation
     */
    public function coordinate($deg, $min, $sec, $orientation) {
        $d = doubleval($deg);
        $m = doubleval($min) / 60;
        $s = doubleval($sec) / 3600;
        $o = ($orientation == 'S' || $orientation == 'W') ? -1 : 1;
        return $o * ($d + $m + $s);
    }

}
