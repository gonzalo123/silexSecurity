<?php

include __DIR__ . "/../vendor/autoload.php";

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

$app = new Application();
$app['debug'] = true;

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views',
]);

$app['security.firewalls'] = [
    'admin' => [
        'pattern' => '^/admin',
        'form' => [
            'login_path' => '/login',
            'check_path' => '/admin/login_check'
        ],
        'logout' => [
            'logout_path' => '/admin/logout',
            'invalidate_session' => false
        ],
        'users' => $app->share(function () use ($app) {
            return new UserProvider();
        }),
    ],
];


$app->get("/", function () use ($app)
{
    return $app['twig']->render('home.twig', []);
});

$app->get("/login", function (Request $request) use ($app)
{
    return $app['twig']->render('login.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

$app->get("/admin", function () use ($app) {
    return $app['twig']->render('secret.twig', []);
})->bind('admin');

// events that we can use related to security component
$app['dispatcher']->addListener(AuthenticationEvents::AUTHENTICATION_FAILURE, function (AuthenticationEvent $event) {
    });

$app['dispatcher']->addListener(AuthenticationEvents::AUTHENTICATION_SUCCESS, function (AuthenticationEvent $event) {
    });

$app['dispatcher']->addListener(SecurityEvents::INTERACTIVE_LOGIN, function (InteractiveLoginEvent $event) {
    });

$app['dispatcher']->addListener(SecurityEvents::SWITCH_USER, function (SwitchUserEvent $event) {
    });

$app->run();