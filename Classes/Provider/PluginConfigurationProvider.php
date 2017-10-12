<?php
namespace DMF\FluxGalleria\Provider;

/**
 * Class PluginConfigurationProvider
 *
 * @package DMF\FluxGalleria\Provider
 */
class PluginConfigurationProvider extends \FluidTYPO3\Flux\Provider\Provider {

    /**
     * @var string
     */
    protected $controllerName = 'Content';

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
    protected $templatePaths = array(
        'templateRootPaths.10' => 'EXT:flux_galleria/Resources/Private/Templates/',
        'partialRootPaths.10' => 'EXT:flux_galleria/Resources/Private/Partials/',
        'layoutRootPaths.10' => 'EXT:flux_galleria/Resources/Private/Layouts/',
    );

    /**
     * @var string
     */
    protected $fieldName = 'pi_flexform';

    /**
     * @var string
     */
    protected $tableName = 'tt_content';

    /**
     * @var string
     */
    protected $templatePathAndFilename = 'EXT:flux_galleria/Resources/Private/Templates/Galleria/Index.html';

}

?>
