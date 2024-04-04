<?php

namespace Dionysopoulos\Module\Awesome\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\WebAsset\WebAssetManager;

class Dispatcher extends AbstractModuleDispatcher
{
	protected function getLayoutData()
	{
		/** @var WebAssetManager $wa */
		$wa = $this->getApplication()->getDocument()->getWebAssetManager();
		//$wa->getRegistry()->addRegistryFile(JPATH_SITE . '/media/mod_awesome/joomla.asset.json');
		$wa->getRegistry()->addExtensionRegistryFile('mod_awesome');
		$wa->useStyle('mod_awesome.frontend');

		return parent::getLayoutData();
	}

}