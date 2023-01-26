<?php
use \Slim\Factory\AppFactory;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;
use \App\Database;
use \App\Authorization;
use \App\AuthorizationException;
use \App\Session;
use \Psr\Http\Server\RequestHandlerInterface;

require __DIR__. '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // $_POST

$session = new Session();
$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($session){
    $session->start();
    $response = $handler->handle($request);
    $session->save();
    return $response;
};

$app->add($sessionMiddleware);

$config = include_once 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

$database = new Database($dsn, $username, $password);
$authorization = new Authorization($database, $session);

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('index.twig', ['user' => $session->getData('user')]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('login.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/login-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session){
    $params = (array) $request-> getParsedBody();
    try {
        $authorization->login($params['username'], $params['password']);
        } catch (AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/login')
            ->withStatus(302);
    }
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/signup', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('signup.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/signup-post', function (ServerRequestInterface $request, ResponseInterface $response) use ($authorization, $session) {
    $params = (array) $request->getParsedBody();
    try {
        $authorization->signup($params);
    } catch (AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/signup')
            ->withStatus(302);
    }
    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/page1', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('page1.twig', ['user' => $session->getData('user')]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/page1-post', function (ServerRequestInterface $request, ResponseInterface $response){
    $response->getBody()->write('201 Created');
});

$app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response) use ($session, $authorization){
    $session->setData('user', null);
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->run();