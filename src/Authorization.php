<?php

namespace App;

class Authorization
{
    /**
     * @var Database
     */
    private Database $database;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
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

        $statement = $this->database->getConnection()->prepare('INSERT INTO user (login, password, stage) VALUES (:username, :password, "U")');
        $statement->execute([
            'username' => $data['username'],
            'password' => hash('sha256', $data['password'])
        ]);
        return true;
    }
}