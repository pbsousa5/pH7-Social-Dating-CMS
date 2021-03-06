<?php
/**
 * @author         Pierre-Henry Soria <hello@ph7cms.com>
 * @copyright      (c) 2016, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Two-Factor Auth / Form / Processing
 */
namespace PH7;
defined('PH7') or die('Restricted access');

use
PH7\Framework\Mvc\Model\Engine\Util\Various,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Url\Header,
RobThree\Auth\TwoFactorAuth as Authenticator;

class VerificationCodeFormProcess extends Form
{
    /**
     * Every OPT is valid for 30 sec.
     * If somebody provides OTP at 29th sec, by the time it reaches the server OTP is expired.
     * So we can give OTP_TOLERANCE=1, it will check current  & previous OTP.
     * OTP_TOLERANCE=2, verifies current and last two OTPS
     * - Text from: http://hayageek.com/two-factor-authentication-with-google-authenticator-php/
     */
    const OTP_TOLERANCE = 1;

    public function __construct($sMod)
    {
        parent::__construct();

        $oAuthenticator = new Authenticator;

        $iProfileId = $this->session->get(TwoFactorAuthCore::PROFILE_ID_SESS_NAME);
        $sSecret = (new TwoFactorAuthModel($sMod))->getSecret($iProfileId);
        $sCode = $this->httpRequest->post('verification_code');
        $bCheck = $oAuthenticator->verifyCode($sSecret, $sCode, self::OTP_TOLERANCE);

        if ($bCheck)
        {
            $sCoreClassName = $this->getClassName($sMod);
            $sCoreModelClassName = $sCoreClassName . 'Model';
            $sCoreModelClass = new $sCoreModelClassName;
            $oUserData = $sCoreModelClass->readProfile($iProfileId, Various::convertModToTable($sMod));
            (new $sCoreClassName)->setAuth($oUserData, $sCoreModelClass, $this->session, new Framework\Mvc\Model\Security);

            $sUrl = ($sMod == PH7_ADMIN_MOD) ? Uri::get(PH7_ADMIN_MOD, 'main', 'index') : Uri::get($sMod, 'account', 'index');
            Header::redirect($sUrl, t('You are successfully logged in!'));
        }
        else
        {
            \PFBC\Form::setError('form_verification_code', t('Oops! The Verification Code is incorrect. Please try again.'));
        }
    }

    /**
     * Get main user core class according to the module.
     *
     * @param string $sMod Module name.
     * @return string Correct class nlass name
     * @throws \PH7\Framework\Error\CException\PH7InvalidArgumentException Explanatory message if the specified module is wrong.
     */
    protected function getClassName($sMod)
    {
        switch ($sMod)
        {
            case 'user':
                $oClass = 'UserCore';
            break;

            case 'affiliate':
                 $oClass = 'AffiliateCore';
            break;

            case PH7_ADMIN_MOD:
                $oClass = 'AdminCore';
            break;

            default:
                throw new \PH7\Framework\Error\CException\PH7InvalidArgumentException('Wrong "' . $sMod . '" module specified to get the class name');
        }

        // Need to use the fully qualified name (with namespace) as we create the class name dynamically
        return 'PH7\\' . $oClass;
    }
}
