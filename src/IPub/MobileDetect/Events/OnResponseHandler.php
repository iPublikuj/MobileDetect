<?php
/**
 * OnResponseHandler.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:MobileDetect!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           23.04.14
 */

declare(strict_types = 1);

namespace IPub\MobileDetect\Events;

use Nette\Application;

use IPub\MobileDetect\Helpers\DeviceView;

/**
 * On response event handler
 *
 * @package        iPublikuj:MobileDetect!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class OnResponseHandler
{
	/**
	 * @var bool
	 */
	private $needModifyResponse = false;

	/**
	 * @var DeviceView
	 */
	private $deviceView;

	/**
	 * @var \Closure
	 */
	public $modifyResponseClosure;

	/**
	 * @param DeviceView $deviceView
	 */
	public function __construct(DeviceView $deviceView)
	{
		$this->deviceView = $deviceView;
	}

	/**
	 * Stores information about modifying response
	 *
	 * @return void
	 */
	public function needModifyResponse() : void
	{
		$this->needModifyResponse = true;
	}

	/**
	 * @param Application\Application $application
	 *
	 * @return void
	 */
	public function __invoke(Application\Application $application) : void
	{
		if ($this->needModifyResponse && $this->modifyResponseClosure instanceof \Closure) {
			$modifyClosure = $this->modifyResponseClosure;
			$modifyClosure($this->deviceView);
		}
	}
}
