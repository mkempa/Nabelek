<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP AnnotationsController
 * @author Matus
 */
class AnnotationsController extends AppController {

    public $components = array('AnnoSys', 'RequestHandler');

    public function view($id) {
        if (!$this->request->is('requested')) {
            throw new MethodNotAllowedException(__("Such use of the method is not allowed"));
        }
        $annotations = $this->AnnoSys->getAnnotations('SAV', 'Dataflos', $id); //json
        //$this->set(compact('annotations'));
        return $annotations;
    }

}
