<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 9 2018
 *
 */
namespace Modules\ModuleZabbixAgent5\App\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;


class ModuleZabbixAgent5Form extends Form
{

    public function initialize($entity = null, $options = null) :void
    {
        // id
        $this->add(new Hidden('id'));

        // Config file
        $this->add(new Hidden('configContent'));
    }
}
