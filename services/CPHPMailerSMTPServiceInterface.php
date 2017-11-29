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

namespace providers\phpmailer\phpmailer\services;
use nabu\core\CNabuEngine;
use nabu\core\CNabuObject;
use nabu\data\messaging\CNabuMessagingService;
use nabu\data\security\CNabuUser;
use nabu\data\security\CNabuUserList;
use nabu\messaging\exceptions\ENabuMessagingException;
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
class CPHPMailerSMTPServiceInterface extends CNabuObject implements INabuMessagingServiceInterface
{
    /** @var CPHPMailerManager Manager instance associated with this interface. */
    private $phpmailer_manager = null;
    /** @var \PHPMailer PHPMailer native instance. */
    private $phpmailer_native = null;
    /** @var string $error_message The error message returned after sent message. */
    private $error_message = null;

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
            is_bool($use_auth = $this->getAttribute($attributes, 'smtp_auth')) &&
            is_string($from_mailbox = $this->getAttribute($attributes, 'from_mailbox'))

        ) {
            if (($smtp_debug = $this->getAttribute($attributes, 'smtp_debug')) &&
                is_numeric($smtp_debug) && nb_isBetween($smtp_debug, 0, 4)
            ) {
                $this->phpmailer_native->SMTPDebug = $smtp_debug;
                $this->phpmailer_native->Debugoutput = function($str, $level) { error_log ("debug level $level; message: $str");};
            } else {
                $this->phpmailer_native->SMTPDebug = 0;
            }

            $this->phpmailer_native->Host = $host;
            $this->phpmailer_native->Port = $port;

            if (is_bool($use_tls = (bool)$this->getAttribute($attributes, 'smtp_use_tls')) && $use_tls) {
                $this->phpmailer_native->SMTPSecure = ($use_tls ? 'tls' : '');
            } else {
                $this->phpmailer_native->SMTPSecure = false;
                $this->phpmailer_native->SMTPAutoTLS = 0;
            }

            if (is_string($user = $this->getAttribute($attributes, 'smtp_user')) &&
                is_string($pass = $this->getAttribute($attributes, 'smtp_password'))
            ) {
                $this->phpmailer_native->SMTPAuth = true;
                $this->phpmailer_native->Username = $user;
                $this->phpmailer_native->Password = $pass;
            } else {
                $this->phpmailer_native->SMTPAuth = false;
            }
        } else {
            throw new EPHPMailerException(EPHPMailerException::ERROR_INSUFFICIENT_SMTP_CONNECTION_PARAMS);
        }

        if (is_string($reply_to = $this->getAttribute($attributes, 'reply_to'))) {
            $this->phpmailer->addReplyTo($reply_to);
        }

        $from_name = $this->getAttribute($attributes, 'from_name');
        if (strlen($from_name) === 0) {
            $from_name = $from_mailbox;
        }
        $this->phpmailer_native->setFrom($from_mailbox, $from_name);

        if (is_string($charset = $this->getAttribute($attributes, 'charset')) && strlen($charset) > 0) {
            $this->phpmailer_native->CharSet = $charset;
        } else {
            $this->phpmailer_native->CharSet = NABU_CHARSET;
        }

        error_log(print_r($this->phpmailer_native, true));
    }

    public function send($to, $cc, $bcc, $subject, $body_html, $body_text, $attachments) : int
    {
        $to_address = $this->refineAddressList($to);
        $cc_address = $this->refineAddressList($cc);
        $bcc_address = $this->refineAddressList($bcc);

        if (count($to_address) === 0 && count($cc_address) === 0 && count($bcc_address) === 0) {
            throw new ENabuMessagingException(ENabuMessagingException::ERROR_TARGET_ADDRESS_BOXES_NOT_DEFINED);
        }

        $this->phpmailer_native->clearAddresses();
        $this->phpmailer_native->clearAttachments();
        $this->phpmailer_native->clearBCCs();
        $this->phpmailer_native->clearCCs();
        $this->phpmailer_native->clearCustomHeaders();
        $this->phpmailer_native->clearReplyTos();
        $this->phpmailer_native->clearAllRecipients();
        $this->phpmailer_native->MessageDate = \PHPMailer::rfcDate();

        if (count($to_address) > 0) {
            foreach ($to_address as $recipient) {
                $this->phpmailer_native->addAddress($recipient['address'], $recipient['name']);
            }
        }

        if (count($cc_address) > 0) {
            foreach ($cc_address as $recipient) {
                $this->phpmailer_native->addCC($recipient['address'], $recipient['name']);
            }
        }

        if (count($bcc_address) > 0) {
            foreach ($bcc_address as $recipient) {
                $this->phpmailer_native->addBCC($recipient['address'], $recipient['name']);
            }
        }

        $this->phpmailer_native->Subject = $subject;
        if (strlen($body_html) > 0) {
            $this->phpmailer_native->isHTML(true);
            $this->phpmailer_native->Body = $body_html;
            $this->phpmailer_native->AltBody = $body_text;
        } else {
            $this->phpmailer_native->isHTML(false);
            $this->phpmailer_native->Body = $body_text;
        }

        if ($this->phpmailer_native->send()) {
            $this->error_message = false;
        } else {
            $this->error_message = $this->phpmailer_native->ErrorInfo;
        }

        if ($this->error_message) {
            CNabuEngine::getEngine()->errorLog($this->error_message);
        }

        return $this->error_message ? 1 : 0;
    }

    public function post($to, $cc, $bcc, $subject, $body_html, $body_text, $attachments) : int
    {
        return $this->send($to, $cc, $bcc, $subject, $body_html, $body_text, $attachments);
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

    /**
     * Transforms a list of inboxes with mixed types to a valid list for PHPMailer.
     * @param mixed $list
     * @return array|null Returns a valid Array conformed to be used directly by PHPMailer or null if $list is empty.
     * @throws ENabuMessagingException Raises an exception if some address box is invalid.
     */
    private function refineAddressList($list)
    {
        if (is_string($list)) {
            $addresses = preg_split('/\\s*,\\s*/', $list);
            if (is_array($addresses) && count($addresses) > 0) {
                $retval = array();
                foreach($addresses as $address) {
                    $parts = preg_split('/(.*)<(.*)/', $address);
                    if (count($parts) === 1) {
                        $retval[] = array(
                            'address' => $parts[0],
                            'name' => null
                        );
                    } elseif (count($parts) === 2) {
                        $retval[] = array(
                            'address' => $parts[0],
                            'name' => $parts[1]
                        );
                    } elseif (count($parts) === 0) {
                        $retval[] = array(
                            'address' => $address,
                            'name' => null
                        );
                    }
                }
            } else {
                throw new ENabuMessagingException(
                    ENabuMessagingException::ERROR_INVALID_ADDRESS_BOX,
                    array(print_r($list, true))
                );
            }
        } elseif (is_array($list)) {
            $retval = array();
            foreach ($list as $item) {
                $sublist = $this->refineAddressList($item);
                $retval = array_merge($retval, $sublist);
            }
        } elseif ($list instanceof CNabuUser) {
            $retval = array(array(
                'address' => $list->getEmail(),
                'name' => preg_replace(
                    '/\\s{2,}/',
                    ' ',
                    trim($list->getFirstName() . ' ' . $list->getLastName())
                )
            ));
        } elseif ($list instanceof CNabuUserList) {
            $retval = array();
            $list->iterate(function($key, CNabuUser $nb_user) use ($retval) {
                $retval[] = array(
                    'address' => $nb_user->getEmail(),
                    'name' => preg_replace(
                        '/\\s{2,}/',
                        ' ',
                        trim($nb_user->getFirstName() . ' ' . $nb_user->getLastName())
                    )
                );
            });
        } elseif ($list) {
            throw new ENabuMessagingException(
                ENabuMessagingException::ERROR_INVALID_ADDRESS_BOX,
                array(print_r($list, true))
            );
        } else {
            $retval = null;
        }

        return $retval;
    }
}
