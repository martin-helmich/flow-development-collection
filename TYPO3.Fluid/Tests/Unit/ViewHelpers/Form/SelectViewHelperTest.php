<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Persistence\PersistenceManagerInterface;

require_once(__DIR__ . '/Fixtures/EmptySyntaxTreeNode.php');
require_once(__DIR__ . '/Fixtures/Fixture_UserDomainClass.php');
require_once(__DIR__ . '/FormFieldViewHelperBaseTestcase.php');

/**
 * Test for the "Select" Form view helper
 */
class SelectViewHelperTest extends \TYPO3\Fluid\Tests\Unit\ViewHelpers\Form\FormFieldViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Form\SelectViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->arguments['name'] = '';
		$this->arguments['sortByOptionLabel'] = FALSE;
		$this->viewHelper = $this->getAccessibleMock(\TYPO3\Fluid\ViewHelpers\Form\SelectViewHelper::class, array('setErrorClassAttribute', 'registerFieldNameForFormTokenGeneration'));
	}

	/**
	 * @test
	 */
	public function selectCorrectlySetsTagName() {
		$this->tagBuilder->expects($this->once())->method('setTagName')->with('select');

		$this->arguments['options'] = array();
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function selectCreatesExpectedOptions() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2'
		);
		$this->arguments['value'] = 'value2';
		$this->arguments['name'] = 'myName';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function anEmptyOptionTagIsRenderedIfOptionsArrayIsEmptyToAssureXhtmlCompatibility() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value=""></option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$this->arguments['options'] = array();
		$this->arguments['value'] = 'value2';
		$this->arguments['name'] = 'myName';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function orderOfOptionsIsNotAlteredByDefault() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="value3">label3</option>' . chr(10) . '<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$this->arguments['options'] = array(
			'value3' => 'label3',
			'value1' => 'label1',
			'value2' => 'label2'
		);

		$this->arguments['value'] = 'value2';
		$this->arguments['name'] = 'myName';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function optionsAreSortedByLabelIfSortByOptionLabelIsSet() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10) . '<option value="value3">label3</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$this->arguments['options'] = array(
			'value3' => 'label3',
			'value1' => 'label1',
			'value2' => 'label2'
		);

		$this->arguments['value'] = 'value2';
		$this->arguments['name'] = 'myName';
		$this->arguments['sortByOptionLabel'] = TRUE;

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function multipleSelectCreatesExpectedOptions() {
		$this->tagBuilder = new \TYPO3\Fluid\Core\ViewHelper\TagBuilder();

		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);

		$this->arguments['value'] = array('value3', 'value1');
		$this->arguments['name'] = 'myName';
		$this->arguments['multiple'] = 'multiple';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
		$result = $this->viewHelper->render();
		$expected = '<select multiple="multiple" name="myName[]"><option value="value1" selected="selected">label1</option>' . chr(10) .
			'<option value="value2">label2</option>' . chr(10) .
			'<option value="value3" selected="selected">label3</option>' . chr(10) .
			'</select>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @test
	 */
	public function multipleSelectCreatesExpectedOptionsInObjectAccessorMode() {
		$this->tagBuilder = new \TYPO3\Fluid\Core\ViewHelper\TagBuilder();

		$user = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Sebastian', 'Düvel');

		$this->viewHelperVariableContainerData = array(
			\TYPO3\Fluid\ViewHelpers\FormViewHelper::class => array(
				'formObjectName' => 'someFormObjectName',
				'formObject' => $user,
			)
		);

		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);
		$this->arguments['property'] = 'interests';
		$this->arguments['multiple'] = 'multiple';
		$this->arguments['selectAllByDefault'] = NULL;

		/** @var PersistenceManagerInterface|\PHPUnit_Framework_MockObject_MockObject $mockPersistenceManager */
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->with($user->getInterests())->will($this->returnValue(NULL));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
		$result = $this->viewHelper->render();
		$expected = '<select multiple="multiple" name="someFormObjectName[interests][]"><option value="value1" selected="selected">label1</option>' . chr(10) .
			'<option value="value2">label2</option>' . chr(10) .
			'<option value="value3" selected="selected">label3</option>' . chr(10) .
			'</select>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @test
	 */
	public function selectOnDomainObjectsCreatesExpectedOptions() {
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(2));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName[__identity]');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName[__identity]');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="1">Ingmar</option>' . chr(10) . '<option value="2" selected="selected">Sebastian</option>' . chr(10) . '<option value="3">Robert</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$user_is = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke');

		$this->arguments['options'] = array(
			$user_is,
			$user_sk,
			$user_rl
		);

		$this->arguments['value'] = $user_sk;
		$this->arguments['optionValueField'] = 'id';
		$this->arguments['optionLabelField'] = 'firstName';
		$this->arguments['name'] = 'myName';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function multipleSelectOnDomainObjectsCreatesExpectedOptions() {
		$this->tagBuilder = new \TYPO3\Fluid\Core\ViewHelper\TagBuilder();
		$this->viewHelper->expects($this->exactly(3))->method('registerFieldNameForFormTokenGeneration')->with('myName[]');

		$user_is = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke');

		$this->arguments['options'] = array(
			$user_is,
			$user_sk,
			$user_rl
		);
		$this->arguments['value'] = array($user_rl, $user_is);
		$this->arguments['optionValueField'] = 'id';
		$this->arguments['optionLabelField'] = 'lastName';
		$this->arguments['name'] = 'myName';
		$this->arguments['multiple'] = 'multiple';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
		$actual = $this->viewHelper->render();

		$expected = '<select multiple="multiple" name="myName[]"><option value="1" selected="selected">Schlecht</option>' . chr(10) .
			'<option value="2">Kurfuerst</option>' . chr(10) .
			'<option value="3" selected="selected">Lemke</option>' . chr(10) .
			'</select>';
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function multipleSelectOnDomainObjectsCreatesExpectedOptionsWithoutOptionValueField() {
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnCallback(
			function ($object) {
				return $object->getId();
			}
		));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$this->tagBuilder = new \TYPO3\Fluid\Core\ViewHelper\TagBuilder();
		$this->viewHelper->expects($this->exactly(3))->method('registerFieldNameForFormTokenGeneration')->with('myName[]');

		$user_is = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke');

		$this->arguments['options'] = array($user_is,$user_sk,$user_rl);
		$this->arguments['value'] = array($user_rl, $user_is);
		$this->arguments['optionLabelField'] = 'lastName';
		$this->arguments['name'] = 'myName';
		$this->arguments['multiple'] = 'multiple';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
		$actual = $this->viewHelper->render();

		$expected = '<select multiple="multiple" name="myName[]">' .
			'<option value="1" selected="selected">Schlecht</option>' . chr(10) .
			'<option value="2">Kurfuerst</option>' . chr(10) .
			'<option value="3" selected="selected">Lemke</option>' . chr(10) .
			'</select>';
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesUuidForValueAndLabel() {
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">fakeUUID</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$user = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');

		$this->arguments['options'] = array(
			$user
		);
		$this->arguments['name'] = 'myName';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesToStringForLabelIfAvailable() {
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">toStringResult</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');

		$user = $this->getMock(\TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass::class, array('__toString'), array(1, 'Ingmar', 'Schlecht'));
		$user->expects($this->atLeastOnce())->method('__toString')->will($this->returnValue('toStringResult'));

		$this->arguments['options'] = array(
			$user
		);
		$this->arguments['name'] = 'myName';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function selectOnDomainObjectsThrowsExceptionIfNoValueCanBeFound() {
		$mockPersistenceManager = $this->getMock(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class);
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$user = new \TYPO3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');

		$this->arguments['options'] = array(
			$user
		);
		$this->arguments['name'] = 'myName';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCallsSetErrorClassAttribute() {
		$this->arguments['options'] = array();

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->expects($this->once())->method('setErrorClassAttribute');
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function allOptionsAreSelectedIfSelectAllIsTrue() {
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="value1" selected="selected">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10) . '<option value="value3" selected="selected">label3</option>' . chr(10));

		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);
		$this->arguments['name'] = 'myName';
		$this->arguments['multiple'] = 'multiple';
		$this->arguments['selectAllByDefault'] = TRUE;

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function selectAllHasNoEffectIfValueIsSet() {
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="value1" selected="selected">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10) . '<option value="value3">label3</option>' . chr(10));

		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);
		$this->arguments['value'] = array('value2', 'value1');
		$this->arguments['name'] = 'myName';
		$this->arguments['multiple'] = 'multiple';
		$this->arguments['selectAllByDefault'] = TRUE;

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function translateLabelIsCalledIfTranslateArgumentIsGiven() {
		$this->arguments['options'] = array('foo' => 'bar');
		$this->arguments['translate'] = array('by' => 'id');
		$viewHelper = $this->getAccessibleMock(\TYPO3\Fluid\ViewHelpers\Form\SelectViewHelper::class, array('getTranslatedLabel', 'setErrorClassAttribute', 'registerFieldNameForFormTokenGeneration'));
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->expects($this->once())->method('getTranslatedLabel')->with('foo', 'bar');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function translateByIdAskForTranslationOfValueById() {
		$this->arguments['translate'] = array('by' => 'id');
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->once())->method('translateById')->with('value1', array(), NULL, NULL, 'Main', '');
		$this->viewHelper->_set('translator', $mockTranslator);
		$this->viewHelper->_call('getTranslatedLabel', 'value1', 'label1');
	}

	/**
	 * @test
	 */
	public function translateByLabelAskForTranslationOfLabelByLabel() {
		$this->arguments['translate'] = array('by' => 'label');
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->once())->method('translateByOriginalLabel')->with('label1', array(), NULL, NULL, 'Main', '');
		$this->viewHelper->_set('translator', $mockTranslator);
		$this->viewHelper->_call('getTranslatedLabel', 'value1', 'label1');
	}

	/**
	 * @test
	 */
	public function translateByLabelUsingValueUsesValue() {
		$this->arguments['translate'] = array('by' => 'label', 'using' => 'value');
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->once())->method('translateByOriginalLabel')->with('value1', array(), NULL, NULL, 'Main', '');
		$this->viewHelper->_set('translator', $mockTranslator);
		$this->viewHelper->_call('getTranslatedLabel', 'value1', 'label1');
	}

	/**
	 * @test
	 */
	public function translateByIdUsingLabelUsesLabel() {
		$this->arguments['translate'] = array('by' => 'id', 'using' => 'label');
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->once())->method('translateById')->with('label1', array(), NULL, NULL, 'Main', '');
		$this->viewHelper->_set('translator', $mockTranslator);
		$this->viewHelper->_call('getTranslatedLabel', 'value1', 'label1');
	}

	/**
	 * @test
	 */
	public function translateOptionsAreObserved() {
		$this->arguments['translate'] = array('by' => 'id', 'using' => 'label', 'locale' => 'dk', 'source' => 'WeirdMessageCatalog', 'package' => 'Foo.Bar', 'prefix' => 'somePrefix.');
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->once())->method('translateById')->with('somePrefix.label1', array(), NULL, new \TYPO3\Flow\I18n\Locale('dk'), 'WeirdMessageCatalog', 'Foo.Bar');
		$this->viewHelper->_set('translator', $mockTranslator);
		$this->viewHelper->_call('getTranslatedLabel', 'value1', 'label1');
	}

	/**
	 * @test
	 */
	public function optionsContainPrependedItemWithEmptyValueIfPrependOptionLabelIsSet() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="">please choose</option>' . chr(10) . '<option value="value1">label1</option>' . chr(10) . '<option value="value2">label2</option>' . chr(10) . '<option value="value3">label3</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');
		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);
		$this->arguments['name'] = 'myName';
		$this->arguments['prependOptionLabel'] = 'please choose';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function optionsContainPrependedItemWithCorrectValueIfPrependOptionLabelAndPrependOptionValueAreSet() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="-1">please choose</option>' . chr(10) . '<option value="value1">label1</option>' . chr(10) . '<option value="value2">label2</option>' . chr(10) . '<option value="value3">label3</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');
		$this->arguments['options'] = array(
			'value1' => 'label1',
			'value2' => 'label2',
			'value3' => 'label3'
		);
		$this->arguments['name'] = 'myName';
		$this->arguments['prependOptionLabel'] = 'please choose';
		$this->arguments['prependOptionValue'] = '-1';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function prependedOptionLabelIsTranslatedIfTranslateArgumentIsSet() {
		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$this->tagBuilder->expects($this->once())->method('setContent')->with('<option value="">translated label</option>' . chr(10));
		$this->tagBuilder->expects($this->once())->method('render');
		$this->arguments['options'] = array();
		$this->arguments['name'] = 'myName';
		$this->arguments['prependOptionLabel'] = 'select';
		$this->arguments['translate'] = array('by' => 'id', 'using' => 'label');

		$mockTranslator = $this->getMock(\TYPO3\Flow\I18n\Translator::class);
		$mockTranslator->expects($this->at(0))->method('translateById')->with('select', array(), NULL, NULL, 'Main', '')->will($this->returnValue('translated label'));
		$this->viewHelper->_set('translator', $mockTranslator);

		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}
}
