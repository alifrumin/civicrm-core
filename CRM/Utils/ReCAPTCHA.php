<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 */
class CRM_Utils_ReCAPTCHA {

  protected $_captcha = NULL;

  protected $_name = NULL;

  protected $_url = NULL;

  protected $_phrase = NULL;

  /**
   * Singleton.
   *
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var CRM_Utils_ReCAPTCHA
   */
  static private $_singleton = NULL;

  /**
   * Singleton function used to manage this object.
   *
   * @return object
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Utils_ReCAPTCHA();
    }
    return self::$_singleton;
  }


  /**
   * Check if reCaptcha settings is avilable to add on form.
   */
  public static function hasSettingsAvailable() {
    $config = CRM_Core_Config::singleton();
    if ($config->recaptchaPublicKey == NULL || $config->recaptchaPublicKey == "") {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Check if reCaptcha has to be added on form forcefully.
   */
  public static function hasToAddForcefully() {
    $config = CRM_Core_Config::singleton();
    if (!$config->forceRecaptcha) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Add element to form.
   *
   * @param CRM_Core_Form $form
   */
  public static function add(&$form) {
    $error = NULL;
    $config = CRM_Core_Config::singleton();
    $useSSL = FALSE;
    if (!function_exists('recaptcha_get_html')) {
      require_once 'packages/recaptcha/recaptchalib.php';
    }

    // Load the Recaptcha api.js over HTTPS
    $useHTTPS = TRUE;

    $html = recaptcha_get_html($config->recaptchaPublicKey, $error, $useHTTPS);

    $form->assign('recaptchaHTML', $html);
    $form->assign('recaptchaOptions', $config->recaptchaOptions);
    $form->add(
      'text',
      'g-recaptcha-response',
      'reCaptcha',
      NULL,
      TRUE
    );
    $form->registerRule('recaptcha', 'callback', 'validate', 'CRM_Utils_ReCAPTCHA');
    if ($form->isSubmitted() && empty($form->_submitValues['g-recaptcha-response'])) {
      $form->setElementError(
        'g-recaptcha-response',
        ts('Please go back and complete the CAPTCHA at the bottom of this form.')
      );
    }
  }

}
