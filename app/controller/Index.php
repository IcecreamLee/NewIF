<?php

namespace App\Controller;

use App\Core\BaseController\MController;

class Index extends MController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        return $this->response('hello world!')
    }
}
