<?php
// разрешение выполнение с другого домена
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *, Authorization');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');
// определение данных как JSON
header('Content-type: json/application');
//подключение внешних файлов
require 'db.php';
require 'function.php';

$method = $_SERVER['REQUEST_METHOD'];

$q = $_GET['q'];

$params = explode('/', $q);

$type = $params[0];
$id = $params[1];

if ($method === 'GET') {
    if ($type === 'mess') {   // Получение сообщений
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        getMess($connect, $data);
    } elseif ($type === 'logout') {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        logout($connect, $data);
    }
} elseif ($method === 'POST') {
    if ($type === 'mess') {   //отправка сообщений
        addMess($connect, $_POST);
    } elseif ($type === 'signup') {   // регистрация
        signup($connect, $_POST);
    } elseif ($type === 'login') {   // авторизация
        login($connect, $_POST);
    }
} elseif ($method === 'PATCH') {   // редактирование сообщений
    if ($type === 'mess') {
        if (isset($id)) {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            editMess($connect, $id, $data);
        }
    }
} elseif ($method === 'DELETE') {   // удаление сообщений
    if ($type === 'mess') {
        if (isset($id)) {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            delMess($connect, $id, $data);
        }
    }
}
