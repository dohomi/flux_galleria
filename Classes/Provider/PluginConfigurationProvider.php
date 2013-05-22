<?php
namespace DMF\FluxGalleria\Provider;

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
	public $extensionKey = 'flux_galleria';

	/**
	 * @var string
	 */
	public $listType = 'fluxgalleria_frontend';

	/**
	 * @var array
	 */
	public $templateVariables = array();

	/**
	 * @var array
	 */
	public $templatePaths = array(
		'templateRootPath' => 'EXT:flux_galleria/Resources/Private/Templates/',
		'partialRootPath' => 'EXT:flux_galleria/Resources/Private/Partials/',
		'layoutRootPath' => 'EXT:flux_galleria/Resources/Private/Layouts/',
	);

	/**
	 * @var string
	 */
	public $configurationSectionName = 'Configuration';



	/**
	 * @var string
	 */
	public $templatePathAndFilename = 'EXT:flux_galleria/Resources/Private/Templates/Galleria/Index.html';

}
?>
