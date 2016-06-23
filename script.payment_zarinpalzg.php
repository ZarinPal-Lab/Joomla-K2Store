<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class plgK2StorePayment_zarinpalzgInstallerScript
{

    function preflight($type, $parent)
    {

        $xmlfile = JPATH_ADMINISTRATOR . '/components/com_k2store/manifest.xml';
        jimport('joomla.installer.installer');
        $installer = new JInstaller;
        $data = $installer->parseXMLInstallFile($xmlfile);

        //check for minimum requirement
        // abort if the current K2Store release is older
        if (version_compare($data['version'], '2.6.1', 'lt')) {
            Jerror::raiseWarning(null, 'You are using an old version of K2Store. Please upgrade to the latest version');
            return false;
        }

    }

}