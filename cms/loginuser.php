<?php

/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 11:28
 */
class loginuser
{
    private $userid;
    private $password;
    private $username;
    private $usertype;
    private $groupid;
    private $zworksemail;
    private $zworkspassword;

    public function __construct($userId, $password)
    {
        $this->userid = $userId;
        $this->password = $password;
    }

    public function setValue($username, $userType, $groupId, $zWorksEmail, $zWorksPassword)
    {
        $this->username = $username;
        $this->usertype = $userType;
        $this->groupid = $groupId;
        $this->zworksemail = $zWorksEmail;
        $this->zworkspassword = $zWorksPassword;
    }

    public function getUserId()
    {
        return $this->userid;
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getZworksEmail()
    {
        return $this->zworksemail;
    }
}