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
use Eadesigndev\Mobiledetect\Helper\MobileDetectModifier as MobileDetect;

/**
 * Helper to be used for mobile detect and validations
 *
 * Class Validations
 * @package Eadesigndev\Detect\Helper
 */
class Detect extends AbstractHelper
{

    const EA_MOBILE = 'eadesign_is_mobile';
    const EA_TABLET = 'eadesign_is_tablet';
    const EA_DESKTOP = 'eadesign_is_desktop';

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * @var bool
     */
    private $detected = false;

    /**
     * Validations constructor.
     * @param Context $context
     * @param MobileDetect $mobileDetect
     */
    public function __construct(Context $context, MobileDetect $mobileDetect)
    {
        $this->mobileDetect = $mobileDetect;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isDetected()
    {
        return $this->detected;
    }

    /**
     * If is mobile device
     * @return bool
     */
    public function isMobile()
    {
        $this->detected = self::EA_MOBILE;
        return $this->mobileDetect->isMobile();
    }

    /**
     * If is a tablet
     * @return bool
     */
    public function isTablet()
    {
        $this->detected = self::EA_TABLET;
        return $this->mobileDetect->isTablet();
    }

    /**
     * If is desktop device
     * @return bool
     */
    public function isDesktop()
    {
        if ($this->isMobile()) {
            return false;
        }
        $this->detected = self::EA_DESKTOP;
        return true;
    }

    /**
     * The mobile detect instance to be able to use all the functionality
     * @return MobileDetect
     */
    public function getMobileDetect()
    {
        return $this->mobileDetect;
    }
}
