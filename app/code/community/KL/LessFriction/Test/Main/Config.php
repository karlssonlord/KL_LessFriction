<?php
/**
 * Configuration tests
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Test_Config_Main
    extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * Assert block alias
     *
     * @return void
     */
    public function testBlockAlias()
    {
        $this->assertBlockAlias(
            'lessfriction/test',
            'KL_LessFriction_Block_Test'
        );
    }

    /**
     * Assert model alias
     *
     * @return void
     */
    public function testModelAlias()
    {
        $this->assertModelAlias(
            'lessfriction/test',
            'KL_LessFriction_Model_Test'
        );
    }

    /**
     * Assert helper alias
     *
     * @return void
     */
    public function testHelperAlias()
    {
        $this->assertHelperAlias(
            'lessfriction/test',
            'KL_LessFriction_Helper_Test'
        );
    }

    /**
     * Assert code pool
     *
     * @return void
     */
    public function testCodepool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * Assert module dependencies
     *
     * @return void
     */
    public function testDepends()
    {
        $this->assertModuleDepends('Mage_Checkout');
    }

    /**
     * Assert layout files
     *
     * @return void
     */
    public function testLayoutFile()
    {
        $this->assertLayoutFileDefined(
            'frontend',
            'kl/lessfriction.xml'
        );
        $this->assertLayoutFileExistsInTheme(
            'frontend',
            'kl/lessfriction.xml',
            'default',
            'base'
        );
    }
}
