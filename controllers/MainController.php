<?php

namespace Hawk\Plugins\HBox;

class MainController extends Controller {

    /**
     * Display the main page of HBox. This page displays the list of all accessible folder in the left sidebar,
     * and in the page content, the content of the root folder '/' of the user
     * @returns string The HTML result
     */
    public function index() {
        $this->addCss($this->getPlugin()->getCssUrl('hbox.less'));
        $this->addJavaScript($this->getPlugin()->getjsUrl('hbox.js'));
        // $this->addJavaScript($this->getPlugin()->getjsUrl('hbox-vue.js'));
        $this->addKeysToJavaScript($this->_plugin . '.close-file-confirmation');

        // $this->addJavaScript('https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.26/vue.min.js');

        $allElements = array_filter(
            BoxElement::getAll(),
            function($element) {
                return $element->isReadable();
            }
        );
        array_unshift($allElements, BoxElement::getRootElement());

        $allElements = array_map(function($element) {
            return $element->formatForJavaScript();
        }, $allElements);

        return LeftSidebarTab::make(array(
            'tabId' => 'hbox-main-page',
            'title' => Lang::get($this->_plugin . '.main-page-title'),
            'icon' => $this->getPlugin()->getFaviconUrl(),
            'page' => array(
                'content' => View::make($this->getPlugin()->getView('hbox-page-content.tpl'), array(
                    'allElements' => htmlentities(json_encode($allElements), ENT_QUOTES),
                    'dialogForms' => array(
                        'createFolder' => FolderController::getInstance()->create(),
                        'uploadFile' => FileController::getInstance()->upload(),
                        'renameElement' => ElementController::getInstance()->rename(),
                        'deleteElement' => ElementController::getInstance()->delete(),
                        'moveElement' => ElementController::getInstance()->move(),
                        'shareElement' => ElementController::getInstance()->share()
                    ),
                    'editorTemplates' => array(
                        'image' => ImageEditor::display(),
                        'pdf' => PdfEditor::display(),
                        'ace' => AceEditor::display(),
                        'audio' => AudioEditor::display(),
                        'video' => VideoEditor::display(),
                        'office' => OfficeEditor::display(),
                    )
                )),
                'class' => 'col-md-9'
            ),
            'sidebar' => array(
                'widgets' => array(
                    HBoxTreeWidget::getInstance()
                ),
                'class' => 'col-md-3'
            )
        ));
    }
}