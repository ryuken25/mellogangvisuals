<?php

namespace App\Controllers;

use App\Support\I18n;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * Switch language (route handler).
     * GET /lang/en atau /lang/id
     */
    public function setLanguage(string $lang)
    {
        I18n::setCookie($lang);
        I18n::set($lang);
        $back = (string) ($_SERVER['HTTP_REFERER'] ?? '/');
        // Hindari open-redirect ke host lain
        if (! preg_match('~^/[^/]~', $back)) {
            $back = '/';
        }
        return redirect()->to($back);
    }

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Inisialisasi i18n SEBELUM parent / view lain.
        I18n::init();

        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }
}
