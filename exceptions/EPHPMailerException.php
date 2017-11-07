<?php

/*  Copyright 2009-2011 Rafael Gutierrez Martinez
 *  Copyright 2012-2013 Welma WEB MKT LABS, S.L.
 *  Copyright 2014-2016 Where Ideas Simply Come True, S.L.
 *  Copyright 2017 nabu-3 Group
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

namespace providers\phpmailer\phpmailer\exceptions;
use nabu\core\exceptions\ENabuException;

/**
 * Exception Class of PHPMailer Messaging.
 * @author Rafael Gutierrez <rgutierrez@nabu-3.com>
 * @since 0.0.2
 * @version 0.0.2
 * @package \providers\phpmailer\phpmailer\exceptions
 */
class EPHPMailerException extends ENabuException
{
    /** @var int PHPMailer native connector already instantiated. */
    const ERROR_NATIVE_CONNECTOR_ALREADY_INSTANTIATED               = 0x0001;
    /** @var int Insufficient SMTP Connection parameters. */
    const ERROR_INSUFFICIENT_SMTP_CONNECTION_PARAMS                 = 0x0002;

    /**
     * List of all error messages defined in this exception.
     * @var array
     */
    private static $error_messages = array(
        EPHPMailerException::ERROR_NATIVE_CONNECTOR_ALREADY_INSTANTIATED =>
            'PHPMailer native connector already instantiated.',
        EPHPMailerException::ERROR_INSUFFICIENT_SMTP_CONNECTION_PARAMS =>
            'Insufficient STMP Connection parameters'
    );

    public function __construct($code, $values = null)
    {
        parent::__construct(EPHPMailerException::$error_messages[$code], $code, $values);
    }
}
