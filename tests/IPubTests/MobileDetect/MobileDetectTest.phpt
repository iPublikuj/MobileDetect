<?php
/**
 * Test: IPub\MobileDetect\MobileDetect
 * @testCase
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:MobileDetect!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           10.01.15
 */

declare(strict_types = 1);

namespace IPubTests\MobileDetect;

use Nette;

use Tester;
use Tester\Assert;

use IPub\MobileDetect;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

class MobileDetectTest extends Tester\TestCase
{
	/**
	 * @var MobileDetect\MobileDetect
	 */
	private $mobileDetector;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() : void
	{
		parent::setUp();

		$dic = $this->createContainer();

		// Get extension services
		$this->mobileDetector = $dic->getService('mobileDetect.mobileDetect');
	}

	public function testBasicMethods() : void
	{
		$this->mobileDetector->setHttpHeaders([
			'SERVER_SOFTWARE'       => 'Apache/2.2.15 (Linux) Whatever/4.0 PHP/5.2.13',
			'REQUEST_METHOD'        => 'POST',
			'HTTP_HOST'             => 'home.ghita.org',
			'HTTP_X_REAL_IP'        => '1.2.3.4',
			'HTTP_X_FORWARDED_FOR'  => '1.2.3.5',
			'HTTP_CONNECTION'       => 'close',
			'HTTP_USER_AGENT'       => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0_1 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A523 Safari/8536.25',
			'HTTP_ACCEPT'           => 'text/vnd.wap.wml, application/json, text/javascript, */*; q=0.01',
			'HTTP_ACCEPT_LANGUAGE'  => 'en-us,en;q=0.5',
			'HTTP_ACCEPT_ENCODING'  => 'gzip, deflate',
			'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
			'HTTP_REFERER'          => 'http://ipublikuj.eu',
			'HTTP_PRAGMA'           => 'no-cache',
			'HTTP_CACHE_CONTROL'    => 'no-cache',
			'REMOTE_ADDR'           => '11.22.33.44',
			'REQUEST_TIME'          => '01-10-2012 07:57'
		]);

		Assert::true($this->mobileDetector->isMobile());
		Assert::false($this->mobileDetector->isTablet());

		Assert::false($this->mobileDetector->isiphone());
		Assert::false($this->mobileDetector->isiOS());

		$this->mobileDetector->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 6_0_1 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A523 Safari/8536.25');

		Assert::true($this->mobileDetector->isiphone());
		Assert::true($this->mobileDetector->isiOS());
		Assert::true($this->mobileDetector->isios());
		Assert::true($this->mobileDetector->is('iphone'));
		Assert::true($this->mobileDetector->is('ios'));
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		MobileDetect\DI\MobileDetectExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		return $config->createContainer();
	}
}

\run(new MobileDetectTest());
