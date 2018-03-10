<?php
/**
 * @package      Userideas
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>
<div class="ui-moditems<?php echo $moduleclassSfx; ?>">
<?php
foreach ($items as $item) {
    $commentsNumber = 0;
    if (array_key_exists($item->id, $numberOfComments)) {
        $commentsNumber = (int)$numberOfComments[$item->id];
    }
    ?>
        <div class="media ui-item">
            <div class="ui-vote pull-left">
                <div class="ui-vote-counter js-ui-item-counter" id="js-ui-mod-vote-counter-<?php echo $item->id; ?>" data-id="<?php echo $item->id; ?>"><?php echo $item->votes; ?></div>
                <a class="btn btn-primary ui-btn-vote js-ui-btn-vote" href="javascript: void(0);" data-id="<?php echo $item->id; ?>"><?php echo JText::_('COM_USERIDEAS_VOTE'); ?></a>
            </div>
            <div class="media-body">
                <?php if ($params->get('show_title', $item->params->get('show_title', $componentParams->get('show_title')))) {?>
                    <h4 class="media-heading">
                        <a href="<?php echo JRoute::_(UserideasHelperRoute::getDetailsRoute($item->slug, $item->catid));?>" >
                        <?php echo JHtmlString::truncate(htmlspecialchars($item->title), $params->get('title_length', $componentParams->get('title_length')), true);?>
                        </a>
                    </h4>
                <?php } ?>

                <?php if ($params->get('show_intro', $item->params->get('show_intro', $componentParams->get('show_intro')))) { ?>
                    <?php echo JHtmlString::truncate($item->description, $params->get('intro_length', $componentParams->get('intro_length')), true, $params->get('allow_html', $componentParams->get('allow_html')));?>
                <?php } ?>
            </div>
            <?php
            if (UserideasHelper::shouldDisplayFootbar($params, $item->params, false) or $commentsEnabled) {
                echo '<div class="clearfix"></div>';

                $layoutData = new stdClass;

                $layoutData->item                = $item;
                $layoutData->socialProfiles      = $socialProfiles;
                $layoutData->integrationOptions  = $integrationOptions;
                $layoutData->commentsEnabled     = $commentsEnabled;
                $layoutData->params              = $params;
                $layoutData->commentsNumber      = $commentsNumber;

                $layoutsBasePath = JPath::clean(JPATH_BASE.'/components/com_userideas/layouts');

                $layout      = new JLayoutFile('footbar', $layoutsBasePath);
                echo $layout->render($layoutData, $layoutsBasePath);
            }?>
        </div>
<?php }?>
</div>
