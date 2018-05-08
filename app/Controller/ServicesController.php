<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP ServicesController
 * @author Matus
 */
class ServicesController extends AppController {
    
    public $components = array('Biocase', 'RequestHandler');

    public function record($id, $schema) {
        if (!$id) {
            throw new InvalidArgumentException(__("Ivalid argument id"));
        }
        if (!$schema) {
            throw new InvalidArgumentException(__("Ivalid argument schema"));
        }
        switch ($schema){
            case 'abcd2.06':
                $this->response->type('xml');
                $p = $this->Biocase->buildAbcd206Query($id);
                break;
            default:
                break;
        }
        $result = $this->Biocase->sendQuery($p);
        $this->set(compact('result'));
        $this->layout = 'xml/default';
    }

}
