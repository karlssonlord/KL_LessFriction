<?php
class KL_LessFriction_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config {
    public function testBlockAlias() {
        $this->assertBlockAlias('lessfriction/test', 'KL_LessFriction_Block_Test');
    }

    public function testModelAlias() {
        $this->assertModelAlias('lessfriction/test', 'KL_LessFriction_Model_Test');
    }

    public function testHelperAlias() {
        $this->assertHelperAlias('lessfriction/test', 'KL_LessFriction_Helper_Test');
    }

    public function testCodepool() {
        $this->assertModuleCodePool('community');
    }

    public function testDepends() {
        $this->assertModuleDepends('Mage_Checkout');
    }

    public function testLayoutFile() {
        $this->assertLayoutFileDefined('frontend', 'kl/lessfriction.xml');
        $this->assertLayoutFileExistsInTheme('frontend', 'kl/lessfriction.xml', 'default', 'base');
    }
}
