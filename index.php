<?php
use \Slim\Factory\AppFactory;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;
use \App\Database;
use \App\Authorization;
use \App\AuthorizationException;

require __DIR__. '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // $_POST

$config = include_once 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

$database = new Database($dsn, $username, $password);
$authorization = new Authorization($database);

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig) {

    $body = $twig->render('index.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig) {
    $body = $twig->render('login.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->post('/login-post', function (ServerRequestInterface $request, ResponseInterface $response){
    $response->getBody()->write('202 Accepted');
});

$app->get('/signup', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig) {
    $body = $twig->render('signup.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->post('/signup-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization) {
    $params = (array) $request->getParsedBody();
    try {
        $authorization->signup($params);
    } catch (AuthorizationException $exception) {
        return $response->withHeader('Location', '/signup')
            ->withStatus(302);
    }
    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/page1', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig) {
    $body = $twig->render('page1.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->post('/page1-post', function (ServerRequestInterface $request, ResponseInterface $response){
    $response->getBody()->write('201 Created');
});

$app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response){
    $response->getBody()->write('200 OK');
});

$app->run();