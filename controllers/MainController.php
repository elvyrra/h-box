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
        $this->addKeysToJavaScript(
            $this->_plugin . '.close-file-confirmation',
            $this->_plugin . '.share-form-write-file-label',
            $this->_plugin . '.share-form-write-folder-label'
        );

        $rootElement = BoxElement::getRootElement();
        $allElements = $rootElement->getReadableElements();

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
                        'zip' => ZipEditor::display(),
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