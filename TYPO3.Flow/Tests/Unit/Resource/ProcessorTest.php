<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Resource;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the resource processor
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ProcessorTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function canAdjustRelativePathsInHTML() {
		$originalHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<base href="###BASEURI###" />
		<style type="text/css">
			.F3_WidgetLibrary_Widgets_FloatingWindow {
				background-image: url(StandardView_FloatingWindow.png);
			}
		</style>
		<link rel="stylesheet" href="SomeCoolStyle.css" />
	</head>
	<body>
		<img src="StandardView_Package.png" class="StandardView_Package" />
		<a href="http://test.invalid/">do not change this link</a>
		<a href="/an/absolute/URL/">nor this link</a>
		<a href="#samePage">nor that link</a>
	</body>
</html>';
		$expectedHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<base href="###BASEURI###" />
		<style type="text/css">
			.F3_WidgetLibrary_Widgets_FloatingWindow {
				background-image: url(test/prefix/to/insert/StandardView_FloatingWindow.png);
			}
		</style>
		<link rel="stylesheet" href="test/prefix/to/insert/SomeCoolStyle.css" />
	</head>
	<body>
		<img src="test/prefix/to/insert/StandardView_Package.png" class="StandardView_Package" />
		<a href="http://test.invalid/">do not change this link</a>
		<a href="/an/absolute/URL/">nor this link</a>
		<a href="#samePage">nor that link</a>
	</body>
</html>';
		$processor = new \F3\FLOW3\Resource\Processor();
		$processedHTML = $processor->prefixRelativePathsInHTML($originalHTML, 'test/prefix/to/insert/');
		$this->assertEquals($processedHTML, $expectedHTML, 'The processed HTML was not changed as expected.');
	}
}

?>