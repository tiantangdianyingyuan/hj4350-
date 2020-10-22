<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/14 13:52
 */
$components = [
    'app-attachment',
    'app-gallery',
    'app-picker',
    'app-pick-link',
    'app-banner',
    'app-image',
    'app-ellipsis',
    'app-map',
    'app-district',
    'app-upload',
    'app-export-dialog',
    'app-template',
    'app-image-upload',
    'app-form',
    'app-test',
    'input/app-input-number',
];
$html = "";
foreach ($components as $component) {
    $html .= $this->renderFile(__DIR__ . "/{$component}.php") . "\n";
}
echo $html;
