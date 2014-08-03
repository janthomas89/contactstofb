<?php

namespace OCA\ContactsToFb\Controller;

use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\IRequest;
use \OCP\IURLGenerator;
use \OCA\ContactsToFb\Lib\SettingsService;
use \OCA\ContactsToFb\Lib\SyncService;

/**
 * Page controller of the application.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class PageController extends Controller
{
    /**
     * @var IURLGenerator
     */
    protected $urlGenerator;

    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * @var SyncService
     */
    protected $syncService;

    /**
     * Constructor of PageController.
     *
     * @param type $appName
     * @param \OCP\IRequest $request
     * @param \OCP\IURLGenerator $urlGenerator
     * @param \OCA\ContactsToFb\Lib\SettingsService $settingsService
     * @param \OCA\ContactsToFb\Lib\SyncService $syncService
     */
    public function __construct(
            $appName,
            IRequest $request,
            IURLGenerator $urlGenerator,
            SettingsService $settingsService,
            SyncService $syncService
    ) {
        parent::__construct($appName, $request);
        $this->urlGenerator = $urlGenerator;
        $this->settingsService = $settingsService;
        $this->syncService = $syncService;
    }

    /**
     * Index action.
     *
     * @NoCSRFRequired
     */
    public function index()
    {
        /* Link to synchronize route. */
        $route = 'contactstofb.page.synchronize';
        $synchronizeUrl = $this->urlGenerator->linkToRoute($route, array());

        /* Link to settings api. */
        $route = 'contactstofb.page.settings';
        $settingsUrl = $this->urlGenerator->linkToRoute($route, array());

        /* Settings */
        $settings = $this->settingsService->getSettingsArray();

        return new TemplateResponse('contactstofb', 'main', array(
            'synchronizeUrl' => $synchronizeUrl,
            'settingsUrl' => $settingsUrl,
            'settings' => $settings,
        ));
    }


    /**
     * Synchronizes the contacts to FRITZ!Box.
     */
    public function synchronize()
    {
        $result = $this->syncService->run();
        return $result;
    }

    /**
     * Saves the settings.
     */
    public function settings($test)
    {
        $post = (array)$this->request->post;
        $result = $this->settingsService->save($post);
        return array('status' => $result ? 'success' : 'failure');
    }
}
