<?php
/**
 * MobileDetectExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:MobileDetect!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		21.04.14
 */

namespace IPub\MobileDetect\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use IPub;

class MobileDetectExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	protected $defaults = array(
		'redirect'	=> array(
			'mobile'	=> array(
				'isEnabled'		=> FALSE,
				'host'			=> NULL,
				'statusCode'	=> 301,
				'action'		=> 'noRedirect',	// redirect/noRedirect/redirectWithoutPath
			),
			'tablet'	=> array(
				'isEnabled'		=> FALSE,
				'host'			=> NULL,
				'statusCode'	=> 301,
				'action'		=> 'noRedirect',	// redirect/noRedirect/redirectWithoutPath
			),
			'detectTabletAsMobile'	=> FALSE,
		),
		'switchDeviceView'	=> array(
			'saveRefererPath'	=> TRUE
		),
		'deviceViewCookie' => array(
			'name' => 'device_view',
			'domain' => NULL,
			'expirationAfter' => '+1 month',
			'path' => '/',
			'secure' => FALSE,
			'httpOnly' => TRUE,
		),
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		// Install mobile detect service
		$builder->addDefinition($this->prefix('mobileDetect'))
			->setClass('IPub\MobileDetect\MobileDetect');

		$builder->addDefinition($this->prefix('deviceView'))
			->setClass('IPub\MobileDetect\Helpers\DeviceView')
			->addSetup('setCookieConfiguration', array($config['deviceViewCookie']))
			->addSetup('detectViewType');
		
		$builder->addDefinition($this->prefix('onRequestHandler'))
			->setClass('IPub\MobileDetect\Events\OnRequestHandler')
			->addSetup('$redirectConf', array($config['redirect']))
			->addSetup('$isFullPath', array($config['switchDeviceView']['saveRefererPath']));

		$builder->addDefinition($this->prefix('onResponseHandler'))
			->setClass('IPub\MobileDetect\Events\OnResponseHandler');

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass('IPub\MobileDetect\Templating\Helpers')
			->setFactory($this->prefix('@mobileDetect') . '::createTemplateHelpers')
			->setInject(FALSE);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onRequest[] = ?', array('@' . $this->prefix('onRequestHandler')));
		$application->addSetup('$service->onResponse[] = ?', array('@' . $this->prefix('onResponseHandler')));
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType('\Nette\Bridges\ApplicationLatte\ILatteFactory') ?: 'nette.latteFactory');
		
		$latteFactory
			->addSetup('IPub\MobileDetect\Latte\Macros::install(?->getCompiler())', array('@self'))
			->addSetup('addFilter', array('getMobileDetectService', array($this->prefix('@helpers'), 'getMobileDetectService')))
			->addSetup('addFilter', array('getDeviceViewService', array($this->prefix('@helpers'), 'getDeviceViewService')));
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'mobileDetect')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new MobileDetectExtension());
		};
	}
}
