Auth (LDAP) Controller Provider for Silex
================================================

Usage:

    $app->mount('/auth', new \SilexProvider\LdapAuthControllerProvider());

Requires the following config options:

    'auth.template.login' => 'login',
    'auth.ldap.options' => array(
        'host'                  => '',
        'bindRequiresDn'        => true,
        'baseDn'                => '',
        'accountFilterFormat'   => '',
        'username'              => '',
        'password'              => '',
    )

(see Zend\Ldap docs for ldap option explanations)

The auth template should be a template available in your view paths (see litek/silex-php-engine).

The login template must submit a form to /auth/login with a field called "user" and "password". Groups can be enforced
wuth the baseDn.