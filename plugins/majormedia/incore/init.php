<?php


/**
 * Hide Some Main Menu
 */
Event::listen('backend.menu.extendItems', function ($manager) {
    $manager->removeMainMenuItem('MajorMedia.ToolBox', 'main-menu-newsletters');
    // $manager->removeMainMenuItem('MajorMedia.ToolBox', 'main-menu-messages');
    $manager->removeMainMenuItem('MajorMedia.ToolBox', 'main-menu-dictionary');
});