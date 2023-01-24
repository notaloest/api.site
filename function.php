<?php
// получение сообщений

function getMess ($connect, $data) {
    $token = $data['tocken'];
    $id = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE tocken='$token'"))["id"];

    if ($chek === $id) {
        $messs = mysqli_query($connect, "SELECT * FROM `mess`");
        $messList = [];
        while ($mess = mysqli_fetch_assoc($messs)) { // проверка налчия сообщений
            $messList[] = $mess;
        }
        echo json_encode($messList);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "Unauthorized",
        ];

        echo json_encode($res);
    }
}

// отправка сообщений

function addMess ($connect, $data) {

    $token = $data['tocken'];
    $mess = $data['body'];
    $idu = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE tocken='$token'"))["id"];

    if ($chek === $idu) {
        mysqli_query($connect, "INSERT INTO mess (`id`, `user`, `body`) VALUES (NULL, '$idu', '$mess')");

        http_response_code(201);

        $res = [
            "status" => true,
            "mess_id" => mysqli_insert_id($connect)
        ];

        echo json_encode($res);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "Unauthorized"
        ];

        echo json_encode($res);
    }
}

// редактирование сообщений

function editMess ($connect, $id, $data) {
    $mess = $data ['body'];
    $token = $data['tocken'];
    $idu = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE tocken='$token'"))["id"];

    if ($chek === $idu) {
        mysqli_query($connect, "UPDATE mess SET body = '$mess' WHERE id = '$id' AND user = '$idu'");

        http_response_code(200);

        $res = [
            "status" => true,
            "messege" => "Messege is updated"
        ];

        echo json_encode($res);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "Unauthorized"
        ];

        echo json_encode($res);
    }
}

// удаление сообщений

function delMess ($connect, $id, $data) {
    $token = $data['tocken'];
    $idu = $data['idu'];

    $chek = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE tocken='$token'"))["id"];

    if ($chek === $idu) {
        mysqli_query($connect, "DELETE FROM mess WHERE id = '$id' AND user = '$idu'");

        http_response_code(200);

        $res = [
            "status" => true,
            "messege" => "Messege is deleted"
        ];

        echo json_encode($res);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "Unauthorized"
        ];

        echo json_encode($res);
    }
}

// регистрация

function signup ($connect, $data) {
    $login = $data['login'];
    $pass = $data['pass'];
    $nick = $data['nick'];

    $c_login = mysqli_fetch_assoc(mysqli_query($connect, "SELECT login FROM users WHERE login = '$login'"))['login'];

    if ($login <> $c_login) {
        $hash = hash('sha256', $pass);

        mysqli_query($connect, "INSERT INTO users (id, login, pass, nick, tocken) VALUES (NULL, '$login', '$hash', '$nick', NULL)");

        http_response_code(201);

        $res = [
            "status" => true,
            "messege" => "Account is created"
        ];

        echo json_encode($res);
    } else {
        http_response_code(201);

        $res = [
            "status" => false,
            "messege" => "err_login"
        ];

        echo json_encode($res);
    }
}

// авторизация

function login ($connect, $data) {
    $login = $data['login'];
    $pass = $data['pass'];
    $hash = hash('sha256', $pass);


    $user = mysqli_query($connect, "SELECT `nick`, `id` FROM `users` WHERE `login` = '$login' AND `pass` = '$hash'");

    $userfd = mysqli_fetch_assoc($user);

    $nick = $userfd['nick'];
    $id = $userfd['id'];
    $tocken = bin2hex(random_bytes(16));

    if (isset($user)) {
        mysqli_query($connect, "UPDATE users SET tocken = '$tocken' WHERE `login` = '$login' AND `pass` = '$hash'");
        $userdata = [
            "nick" => $nick,
            "id" => $id,
            "tocken" => $tocken
        ];

        echo json_encode($userdata);
    } else {
        http_response_code(401);

        $res = [
            "status" => false,
            "messege" => "err_auth"
        ];

        echo json_encode($res);
    }

}
// выход

function logout ($connect, $data) {
    $id = $data['idu'];
    mysqli_query($connect, "UPDATE users SET tocken = NULL WHERE id = '$id'");

    http_response_code(200);

    $res = [
        "status" => true,
        "messege" => "Logout"
    ];

    echo json_encode($res);
}