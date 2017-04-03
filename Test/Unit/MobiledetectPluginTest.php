<?php

namespace Eadesigndev\Mobiledetect\Test\Unit;

use Eadesigndev\Mobiledetect\Helper\Detect;
use Eadesigndev\Mobiledetect\Helper\Redirect;
use Eadesigndev\Mobiledetect\View\Plugin\DesignExceptions;
use Eadesigndev\Mobiledetect\Helper\MobileDetectModifier;
use Magento\Framework\View\DesignExceptions as InitialDesignExceptions;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Unserialize\Unserialize;

/**
 * Copyright © 2017 EaDesign by Eco Active S.R.L. All rights reserved.
 * See LICENSE for license details.
 */
class MobiledetectPluginTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var /Magento\Framework\App\ObjectManage;
     */
    public $objectManager;

    /**
     * @var  \Eadesigndev\Mobiledetect\Helper\Detect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $detectHelper;

    /**
     * @var  \Eadesigndev\Mobiledetect\Helper\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectHelper;

    /**
     * @var \Eadesigndev\Mobiledetect\View\Plugin\DesignExceptions
     */
    private $designExceptions;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigInterface;

    /**
     * @var string
     */
    private $exceptionConfigPath = 'exception_path';

    /**
     * @var string
     */
    private $scopeType = 'scope_type';

    /**
     * @var $subject \Magento\Framework\View\DesignExceptions
     */
    private $subject;

    /**
     * Setup the testing environment
     * @SuppressWarnings("StaticAccess")
     */
    public function setup()
    {

        $this->objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->detectHelper = $this->getMockBuilder(Detect::class)
            ->setMethods(['getMobileDetect', 'isMobile', 'isTablet','isDetected'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectHelper = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->objectManager->getObject(InitialDesignExceptions::class);

        $unSerialize = new Unserialize();

        $this->designExceptions = new DesignExceptions(
            $this->scopeConfigInterface,
            $this->exceptionConfigPath,
            $this->scopeType,
            $this->detectHelper,
            $this->redirectHelper,
            $unSerialize
        );
    }

    /**
     * @param string $userAgent
     * @param bool $useConfig
     * @param bool|string $result
     * @param array $expressions
     * @dataProvider getThemeByRequestDataProvider
     */
    public function testIfTheModuleIsNotEnabledStandardSystem($userAgent, $useConfig, $result, $expressions = [])
    {
        $this->redirectHelper
            ->method('isEnable')
            ->willReturn(false);

        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with($this->equalTo('HTTP_USER_AGENT'))
            ->will($this->returnValue($userAgent));

        if ($useConfig) {
            $this->scopeConfigInterface->expects($this->any())
                ->method('getValue')
                ->with($this->equalTo($this->exceptionConfigPath), $this->equalTo($this->scopeType))
                ->will($this->returnValue(serialize($expressions)));
        }

        $subject = $this->subject;

        $proceed = function () use ($result) {
            return $result;
        };

        $this->assertSame(
            $result,
            $this->designExceptions->aroundGetThemeByRequest($subject, $proceed, $this->requestMock)
        );
    }

    /**
     * @return array
     */
    public function getThemeByRequestDataProvider()
    {
        return [
            [false, false, false],
            ['iphone', false, false],
            ['iphone', true, false],
            ['iphone', true, 'matched', [['regexp' => '/iphone/', 'value' => 'matched']]],
            ['explorer', true, false, [['regexp' => '/iphone/', 'value' => 'matched']]],
        ];
    }

    /**
     * @param $userAgent
     * @param $case
     * @param $ifMobileString
     * @param $useConfig
     * @param $result
     * @param array $expressions
     * @dataProvider getIfModuleIfMobileException
     */
    public function testIfModuleIfMobileException(
        $userAgent,
        $case,
        $ifMobileString,
        $useConfig,
        $result,
        $expressions = []
    ) {

        $this->redirectHelper
            ->method('isEnable')
            ->willReturn(true);

        $this->requestMock->method('getServer')
            ->with($this->equalTo('HTTP_USER_AGENT'))
            ->will($this->returnValue($userAgent));

        $mobileDetectModifier = $this->getMockBuilder(MobileDetectModifier::class)
            ->setMethods(['setHttpHeaders','setUserAgent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->detectHelper->method('getMobileDetect')
            ->will($this->returnValue($mobileDetectModifier));

        $this->detectHelper->method('is' . $case)
            ->willReturn(true);

        $this->detectHelper->expects($this->once())
            ->method('isDetected')
            ->willReturn($ifMobileString);

        if ($useConfig) {
            $this->scopeConfigInterface
                ->method('getValue')
                ->with($this->equalTo($this->exceptionConfigPath), $this->equalTo($this->scopeType))
                ->will($this->returnValue(serialize($expressions)));
        }

        $subject = $this->subject;

        $proceed = function () use ($result) {
            return $result;
        };

        $this->assertSame(
            $result,
            $this->designExceptions->aroundGetThemeByRequest($subject, $proceed, $this->requestMock)
        );
    }

    /**
     * @return array
     */
    public function getIfModuleIfMobileException()
    {
        return [
            ['iphone', 'Mobile', Detect::EA_MOBILE, false, false],
            ['iphone', 'Mobile', Detect::EA_MOBILE, true, false],
            ['iphone', 'Tablet', Detect::EA_TABLET, false, false],
            ['iphone', 'Tablet', Detect::EA_TABLET, true, false],
            ['iphone', 'Desktop', Detect::EA_DESKTOP, false, false],
            ['iphone', 'Desktop', Detect::EA_DESKTOP, true, false],
            ['iphone', 'Mobile', Detect::EA_MOBILE, true, 'matched',
                [['regexp' => '/' . Detect::EA_MOBILE . '/', 'value' => 'matched']]
            ],
            ['explorer', 'Tablet', Detect::EA_TABLET, true, 'matched',
                [['regexp' => '/' . Detect::EA_TABLET . '/', 'value' => 'matched']]
            ],
            ['explorer', 'Desktop', Detect::EA_DESKTOP, true, 'matched',
                [['regexp' => '/' . Detect::EA_DESKTOP . '/', 'value' => 'matched']]
            ],
        ];
    }
}
