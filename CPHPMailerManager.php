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
use nabu\core\CNabuEngine;
use nabu\core\interfaces\INabuApplication;
use nabu\messaging\CNabuMessagingServiceInterfaceDescriptor;
use nabu\messaging\interfaces\INabuMessagingServiceInterface;
use nabu\messaging\managers\base\CNabuMessagingModuleManagerAdapter;

/**
 * Class to manage PHPMailer library
 * @author Rafael Gutierrez <rgutierrez@wiscot.com>
 * @since 0.0.1
 * @version 0.0.2
 * @package \providers\phpmailer\phpmailer
 */
class CPHPMailerManager extends CNabuMessagingModuleManagerAdapter
{
    /** @var CNabuMessagingServiceInterfaceDescriptor $nb_messaging_account_descriptor Messaging Account descriptor. */
    private $nb_messaging_account_descriptor = null;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(PHPMAILER_VENDOR_KEY, PHPMAILER_MODULE_KEY);
    }

    public function enableManager()
    {
        $nb_engine = CNabuEngine::getEngine();

        $this->nb_messaging_service_descriptor = new CNabuMessagingServiceInterfaceDescriptor(
            $this,
            'PHPMailerService',
            'PHPMailer Service'
        );

        $nb_engine->registerProviderInterface($this->nb_messaging_service_descriptor);

        return true;
    }

    public function registerApplication(INabuApplication $nb_application)
    {
        return $this;
    }

    public function createServiceInterface(string $name)
    {

    }

    public function releaseServiceInterface(INabuMessagingServiceInterface $interface)
    {
        
    }
}
