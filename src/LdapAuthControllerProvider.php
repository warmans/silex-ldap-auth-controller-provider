<?php
namespace SilexProvider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Zend\Ldap\Exception\LdapException;

class LdapAuthControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        //use session storage
        $app->register(new \Silex\Provider\SessionServiceProvider());

        //register ldap service
        $app['auth.ldap'] = function() use ($app) {
            return new \Zend\Ldap\Ldap($app['auth.ldap.options']);
        };

        //redirect to login page if not logged inwar
        $app->before(
            function (Request $request) use ($app) {

                //user is not logged in go to login
                if (null === $app['session']->get('user') && $request->get("_route") != 'login') {
                    $app['session']->set('user_target', $request->getUri());
                    return $app->redirect('/auth/login');
                }

                //user is logged in - go to home
                if ($app['session']->get('user') && $request->get("_route") == 'login') {
                    return $app->redirect('/');
                }

                //write close to allow concurrent requests
                $app['session.storage']->save();
            }
        );

        $controllers->match('/login', function (Request $request) use ($app) {

            $view_params = array('error'=>null);

            //handle login where appropriate
            if ($request->get('user') && $request->get('password')) {

                try {
                    //throws exception
                    $app['auth.ldap']->bind($request->get('user'), $request->get('password'));
                    $app['session']->set('user', array('username' => $request->get('user')));

                    if ($user_target = $app['session']->get('user_target')) {
                        return $app->redirect($user_target);
                    } else {
                        return $app->redirect('/');
                    }
                } catch (LdapException $e) {
                    $view_params['error'] = 'Login Failed. The reason provided was: '.$e->getMessage();
                }
            }
            return $app['view']->render($app['auth.template.login'] ?: 'login', $view_params);

        })->bind('login');

        $controllers->match(
            '/logout',
            function (Request $request) use ($app) {
                $app['session']->set('user', null);
                return $app->redirect('/');
            }
        );

        return $controllers;
    }
}
