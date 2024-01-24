<?php
/**
 * @package    Threeprong_InvisibleCaptcha
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Adds PHP 8.2 compatibility to Amasty's InvisibleCaptcha module.
 */
class Threeprong_InvisibleCaptcha_Model_Captcha extends Amasty_InvisibleCaptcha_Model_Captcha
{
    /**
     * Validation of token from Google
     *
     * @param string $token
     * @return array
     * @throws Zend_Http_Client_Exception
     */
    public function verify($token)
    {
        $verification = array(
            'success' => false,
            'error' => ''
        );

        $postOptions = array(
            'secret' => $this->_getSecretKey(),
            'response' => $token
        );

        try {
            $googleVerify = new Varien_Http_Adapter_Curl();
            $googleVerify->write(Zend_Http_Client::POST,
                self::GOOGLE_VERIFY_URL,
                '1.1',
                array(),
                $postOptions
            );
            $googleResponse = $googleVerify->read();
            $body = Zend_Http_Response::extractBody($googleResponse);
            $answer = json_decode($body);

            if (property_exists($answer, 'success')) {
                $success = $answer->success;

                if (isset($answer->{'score'})
                    && Mage::getStoreConfig('aminvisiblecaptcha/frontend/type') ===
                    \Amasty_InvisibleCaptcha_Model_System_Config_Source_Captcha_Type::V3
                    && $answer->{'score'} < (float)Mage::getStoreConfig('aminvisiblecaptcha/frontend/v3_score')
                ) {
                    $verification['error'] = Mage::helper('core')->escapeHtml(
                        Mage::getStoreConfig('aminvisiblecaptcha/frontend/error_message')
                    );
                    $verification['success'] = false;
                } elseif ($success) {
                    $verification['success'] = true;
                } elseif (property_exists($answer, 'error-codes')) {
                    $error = $answer->{'error-codes'};
                    $verification['error'] = $this->_getErrorByCode($error[0]);
                }
            }
        } catch (Exception $e) {
            Mage::log($e->__toString(), null, 'Amasty_InvisibleCaptcha.log');
        }

        return $verification;
    }
}
