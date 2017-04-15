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

namespace providers\phpmailer\phpmailer\services;
use nabu\core\CNabuObject;
use nabu\data\messaging\CNabuMessagingService;
use nabu\messaging\interfaces\INabuMessagingServiceInterface;
use providers\phpmailer\phpmailer\CPHPMailerManager;
use providers\phpmailer\phpmailer\exceptions\EPHPMailerException;

/**
 * Class to use PHPMailer as a Service Interface for Messaging.
 * @author Rafael Gutierrez <rgutierrez@nabu-3.com>
 * @since 0.0.2
 * @version 0.0.2
 * @package \providers\phpmailer\phpmailer\services
 */
class CPHPMailerServiceInterface extends CNabuObject implements INabuMessagingServiceInterface
{
    /** @var CPHPMailerManager Manager instance associated with this interface. */
    private $phpmailer_manager = null;

    /** @var \PHPMailer PHPMailer native instance. */
    private $phpmailer_native = null;

    /**
     * Default constructor.
     * @param CPHPMailerManager $phpmailer_manager PHPMailer Manager that owns this instance.
     */
    public function __construct(CPHPMailerManager $phpmailer_manager)
    {
        $this->phpmailer_manager = $phpmailer_manager;
    }

    public function init()
    {
        if ($this->phpmailer_native !== null) {
            throw new EPHPMailerException(EPHPMailerException::ERROR_NATIVE_CONNECTOR_ALREADY_INSTANTIATED);
        }

        $this->phpmailer_native = new \PHPMailer();
        $this->phpmailer_native->isSMTP();
        $this->phpmailer_native->SMTPKeepAlive = true;

        return true;
    }

    public function connect(CNabuMessagingService $nb_messaging_service)
    {
        $attributes = $nb_messaging_service->getAttributes();

        if (is_array($attributes) &&
            is_string($host = $this->getAttribute($attributes, 'smtp_host')) &&
            is_integer($port = $this->getAttribute($attributes, 'smtp_port')) &&
            is_bool($use_tls = (bool)$this->getAttribute($attributes, 'smtp_use_tls')) &&
            is_string($user = $this->getAttribute($attributes, 'smtp_user')) &&
            is_string($pass = $this->getAttribute($attributes, 'smtp_password'))
        ) {
            $this->phpmailer_native->Host = $host;
            $this->phpmailer_native->Port = $port;
            $this->phpmailer_native->SMTPSecure = ($use_tls ? 'tls' : '');
            $this->phpmailer_native->SMTPAuth = true;
            $this->phpmailer_native->Username = $user;
            $this->phpmailer_native->Password = $pass;

            error_log("******> Connected");
        } else {
            throw new EPHPMailerException(EPHPMailerException::ERROR_INSUFFICIENT_SMTP_CONNECTION_PARAMS);
        }
    }

    public function disconnect()
    {
        if ($this->phpmailer_native !== null) {
            $this->phpmailer_native->smtpClose();
        }
    }

    public function finish()
    {
    }

    /**
     * Check the attributes list for an attribute and returns his value if exists or a default value if not.
     * @param array $list List of attributes to be inspected.
     * @param string $name Name of attribute.
     * @param mixed $default Default value if attribute does not exists.
     * @return mixed Returns the attribute value in $list if exists or $default if not.
     */
    private function getAttribute(array $list, string $name, $default = false)
    {
        return array_key_exists($name, $list) ? $list[$name] : $default;
    }
}
