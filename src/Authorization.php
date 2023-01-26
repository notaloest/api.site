<?php

namespace App;

class Authorization
{
    /**
     * @var Database
     */
    private Database $database;
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Database $database
     */
    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    /**
     * @param array $data
     * @return bool
     * @throws AuthorizationException
     */
    public function signup(array $data): bool
    {
        if (empty($data['username'])) {
            throw new AuthorizationException('The username should not be empty');
        }
        if (empty($data['password'])) {
            throw new AuthorizationException('The password should not be empty');
        }
        if (empty($data['con-password'])) {
            throw new AuthorizationException('The confirm password should not be empty');
        }
        if ($data['password']!== $data['con-password']) {
            throw new AuthorizationException('The confirm password should match');
        }

        $statement = $this->database->getConnection()->prepare('SELECT * FROM user WHERE login = :username');
        $statement->execute([
            'username' => $data['username']
        ]);
        $user = $statement->fetch();
        if (!empty($user)) {
            throw new AuthorizationException('User with such this login');
        }
        $statement = $this->database->getConnection()->prepare('INSERT INTO user (login, password, stage) VALUES (:username, :password, "U")');
        $statement->execute([
            'username' => $data['username'],
            'password' => hash('sha256', $data['password'])
        ]);
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws AuthorizationException
     */
    public function login(string $username, string $password): bool
    {
        if (empty($username)) {
            throw new AuthorizationException('The username should not be empty');
        }
        if (empty($password)) {
            throw new AuthorizationException('The password should not be empty');
        }
        $statement = $this->database->getConnection()->prepare('SELECT * FROM user WHERE login = :username');
        $statement->execute(['username' => $username,]);
        $user = $statement->fetch();
        if (empty($user)){
            throw new AuthorizationException('User with such login not found');
        }
        if (hash('sha256', $password) === $user['password']) {
            $tocken = bin2hex(random_bytes(16));
            $proxy = $this->database->getConnection()->prepare('UPDATE user SET tocken = :tocken WHERE login = :username');
            $proxy->execute(['tocken' => $tocken, 'username' => $username]);
            $this->session->setData('user', ['username' => $username, 'tocken' => $tocken]);
            return true;
        }
        throw new AuthorizationException('Incorrect login or password');

    }
    /**public function page(array $data)
    {
        if (empty($data ['article'])) {
            throw new AuthorizationException('The article should not be empty');
        }
        if (empty($data ['name'])) {
            throw new AuthorizationException('The name should not be empty');
        }
        if (empty($data ['type'])) {
            throw new AuthorizationException('The type should not be empty');
        }
        $statement = $this->database->getConnection()->prepare('SELECT * FROM accessories');
        $statement->execute();
        $outdata = $statement->fetchAll(\PDO::FETCH_ASSOC);
    }**/
}