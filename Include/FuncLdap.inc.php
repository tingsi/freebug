<?php
# vim: sts=4 ts=4 sw=4 cindent fdm=marker expandtab nu

/*
* return false on fail , or userinfo array if succeed.
*/
function ldapLogin($login, $upass){

    global $_CFG;

    $ldap_url = $_CFG['LDAP']['Url'];
    $ldap_ttls = $_CFG['LDAP']['TTLS'];
    $ldap_base = $_CFG['LDAP']['Base'];
    $dn_attribute = $_CFG['LDAP']['UserName'];
    $dn = "uid=$login,ou=peoples,$ldap_base"; 


    # Connect to LDAP
    $ldap = ldap_connect($ldap_url);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if ( $ldap_ttls && !ldap_start_tls($ldap) ) {
        error_log("LDAP - Unable to use StartTLS");
        return false;
    } 

    # Bind with password
    $bind = ldap_bind($ldap, $dn, $upass);
    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - user login error $errno  (".ldap_error($ldap).")");
        error_log("LDAP - ($login, $upass, $dn)");
        return false;
    }

    $filter="(objectclass=*)"; 
    $justthese = array($dn_attribute, "phone", "mail"); 
    $sr=ldap_read($ldap, $dn, $filter, $justthese);
    $entry = ldap_get_entries($ldap, $sr);

    $entry  = $entry[0];

    ldap_close($ldap);


    return Array('UserName' => $login, 'Email'=>$entry['mail'][0], 'RealName'=>$entry[$dn_attribute][0]);
}
 
# find a user
function ldapSearchUser($login){

    global $_CFG;

    $UserInfo = array();
    $UserInfo['UserName'] = $login;


    $ldap_url = $_CFG['LDAP']['Url'];
    $ldap_base = $_CFG['LDAP']['Base'];
    $ldap_binddn = $_CFG['LDAP']['BindDn'];
    $ldap_bindpw = $_CFG['LDAP']['BindPw'];
    $mail_attribute  = $_CFG['LDAP']['Email'];
    $filter = $_CFG['LDAP']['Filter'];
    $dn_attribute = $_CFG['LDAP']['UserName'];
    $ldap_ttls = $_CFG['LDAP']['TTLS'];


    # Connect to LDAP
    $ldap = ldap_connect($ldap_url);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if ( $ldap_ttls && !ldap_start_tls($ldap) ) {
        error_log("LDAP - Unable to use StartTLS");
        return false;
    } 

    # Bind
    if ( isset($ldap_binddn) && isset($ldap_bindpw) ) {
        $bind = ldap_bind($ldap, $ldap_binddn, $ldap_bindpw);
    } else {
        $bind = ldap_bind($ldap);
    }

    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - Bind error $errno  (".ldap_error($ldap).")");
        return false;
    } 
    
    # Search for user
    $filter = str_replace('{login}', $login, $filter);
    $search = ldap_search($ldap, $ldap_base, $filter);

    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
        return false;
    } 

    # Get user DN
    $entry = ldap_first_entry($ldap, $search);
    $userdn = ldap_get_dn($ldap, $entry);
    $UserInfo['userdn'] = $userdn;

    if( !$userdn ) {
        error_log("LDAP - User $login not found");
        return false;
    } 
    
    # Get user email 
    $mailValues = ldap_get_values($ldap, $entry, $mail_attribute);
    if ( $mailValues["count"] )  $UserInfo['Email']  = $mailValues[0];
    
    # Get user display name 
    $RealNames = ldap_get_values($ldap, $entry, $dn_attribute);
    if ($RealNames['count']) $UserInfo['RealName'] = $RealNames[0];

    ldap_close($ldap);

    return $UserInfo;

}

 
# get all user;for admin only
function ldapListUser(){
    global $_CFG;

    $ldap_url = $_CFG['LDAP']['Url'];
    $ldap_base = $_CFG['LDAP']['Base'];
    $ldap_binddn = $_CFG['LDAP']['BindDn'];
    $ldap_bindpw = $_CFG['LDAP']['BindPw'];
    $mail_attribute  = $_CFG['LDAP']['Email'];
    $filter = $_CFG['LDAP']['Filter'];
    $dn_attribute = $_CFG['LDAP']['UserName'];
    $ldap_ttls = $_CFG['LDAP']['TTLS'];
    $login_attribute= $_CFG['LDAP']['Login'];


    # Connect to LDAP
    $ldap = ldap_connect($ldap_url);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if ( $ldap_ttls && !ldap_start_tls($ldap) ) {
        error_log("LDAP - Unable to use StartTLS");
        return false;
    } 

    # Bind
    if ( isset($ldap_binddn) && isset($ldap_bindpw) ) {
        $bind = ldap_bind($ldap, $ldap_binddn, $ldap_bindpw);
    } else {
        $bind = ldap_bind($ldap);
    }

    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - Bind error $errno  (".ldap_error($ldap).")");
        return false;
    } 
    
    # Search for user
    $filter = str_replace('{login}', '*', $filter);
    $search = ldap_search($ldap, $ldap_base, $filter);

    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
        return false;
    } 

    $Users = Array();

    # Get user DN
    $entry = ldap_first_entry($ldap, $search);
    do {
        $User = Array();
        $userdn = ldap_get_dn($ldap, $entry);
        $User['userdn'] = $userdn;

        $vals = ldap_get_values($ldap, $entry, $login_attribute);
        $User['UserName'] = $vals[0];

        $mailValues = ldap_get_values($ldap, $entry, $mail_attribute);
        if ( $mailValues["count"] )  $User['Email']  = $mailValues[0];

        $RealNames = ldap_get_values($ldap, $entry, $dn_attribute);
        if ($RealNames['count']) $User['RealName'] = $RealNames[0];

        $Users []= $User;
    } while ($entry = ldap_next_entry($ldap, $entry));


    ldap_close($ldap);

    return $Users;

}

 
