<?php
/**
 * EaDesgin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eadesign.ro so we can send you a copy immediately.
 *
 * @category    eadesigndev_pdfgenerator
 * @copyright   Copyright (c) 2008-2016 EaDesign by Eco Active S.R.L.
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Eadesigndev\Mobiledetect\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Url\Validator;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResponseFactory;

/**
 * Helper to be used for mobile detect and validations
 *
 * Class Validations
 * @package Eadesigndev\Detect\Helper
 */
class Redirect extends AbstractHelper
{

    const MOBILEDETECT_ENABLED = 'eadesign_mobiledetect/general/enabled';
    const MOBILEDETECT_MOBILE = 'eadesign_mobiledetect/general/eadesign_is_mobile';
    const MOBILEDETECT_TABLET = 'eadesign_mobiledetect/general/eadesign_is_tablet';
    const MOBILEDETECT_DESKTOP = 'eadesign_mobiledetect/general/eadesign_is_desktop';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param ResponseFactory $responseFactory
     * @param Validator $validator
     */
    public function __construct(
        Context $context,
        ResponseFactory $responseFactory,
        Validator $validator
    ) {
        $this->config = $context->getScopeConfig();
        $this->responseFactory = $responseFactory;
        $this->validator = $validator;
        parent::__construct($context);
    }

    /**
     * Get config value
     *
     * @param string $configPath
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->config->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if module is enable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return $this->getConfig(self::MOBILEDETECT_ENABLED);
    }

    /**
     * Redirect to tablet url
     */
    public function redirectTablet()
    {

        $tablet = $this->getConfig(self::MOBILEDETECT_TABLET);

        if ($this->validator->isValid($tablet)) {
            $this->responseFactory->create()->setRedirect($tablet)->sendResponse();
        }
    }

    /**
     * Redirect to mobile url
     */
    public function redirectMobile()
    {

        $mobile = $this->getConfig(self::MOBILEDETECT_MOBILE);

        if ($this->validator->isValid($mobile)) {
            $this->responseFactory->create()->setRedirect($mobile)->sendResponse();
        }
    }

    /**
     * Redirect to desktop url
     */
    public function redirectDesktop()
    {

        $desktop = $this->getConfig(self::MOBILEDETECT_TABLET);

        if ($this->validator->isValid($desktop)) {
            $this->responseFactory->create()->setRedirect($desktop)->sendResponse();
        }
    }
}
