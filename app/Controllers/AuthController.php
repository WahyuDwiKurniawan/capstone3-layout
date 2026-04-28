<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    function __construct()
    {
        helper('form');
    }

    public function login()
    {
        if ($this->request->getPost()) {
            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');

            $dataUsers = [
                [
                    'username' => 'danny',
                    'password' => '202cb962ac59075b964b07152d234b70', // 123
                    'role'     => 'admin',
                    'email'    => 'rdannyoka@dsn.dinus.ac.id'
                ],
                [
                    'username' => 'april',
                    'password' => '202cb962ac59075b964b07152d234b70', // 123
                    'role'     => 'admin',
                    'email'    => 'april@dsn.dinus.ac.id'
                ],
            ];

            // Cari user yang cocok
            $foundUser = null;
            foreach ($dataUsers as $user) {
                if ($username == $user['username']) {
                    $foundUser = $user;
                    break;
                }
            }

            if ($foundUser) {
                if (md5($password) == $foundUser['password']) {
                    session()->set([
                        'username'   => $foundUser['username'],
                        'role'       => $foundUser['role'],
                        'email'      => $foundUser['email'],
                        'login_time' => date('Y-m-d H:i:s'),
                        'isLoggedIn' => TRUE
                    ]);
                    return redirect()->to(base_url('/'));
                } else {
                    session()->setFlashdata('failed', 'Username & Password Salah');
                    return redirect()->back();
                }
            } else {
                session()->setFlashdata('failed', 'Username Tidak Ditemukan');
                return redirect()->back();
            }
        } else {
            return view('v_login');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}