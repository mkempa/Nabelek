<?php

App::uses('AppHelper', 'View/Helper');

class FormatHelper extends AppHelper {

    public $helpers = array('Html');

    public function chkEmpty($string) {
        if (empty(trim($string))) {
            return '-';
        }
        return $string;
    }

    public function taxonName($name, $authors) {
        $nameStripped = trim($name, " *");
        return '<i>' . $nameStripped . '</i> ' . $authors;
    }

    public function detailValue($label, $value, $link = false, $wrap = true) {
        $out = ($wrap ? '<p>' : '');
        $out .= '<span class="label">' . $label . '</span><span class="value">';
        if (!isset($value) || empty($value) || $value == 'null') {
            $value = '-';
        }
        if (is_array($value)) {
            $out .= '<ul>';
            foreach ($value as $k => $v) {
                $out .= '<li>';
                $out .= $v;
                $out .= '</li>';
            }
            $out .= '</ul>';
        } else {
            $out .= $value;
        }
        $out .= '</span>';
        $out .= ($wrap ? '</p>' : '');
        return $out;
    }

    public function convCoordinates($coord) {
        if (floatval($coord)) {
            return $this->_dToDMS(floatval($coord));
        }
        return $this->_DMStoD($coord);
    }

    public function coordinates($lat, $lon) {
        if (empty($lat) && empty($lon)) {
            return '';
        }
        $latO = $this->orientation($lat);
        $lonO = $this->orientation($lon, false);
        return "$lat&deg; $latO, $lon&deg; $lonO";
    }

    public function orientation($coord, $lat = true) {
        if (empty($coord)) {
            return '';
        }
        if ($lat) {
            return floatval($coord) >= 0 ? 'N' : 'S';
        }
        return floatval($coord) >= 0 ? 'E' : 'W';
    }

    /**
     * 
     * @param type $datestring string in format yyyymmdd
     */
    public function date($datestring) {
        if ($datestring == '00000000') {
            return '';
        }
        $d = '';
        $m = '';
        $y = '';
        if (substr($datestring, -2) != '00') {
            $d = substr($datestring, -2);
        }
        if (substr($datestring, -4, 2) != '00') {
            $md = DateTime::createFromFormat('m', substr($datestring, -4, 2));
            $m = $md->format('M');
        }
        if (substr($datestring, 0, 4) != '0000') {
            $y = substr($datestring, 0, 4);
        }
        return join(' ', array($d, $m, $y));
    }

    public function gallery($images) {
        $out = '<div class="row">';
        $i = 0;
        foreach ($images as $im) {
            if ($i % 3 == 0) {
                $out .= '</div>';
                $out .= '<div class="row">';
            }
            $pos = strpos($im['url'], 'SAV', 27);
            $src = substr($im['url'], $pos, -4);
            $out .= '<div class="col-sm-4">';
            $out .= $this->Html->link(
                    $this->Html->image('thumbs/' . $src . '_thumb.jpg', array('class' => 'img-responsive', 'alt' => $im['url'], 'data' => $src)), '#', array('class' => 'thumbnail center-block preview', 'escape' => false));
            $out .= '</div>';
            $i++;
        }
        $out .= '</div>';
        return $out;
    }

    /**
     * Makes threaded array of comments into one-dim array with nested property
     * @param type $value
     * @return type
     */
    public function flatComments($comments, $result, $nested) {
        foreach ($comments as $c) {
            $nc = array(); //new comment variable
            $c['UdajComment']['nested'] = $nested;
            $nc['UdajComment'] = $c['UdajComment'];
            array_push($result, $nc);
            if (!empty($c['children'])) {
                $result = $this->flatComments($c['children'], $result, $nested + 1);
            }
        }
        return $result;
    }

    private function _dToDMS($value) {
        $d = floor($value);
        $tmp = ($value - $d) * 60;
        $m = floor($tmp);
        $s = round(($tmp - $m) * 60, 3);
        if ($s >= 60) {
            $s -= 60;
            $m++;
        }
        if ($m >= 60) {
            $m -= 60;
            $d++;
        }
        return $d . "°$m'$s''";
    }

    private function _DMStoD($str) {
        
    }

}
