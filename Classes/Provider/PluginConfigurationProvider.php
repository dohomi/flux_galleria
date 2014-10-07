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
    protected $extensionKey = 'flux_galleria';

    /**
     * @var string
     */
    protected $listType = 'fluxgalleria_frontend';

    /**
     * @var array
     */
    protected $templatePaths = array(
        'templateRootPath' => 'EXT:flux_galleria/Resources/Private/Templates/',
        'partialRootPath' => 'EXT:flux_galleria/Resources/Private/Partials/',
        'layoutRootPath' => 'EXT:flux_galleria/Resources/Private/Layouts/',
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
