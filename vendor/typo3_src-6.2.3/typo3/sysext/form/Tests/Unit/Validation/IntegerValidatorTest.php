<?php
namespace TYPO3\CMS\Form\Tests\Unit\Validation;
/***************************************************************
*  Copyright notice
*
*  (c) 2012-2013 Andreas Lappe <a.lappe@kuehlhaus.com>, kuehlhaus AG
*
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

/**
 * Test case
 */
class IntegerValidatorTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\CMS\Form\Tests\Unit\Validation\Helper
	 */
	protected $helper;

	/**
	 * @var \TYPO3\CMS\Form\Validation\IntegerValidator
	 */
	protected $subject;

	public function setUp() {
		$this->helper = new \TYPO3\CMS\Form\Tests\Unit\Validation\Helper();
		$this->subject = $this->getMock('TYPO3\\CMS\\Form\\Validation\\IntegerValidator', array('dummy'), array(), '', FALSE);
	}

	public function validIntegerProvider() {
		return array(
			'12 for de locale'    => array(array(12, 'de')),
		);
	}

	public function invalidIntegerProvider() {
		return array(
			'12.1 for en_US locale' => array(array(12.1, 'en_US')),
			'12,1 for de_DE locale' => array(array('12,1', 'de_DE'))
		);
	}

	/**
	 * @test
	 * @dataProvider validIntegerProvider
	 */
	public function isValidForValidInputReturnsTrue($input) {
		$this->subject->setFieldName('myFile');
		setlocale(LC_NUMERIC, $input[1]);
		$requestHandlerMock = $this->helper->getRequestHandler(array(
			'myFile' => $input[0]
		));
		$this->subject->injectRequestHandler($requestHandlerMock);

		$this->assertTrue(
			$this->subject->isValid()
		);
	}

	/**
	 * @test
	 * @dataProvider invalidIntegerProvider
	 */
	public function isValidForInvalidInputReturnsFalse($input) {
		$this->subject->setFieldName('myFile');
		setlocale(LC_NUMERIC, $input[1]);
		$requestHandlerMock = $this->helper->getRequestHandler(array(
			'myFile' => $input[0]
		));
		$this->subject->injectRequestHandler($requestHandlerMock);

		$this->assertFalse(
			$this->subject->isValid()
		);
	}
}
