<?php

class LDAP {

    /* Connection and bind functions */

    public static function getConnection() {
        $ds = ldap_connect(LDAP_SERVER);
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ds) {
            return new self($ds);
        } else {
            return NULL;
        }
    }

    function __construct($ds) {
        $this->ds = $ds;
        $this->users = array();
    }

    public function bindAnonymously() {
        $res = ldap_bind($this->ds);

        if (!$res) {
            error_log("Could not bind anonymously: " . ldap_error($this->ds));
            return false;
        }

        return true;
    }

    public function bindAdmin($pass) {
        $res = ldap_bind($this->ds, LDAP_ADMIN_DOMAIN, LDAP_ADMIN_PASSWORD);

        if (!$res) {
            error_log("Could not bind as admin: " . ldap_error($this->ds));
            return false;
        }

        return true;
    }

    public function bind($user = NULL, $pass = NULL) {
        if (!isset($user) && !isset($pass)) {
            return $this->bindAnonymously();
        }

        $search_res = ldap_search($this->ds, LDAP_PEOPLE_DOMAIN, 'cn=' . $user);

        if ($search_res) {
            $entries_res = ldap_get_entries($this->ds, $search_res);
            if ($entries_res[0]) {
                $bind = ldap_bind($this->ds, $entries_res[0]['dn'], $pass);
                if ($bind) {
                    $this->user = $user;
                    return true;
                } else {
                    error_log("Could not bind: " . ldap_error($this->ds));
                }
            } else {
                error_log("LDAP error: " . ldap_error($this->ds));
            }
        } else {
            error_log("Could not find user: " . ldap_error($this->ds));
        }

        return false;
    }

    public function unbind() {
        ldap_unbind($this->ds);
    }

    /* LDAP retrieval functions */

    public function getUserEntry($user) {
        if (array_key_exists($user, $this->users)) {
            return $this->users[$user];
        }

        $sr = ldap_search($this->ds, "cn=$user, " . LDAP_PEOPLE_DOMAIN, "sn=*");  
        $info = ldap_get_entries($this->ds, $sr);

        // $info[0] is the first user back from the search query
        $this->users[$user] = $info[0];
        return $info[0];
    }

    public function getPassword($user) {
        $userEntry = $this->getUserEntry($user);
        return $userEntry["userpassword"][0];
    }

    public function getPhone($user) {
        $userEntry = $this->getUserEntry($user);
        return $userEntry["homephone"][0];
    }

    public function getWebEmail($user) {
        $userEntry = $this->getUserEntry($user);
        return $userEntry["mail"][1];
    }

    public function getInternalEmail($user) {
        $userEntry = $this->getUserEntry($user);
        return $userEntry["mail"][0];
    }

}

?>
