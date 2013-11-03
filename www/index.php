<?php

include __DIR__ . "/../vendor/autoload.php";

use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

$app = new Application();
$app['debug'] = true;

$app->register(new UrlGeneratorServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider(), [
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

$app->on(AuthenticationEvents::AUTHENTICATION_FAILURE, function (AuthenticationEvent $event) {
});

$app->on(AuthenticationEvents::AUTHENTICATION_SUCCESS, function (AuthenticationEvent $event) {
});

$app->on(SecurityEvents::INTERACTIVE_LOGIN, function (InteractiveLoginEvent $event) {
});

$app->on(SecurityEvents::SWITCH_USER, function (SwitchUserEvent $event) {
});

$app->get("/", function () use ($app) {
    return $app['twig']->render('home.twig', []);
});

$app->get("/login", function (Request $request) use ($app) {
    return $app['twig']->render('login.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

$app->get("/admin", function () use ($app) {
    return $app['twig']->render('secret.twig', []);
})->bind('admin');

$app->run();