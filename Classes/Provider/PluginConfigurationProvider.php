<?php
namespace DMF\FluxGalleria\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PluginConfigurationProvider
 *
 * @package DMF\Intranet\Provider\Configuration
 */
class PluginConfigurationProvider extends \Tx_Flux_Provider_AbstractPluginConfigurationProvider
	implements \Tx_Flux_Provider_PluginConfigurationProviderInterface {

	/**
	 * @var string
	 */
	protected $extensionKey = 'flux_galleria';

	/**
	 * @var string
	 */
	protected $listType = 'fluxgalleria_frontend';

	/**
	 * @var array
	 */
	protected $templateVariables = array();

	/**
	 * @var array
	 */
	protected $templatePaths = array(
		'templateRootPath' => 'EXT:flux_galleria/Resources/Private/Templates/',
		'partialRootPath'  => 'EXT:flux_galleria/Resources/Private/Partials/',
		'layoutRootPath'   => 'EXT:flux_galleria/Resources/Private/Layouts/',
	);

	/**
	 * @var string
	 */
	protected $configurationSectionName = 'Configuration';


	/**
	 * @var string
	 */
	protected $templatePathAndFilename = 'EXT:flux_galleria/Resources/Private/Templates/Galleria/Index.html';

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getTemplatePathAndFilename(array $row) {
		unset($row);
		$this->templatePathAndFilename = GeneralUtility::getFileAbsFileName($this->templatePathAndFilename);
		return $this->templatePathAndFilename;
	}
}

?>
