<?php

/*  Copyright 2009-2011 Rafael Gutierrez Martinez
 *  Copyright 2012-2013 Welma WEB MKT LABS, S.L.
 *  Copyright 2014-2016 Where Ideas Simply Come True, S.L.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace providers\phpmailer\phpmailer;
use nabu\core\interfaces\INabuApplication;
use nabu\messaging\managers\base\CNabuMessagingManager;

/**
 * Class to manage PHPMailer library
 * @author Rafael Gutierrez <rgutierrez@wiscot.com>
 * @since 0.0.1
 * @version 0.0.1
 * @package \providers\phpmailer\phpmailer
 */
class CPHPMailerManager extends CNabuMessagingManager
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setVendorKey(PHPMAILER_VENDOR_KEY);
        $this->setModuleKey(PHPMAILER_MODULE_KEY);
    }

    public function enableManager()
    {
        return true;
    }

    public function registerApplication(INabuApplication $nb_application)
    {
        return $this;
    }
}
