<?php

defined('_JEXEC') or die;

use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\Event\Event;

/** @var Joomla\CMS\Document\HtmlDocument $this */

// Get a reference to the global WebAssetManager object.
$wa             = $this->getWebAssetManager();

// This will hold a cached copy of the FontAwesome dependency, before we try to remove it.
global $_workaround_FontAwesome_dep;
$_workaround_FontAwesome_dep = null;

// Have we already had to restore the FontAwesome dependency?
global $_workaround_FontAwesome_discovered;
$_workaround_FontAwesome_discovered = false;

/**
 * Custom event handler to conditionally restore the FontAwesome dependency as needed.
 *
 * @param   Event  $event  The event we are responding to
 *
 * @return  void
 */
$handleConditionalFontAwesome = function (Event $event) {
	global $_workaround_FontAwesome_dep;
	global $_workaround_FontAwesome_discovered;

	/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
	$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();

	// If we have already discovered FontAwesome as a hard dependency there is nothing more to do.
	if ($_workaround_FontAwesome_discovered)
	{
		return;
	}

	// Do we have FontAwesome as a dependency on any of our styles, OTHER than the Cassiopeia template?
	$hasFontAwesomeDependency = array_reduce(
		$wa->getAssets('style'),
		function (bool $carry, \Joomla\CMS\WebAsset\WebAssetItemInterface $asset) {
			return $carry
			       || (in_array('fontawesome', $asset->getDependencies())
			           && !str_starts_with($asset->getName(), 'template.cassiopeia.'));

		},
		false
	);

	// A dependency exists. Try to restore FontAwesome
	if ($hasFontAwesomeDependency)
	{
		// The FontAwesome dependency did not exist, or its URI is null.
		if (!$wa->assetExists('style', 'fontawesome') || $wa->getAsset('style', 'fontawesome')->getUri() === '')
		{
			// Make sure there is something to restore before attempting to restore it.
			if (empty($_workaround_FontAwesome_dep))
			{
				return;
			}

			$_workaround_FontAwesome_discovered = true;

			$wa->registerStyle($_workaround_FontAwesome_dep);
		}

		return;
	}

	// No dependency. Kill FontAwesome after keeping a copy of it.
	if ($wa->getAsset('style', 'fontawesome'))
	{
		$_workaround_FontAwesome_dep ??= clone $wa->getAsset('style', 'fontawesome');
	}

	$wa->registerStyle('fontawesome', '');
};

/**
 * Register Joomla! event handlers. Like a plugin, but kinkier.
 *
 * Here is the idea.
 *
 * When the template code is loaded Joomla! has already loaded most, if not all, plugins and the component rendering on
 * the page. It has not, however, rendered any modules. It is possible that modules themselves depend on FontAwesome.
 * Therefore, we cannot make a decision just yet as to whether FontAwesome is needed or not.
 *
 * We cannot use the onAfterRender event because by that time it's too late; Joomla will have already rendered the page
 * header which includes the styles.
 *
 * This is why we go with a more convoluted solution.
 *
 * We first trap onBeforeRender which is executed before the template is parsed for Joomla! module tags. This allows us
 * to evaluate whether the component and/or any plugins depend on FontAwesome.
 *
 * Then, we trap onAfterRenderModules which is called whenever there is a Joomla! tag loading module positions. This
 * allows us to evaluate whether any modules loaded during rendering depend on FontAwesome.
 *
 * However, we can also have Joomla tags to render individual modules. Hence, the third trap, for the
 * onAfterRenderModule event.
 */
$dispatcher = \Joomla\CMS\Factory::getApplication()->getDispatcher();
$dispatcher->addListener('onBeforeRender', $handleConditionalFontAwesome);
$dispatcher->addListener('onAfterRenderModules', $handleConditionalFontAwesome);
$dispatcher->addListener('onAfterRenderModule', $handleConditionalFontAwesome);

// Load the Cassiopeia index.php. If you have your own custom template, your code goes here.
require_once JPATH_THEMES . '/cassiopeia/index.php';
