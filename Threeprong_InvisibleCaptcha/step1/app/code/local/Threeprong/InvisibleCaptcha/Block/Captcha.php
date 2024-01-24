<?php
/**
 * @package    Threeprong_InvisibleCaptcha
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Replaces Amasty_InvisibleCaptcha_Block_Captcha. This is necessary because the quoteEscape method is not compatible
 * with that in the core block template, which causes fatal errors due to function signature mismatch with PHP 8.0+
 */
class Threeprong_InvisibleCaptcha_Block_Captcha extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('amasty/aminvisiblecaptcha/captcha.phtml');
    }

    public function isEnabled()
    {
        return (Mage::registry('need_amasty_captcha') && Mage::getModel('aminvisiblecaptcha/captcha')->isEnabled());
    }

    // support Magento older than 1.8.0.0
    public function quoteEscape($data, $addSlashes = false)
    {
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }
}
