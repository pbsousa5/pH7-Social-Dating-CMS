<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2016, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / User / Controller
 */
namespace PH7;

class SearchController extends Controller
{

    public function index()
    {
        Framework\Url\Header::redirect(Framework\Mvc\Router\Uri::get('user', 'search', 'quick'));
    }

    public function quick()
    {
        $this->view->page_title = $this->view->h1_title = t('Quick Search');
        $this->output();
    }

    public function advanced()
    {
        $this->view->page_title = $this->view->h1_title = t('Advanced Search');
        $this->output();
    }

}
