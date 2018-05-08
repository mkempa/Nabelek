<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP AnnoSysComponent
 * @author Matus
 */
class AnnoSysComponent extends Component {

    public function getAnnotations($authority, $namespace, $objectId) {
        if (!$authority) {
            throw new InvalidArgumentException(__('Invalid parameter authority'));
        }
        if (!$namespace) {
            throw new InvalidArgumentException(__('Invalid argument namespace'));
        }
        if (!$objectId) {
            throw new InvalidArgumentException(__('Invalid argument objectID'));
        }
        $url = ANNOSYS_BASE_URL . "/services/records/$authority/$namespace/$objectId/annotations";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            $response = null;
        }
        return $response;
    }

}
