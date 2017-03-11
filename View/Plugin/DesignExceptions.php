<?php
/**
 * EaDesign
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
 * @category    eadesigndev_warehouses
 * @copyright   Copyright (c) 2008-2016 EaDesign by Eco Active S.R.L.
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Eadesigndev\Mobiledetect\View\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\DesignExceptions as InitialDesignExceptions;
use Eadesigndev\Mobiledetect\Helper\Detect;
use Eadesigndev\Mobiledetect\Helper\Redirect;

class DesignExceptions extends InitialDesignExceptions
{
    /**
     * @var Detect
     */
    private $detect;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * DesignExceptions constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param string $exceptionConfigPath
     * @param string $scopeType
     * @param Detect $detect
     * @param Redirect $redirect
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $exceptionConfigPath,
        $scopeType,
        Detect $detect,
        Redirect $redirect
    ) {
        parent::__construct($scopeConfig, $exceptionConfigPath, $scopeType);
        $this->detect = $detect;
        $this->redirect = $redirect;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param HttpRequest $request
     * @return bool|string
     * @SuppressWarnings("unused")
     */
    public function aroundGetThemeByRequest($subject, callable $proceed, HttpRequest $request)
    {
        $rules = $proceed($request);

        $userAgent = $request->getServer('HTTP_USER_AGENT');

        if (empty($userAgent)) {
            return $rules;
        }

        $this->userAgent = $userAgent;

        if (!$this->redirect->isEnable()) {
            return $rules;
        }

        $exception = $this->ifThemeChange();

        if (!$exception) {
            return $rules;
        }

        $expressions = $this->scopeConfig->getValue(
            $this->exceptionConfigPath,
            $this->scopeType
        );

        if (!$expressions) {
            return $rules;
        }

        $expressions = unserialize($expressions);

        foreach ($expressions as $rule) {
            if (preg_match($rule['regexp'], $exception)) {
                return $rule['value'];
            }
        }

        return $rules;
    }

    /**
     * The tablet is overwritten by the mobile
     *
     * @return bool
     */
    private function ifThemeChange()
    {
        if ($this->detect->isTablet()) {
            $this->redirect->redirectTablet();
        }

        if ($this->detect->isMobile()) {
            $this->redirect->redirectMobile();
        }

        if ($this->detect->isDesktop()) {
            $this->redirect->redirectDesktop();
        }

        $exception = $this->detect->isDetected();

        return $exception;
    }
}
