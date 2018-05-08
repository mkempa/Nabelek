<?php

class Utility {
    
    /**
     * Creates decadic representation from degrees, minutes, seconds
     * @param type $d
     * @param type $m
     * @param type $s
     */
    public static function coordinates($d, $m, $s) {
        $dd = self::replaceComa(self::emptyToZero($d));
        $mm = self::replaceComa(self::emptyToZero($m));
        $ss = self::replaceComa(self::emptyToZero($s));
        if (!is_numeric($dd)) {
            throw new InvalidArgumentException('Degrees value is not a number');
        }
        if (!is_numeric($mm)) {
            throw new InvalidArgumentException('Minutes value is not a number');
        }
        if (!is_numeric($ss)) {
            throw new InvalidArgumentException('Seconds value is not a number');
        }
        return doubleval($dd) + doubleval($mm) / 60 + doubleval($ss) / 3600;
    }
    
    public static function emptyToZero($s) {
        return empty($s) ? '0' : $s;
    }
    
    public static function orientation($s) {
        if ($s == 'N' || $s == 'E') {
            return 1;
        }
        if ($s == 'S' || $s == 'W') {
            return -1;
        }
        return 0;
    }
    
    public static function replaceComa($s) {
        return str_replace(',', '.', $s);
    }
    
}

