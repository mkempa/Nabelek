<?php

App::uses('AppController', 'Controller');

class ImagesController extends AppController {

    public function view($barcode) {
        if (!$barcode) {
            throw new NotFoundException(__('record not found'));
        }
        $image = $barcode . 'p.tif';
        $this->set('image', $image);
        $this->render('view', 'iipviewer');
    }

}
