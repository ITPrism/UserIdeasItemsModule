<?php
/**
 * @package      Userideas
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Userideas.init');

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

JHtml::stylesheet('com_userideas/frontend.style.css', false, true, false);

JHtml::_('Prism.ui.pnotify');
JHtml::_('Prism.ui.joomlaHelper');
JHtml::_('Userideas.loadVoteScript', $params->get('counter_button', Prism\Constants::NO));

// Load library language
$lang = JFactory::getLanguage();
$lang->load('com_userideas', USERIDEAS_PATH_COMPONENT_SITE);

$limitResults = $params->get('items_limit', 5);
if ($limitResults <= 0) {
    $limitResults = 5;
}

$accessGroups  = \JFactory::getUser()->getAuthorisedViewLevels();
$resultOptions = array(
    'order_column' => 'title',
    'order_direction' => 'ASC',
    'limit' => $limitResults,
    'access_groups' => $accessGroups,
    'state' => Prism\Constants::PUBLISHED
);

switch ($params->get('results_type')) {
    case 'latest':
        $items           =  new Userideas\Statistic\Items\Latest(JFactory::getDbo());
        $items->load($resultOptions);
        break;

    case 'popular':
        $items           =  new Userideas\Statistic\Items\Popular(JFactory::getDbo());
        $items->load($resultOptions);
        break;

    case 'mostvoted':
        $items           =  new Userideas\Statistic\Items\MostVoted(JFactory::getDbo());
        $items->load($resultOptions);
        break;

    default: // none
        $itemsIds = $params->get('items_ids');
        $itemsIds = explode(',', $itemsIds);
        $itemsIds = Joomla\Utilities\ArrayHelper::toInteger($itemsIds);

        if (count($itemsIds) > 0) {
            $resultOptions['ids'] = $itemsIds;
        }

        $items = new Userideas\Item\Items(JFactory::getDbo());
        $items->load($resultOptions);
        break;
}

// Get component parameters
$componentParams = JComponentHelper::getParams('com_userideas');
/** @var  $componentParams Joomla\Registry\Registry */

$params->set('integration_name_type', $componentParams->get('integration_name_type'));
$params->set('integration_display_owner_avatar', $componentParams->get('integration_display_owner_avatar'));

$commentsEnabled  = $componentParams->get('comments_enabled', 1);
$numberOfComments = array();
if ($commentsEnabled and $params->get('show_button_comments', $componentParams->get('show_button_comments'))) {
    $ids = $items->getValues('id');
    $comments         = new Userideas\Comment\Comments(JFactory::getDbo());
    $numberOfComments = $comments->advancedCount(['items_ids' => $ids, 'state' => Prism\Constants::PUBLISHED]);
} else {
    $commentsEnabled = false;
}

$helpersOptions = array();

$helperBus     = new Prism\Helper\HelperBus($items);
$helperBus->addCommand(new Userideas\Helper\PrepareParamsHelper());
$helperBus->addCommand(new Userideas\Helper\PrepareStatusesHelper());
$helperBus->addCommand(new Userideas\Helper\PrepareAccessHelper(JFactory::getUser()));

// Set helper command that prepares tags.
if ($params->get('show_tags', $componentParams->get('show_tags'))) {
    $helpersOptions['content_type']  = 'com_userideas.item';
    $helpersOptions['access_groups'] = $accessGroups;

    $helperBus->addCommand(new Userideas\Helper\PrepareTagsHelper());
}

$helperBus->handle($helpersOptions);

$showAuthorFlag = 0;
foreach ($items as $item) {
    if ($item->params->get('show_author', $componentParams->get('show_author'))) {
        $showAuthorFlag = 1;
        break;
    }
}

// Get options
$displayAuthor      = (int)$params->get('show_author', $showAuthorFlag);
$titleLength        = (int)$params->get('title_length', $componentParams->get('title_length'));
$descriptionLength  = (int)$params->get('intro_length', $componentParams->get('intro_length'));

// Display user social profile (integrate).
$socialProfiles     = null;
$integrationOptions = array();
if ($displayAuthor === 1 and $componentParams->get('integration_social_platform')) {
    $usersIds = $items->getValues('user_id');
    $usersIds = Joomla\Utilities\ArrayHelper::toInteger($usersIds);
    $usersIds = array_filter(array_unique($usersIds));

    $integrationOptions = array(
        'size' => $componentParams->get('integration_avatars_size', 'small'),
        'default' => $componentParams->get('integration_avatars_default', '/media/com_userideas/images/no-profile.png')
    );

    $options = new \Joomla\Registry\Registry(array(
        'platform' => $componentParams->get('integration_social_platform'),
        'user_ids' => $usersIds
    ));

    $socialProfilesBuilder = new Prism\Integration\Profiles\Factory($options);
    $socialProfiles        = $socialProfilesBuilder->create();
}

require JModuleHelper::getLayoutPath('mod_userideasitems', $params->get('layout', 'default'));
