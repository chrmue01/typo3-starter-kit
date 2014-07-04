<?php
namespace TYPO3\CMS\Extensionmanager\Tests\Unit\Report;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Christian Kuhn <lolli@schwarzbu.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extensionmanager\Report;

/**
 * Test case
 */
class ExtensionStatusTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository
	 */
	protected $mockRepositoryRepository;

	/**
	 * @var \TYPO3\CMS\Lang\LanguageService
	 */
	protected $mockLanguageService;

	/**
	 * Set up
	 */
	public function setUp() {
		$this->mockObjectManager = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface');
		/** @var $mockRepositoryRepository \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository|\PHPUnit_Framework_MockObject_MockObject */
		$this->mockRepositoryRepository = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array(), array($this->mockObjectManager));
		$this->mockLanguageService = $this->getMock('TYPO3\\CMS\\Lang\\LanguageService', array(), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function extensionStatusImplementsStatusProviderInterface() {
		$reportMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array(), array(), '', FALSE);
		$this->assertInstanceOf('TYPO3\\CMS\\Reports\\StatusProviderInterface', $reportMock);
	}

	/**
	 * @test
	 */
	public function getStatusReturnsArray() {
		$report = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('getSecurityStatusOfExtensions', 'getMainRepositoryStatus'), array(), '', FALSE);
		$this->assertInternalType('array', $report->getStatus());
	}

	/**
	 * @test
	 */
	public function getStatusReturnArrayContainsFiveEntries() {
		$report = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('getSecurityStatusOfExtensions', 'getMainRepositoryStatus'), array(), '', FALSE);
		$this->assertSame(5, count($report->getStatus()));
	}

	/**
	 * @test
	 */
	public function getStatusReturnArrayContainsInstancesOfReportsStatusStatus() {
		$statusObject = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array('title', 'value'));
		/** @var \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus $report */
		$report = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('getSecurityStatusOfExtensions', 'getMainRepositoryStatus'), array(), '', FALSE);
		$report->expects($this->any())->method('getMainRepositoryStatus')->will($this->returnValue($statusObject));
		$resultStatuses = $report->getStatus();
		foreach($resultStatuses as $status) {
			if ($status) {
				$this->assertInstanceOf('TYPO3\\CMS\\Reports\\Status', $status);
			}
		}
	}

	/**
	 * @test
	 */
	public function getStatusCallsGetMainRepositoryStatusForMainRepositoryStatusResult() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(0));
		$mockExtensionList = array(
			'enetcache' => array(
				'installed' => TRUE,
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('getMainRepositoryStatus'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);
		$mockReport
			->expects($this->once())
			->method('getMainRepositoryStatus')
			->will($this->returnValue('foo'));

		$result = $mockReport->getStatus();
		$this->assertSame('foo', $result['mainRepositoryStatus']);
	}

	/**
	 * @test
	 */
	public function getMainRepositoryStatusReturnsErrorStatusIfRepositoryIsNotFound() {
		$this->mockRepositoryRepository
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue(NULL));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->once())
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::ERROR)
			->will($this->returnValue($statusMock));
		$mockReport->_set('repositoryRepository', $this->mockRepositoryRepository);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getMainRepositoryStatus');
		$this->assertSame($statusMock, $result);
	}

	/**
	 * @test
	 */
	public function getMainRepositoryStatusReturnsNoticeIfRepositoryUpdateIsLongerThanSevenDaysAgo() {
		/** @var $mockRepositoryRepository \TYPO3\CMS\Extensionmanager\Domain\Model\Repository|\PHPUnit_Framework_MockObject_MockObject */
		$mockRepository = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Repository');
		$mockRepository
			->expects($this->once())
			->method('getLastUpdate')
			->will($this->returnValue(new \DateTime('-8 days')));

		$this->mockRepositoryRepository
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue($mockRepository));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->once())
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::NOTICE)
			->will($this->returnValue($statusMock));
		$mockReport->_set('repositoryRepository', $this->mockRepositoryRepository);
		$mockReport->_set('languageService', $this->mockLanguageService);

		/** @var $result \TYPO3\CMS\Reports\Status */
		$result = $mockReport->_call('getMainRepositoryStatus');
		$this->assertSame($statusMock, $result);
	}

	/**
	 * @test
	 */
	public function getMainRepositoryStatusReturnsOkIfUpdatedLessThanSevenDaysAgo() {
		/** @var $mockRepositoryRepository \TYPO3\CMS\Extensionmanager\Domain\Model\Repository|\PHPUnit_Framework_MockObject_MockObject */
		$mockRepository = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Repository');
		$mockRepository
			->expects($this->once())
			->method('getLastUpdate')
			->will($this->returnValue(new \DateTime('-6 days')));

		$this->mockRepositoryRepository
			->expects($this->once())
			->method('findOneTypo3OrgRepository')
			->will($this->returnValue($mockRepository));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->once())
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::OK)
			->will($this->returnValue($statusMock));
		$mockReport->_set('repositoryRepository', $this->mockRepositoryRepository);
		$mockReport->_set('languageService', $this->mockLanguageService);

		/** @var $result \TYPO3\CMS\Reports\Status */
		$result = $mockReport->_call('getMainRepositoryStatus');
		$this->assertSame($statusMock, $result);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsOkForLoadedExtensionIfNoInsecureExtensionIsLoaded() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(0));
		$mockExtensionList = array(
			'enetcache' => array(
				'installed' => TRUE,
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(0))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::OK)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->loaded;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsErrorForLoadedExtensionIfInsecureExtensionIsLoaded() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(-1));
		$mockExtensionList = array(
			'enetcache' => array(
				'installed' => TRUE,
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(0))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::ERROR)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->loaded;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsOkForExistingExtensionIfNoInsecureExtensionExists() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(0));
		$mockExtensionList = array(
			'enetcache' => array(
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(1))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::OK)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->existing;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsErrorForExistingExtensionIfInsecureExtensionExists() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(-1));
		$mockExtensionList = array(
			'enetcache' => array(
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(1))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::WARNING)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->existing;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsOkForLoadedExtensionIfNoOutdatedExtensionIsLoaded() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(0));
		$mockExtensionList = array(
			'enetcache' => array(
				'installed' => TRUE,
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(2))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::OK)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->loadedoutdated;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsErrorForLoadedExtensionIfOutdatedExtensionIsLoaded() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(-2));
		$mockExtensionList = array(
			'enetcache' => array(
				'installed' => TRUE,
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(2))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::WARNING)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->loadedoutdated;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsOkForExistingExtensionIfNoOutdatedExtensionExists() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(0));
		$mockExtensionList = array(
			'enetcache' => array(
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(3))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::OK)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->existingoutdated;
		$this->assertSame($statusMock, $loadedResult);
	}

	/**
	 * @test
	 */
	public function getSecurityStatusOfExtensionsReturnsErrorForExistingExtensionIfOutdatedExtensionExists() {
		/** @var $mockTerObject \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject */
		$mockTerObject = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension');
		$mockTerObject
			->expects($this->any())
			->method('getVersion')
			->will($this->returnValue('1.0.6'));
		$mockTerObject
			->expects($this->atLeastOnce())
			->method('getReviewState')
			->will($this->returnValue(-2));
		$mockExtensionList = array(
			'enetcache' => array(
				'terObject' => $mockTerObject
			),
		);
		/** @var $mockListUtility \TYPO3\CMS\Extensionmanager\Utility\ListUtility|\PHPUnit_Framework_MockObject_MockObject */
		$mockListUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$mockListUtility
			->expects($this->once())
			->method('getAvailableAndInstalledExtensionsWithAdditionalInformation')
			->will($this->returnValue($mockExtensionList));

		/** @var $mockReport \TYPO3\CMS\Extensionmanager\Report\ExtensionStatus|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface */
		$mockReport = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Report\\ExtensionStatus', array('dummy'), array(), '', FALSE);
		$mockReport->_set('objectManager', $this->mockObjectManager);
		$statusMock = $this->getMock('TYPO3\\CMS\\Reports\\Status', array(), array(), '', FALSE);
		$this->mockObjectManager
			->expects($this->at(3))
			->method('get')
			->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), \TYPO3\CMS\Reports\Status::WARNING)
			->will($this->returnValue($statusMock));
		$mockReport->_set('listUtility', $mockListUtility);
		$mockReport->_set('languageService', $this->mockLanguageService);

		$result = $mockReport->_call('getSecurityStatusOfExtensions');
		/** @var $loadedResult \TYPO3\CMS\Reports\Status */
		$loadedResult = $result->existingoutdated;
		$this->assertSame($statusMock, $loadedResult);
	}
}
