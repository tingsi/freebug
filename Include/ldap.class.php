<?php
# vim: sts=4 ts=4 sw=4 cindent fdm=marker expandtab nu


/*
* for openldap client.
*/

class LDAPException extends Exception {
}

function ldapError($info){
    error_log($info);
    throw new LDAPException($info);
}


class PowerLDAP {
    function __destruct() {
        ldap_close($this->ldap);
    }
    function __construct($url, $tls, $binddn, $bindpw, $userdn, $groupdn ){ 
        $ldap = ldap_connect($url);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        if ( $tls  && !ldap_start_tls($ldap) ) {
            ldapError("PowerLDAP SSL Error");
        }
        $bind = ldap_bind($ldap, $binddn, $bindpw);
        ldap_errno($ldap) and  ldapError("PowerLDAP- Bind error $errno  (".ldap_error($ldap).")");
         
        $this->ldap = $ldap;
        $this->userdn = $userdn;
        $this->groupdn = $groupdn;
    }

    function getUser($login) {
        $ldap = $this->ldap;
        $UserInfo = array();
        $UserInfo['UserName'] = $login;

# Search for user
        $filter = "(&(objectClass=person)(uid={$login}))";
        $search = ldap_search($ldap, $this->userdn, $filter);

        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
            return false;
        } 

        $entry = ldap_first_entry($ldap, $search);
        $userdn = ldap_get_dn($ldap, $entry);
        $UserInfo['userdn'] = $userdn;

        if( !$userdn ) {
            error_log("LDAP - User $login not found");
            return false;
        } 

        $mailValues = ldap_get_values($ldap, $entry, 'mail');
        if ( $mailValues["count"] )  $UserInfo['Email']  = $mailValues[0];

        $RealNames = ldap_get_values($ldap, $entry, 'cn');
        if ($RealNames['count']) $UserInfo['RealName'] = $RealNames[0];

        return $UserInfo;

    }

    function listUsers() {
        $ldap = $this->ldap;

        $filter = "(&(objectClass=person)(uid=*))";
        $search = ldap_search($ldap, $this->userdn, $filter);

        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
            return false;
        } 

        $Users = Array();

        $entry = ldap_first_entry($ldap, $search);
        do {
            $User = Array();
            $userdn = ldap_get_dn($ldap, $entry);
            $User['userdn'] = $userdn;

            $vals = ldap_get_values($ldap, $entry, 'uid');
            $User['UserName'] = $vals[0];

            $mailValues = ldap_get_values($ldap, $entry, 'mail');
            if ( $mailValues["count"] )  $User['Email']  = $mailValues[0];

            $RealNames = ldap_get_values($ldap, $entry, 'cn');
            if ($RealNames['count']) $User['RealName'] = $RealNames[0];

            $Users []= $User;
        } while ($entry = ldap_next_entry($ldap, $entry));

        return $Users;

    }

    function getGroup($group){
        $ldap = $this->ldap;
        $GroupInfo = array();
        $GroupInfo['GroupName'] = $group;

        $filter = "(&(objectClass=posixGroup)(cn={$group}))";
        $search = ldap_search($ldap, $this->groupdn, $filter);

        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
            return false;
        } 

        $entry = ldap_first_entry($ldap, $search);
        $userdn = ldap_get_dn($ldap, $entry);
        $GroupInfo['dn'] = $userdn;

        if( !$userdn ) {
            error_log("LDAP - Group $group not found");
            return false;
        } 

        $members= ldap_get_values($ldap, $entry, 'memberUid');
        unset($members['count']);
        $GroupInfo['members']  = $members;

        return $GroupInfo;
    }
    function listGroups(){

        $ldap = $this->ldap;

        $filter = "(&(objectClass=posixGroup))";
        $search = ldap_search($ldap, $this->groupdn, $filter);

        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
            return false;
        } 

        $entry = ldap_first_entry($ldap, $search);
        $Groups = array();
        do {
            $Group = Array();
            $gdn = ldap_get_dn($ldap, $entry);
            $Group['dn'] = $gdn;
            $vals = ldap_get_values($ldap, $entry, 'cn');
            $Group['GroupName'] = $vals[0];

            $vals = ldap_get_values($ldap, $entry, 'memberUid');
            if ($vals['count']){
                unset($vals['count']);
                $Group['members'] = $vals;
            }
            $Groups []= $Group;
        }while($entry = ldap_next_entry($ldap, $entry));

        return $Groups;
    }
}


