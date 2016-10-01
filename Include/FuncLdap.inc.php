<?php
# vim: sts=4 ts=4 sw=4 cindent fdm=marker expandtab nu

/*
* return false on fail , or userinfo array if succeed.
*/
function ldapLogin($url, $tls, $path, $login, $upass){

    # Connect to LDAP
    $ldap = ldap_connect($url);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if ( $tls && !ldap_start_tls($ldap) ) {
        error_log("LDAP - Unable to use StartTLS");
        return false;
    } 

    # Bind with password
    $bind = ldap_bind($ldap, $path, $upass);
    $errno = ldap_errno($ldap);
    if ( $errno ) {
        error_log("LDAP - user login error $errno  (".ldap_error($ldap).")");
        error_log("LDAP - ($login, $upass, $path)");
        return false;
    }

    $filter="(objectclass=*)"; 
    $justthese = array('uid', 'cn', "phone", "mail"); 
    $sr=ldap_read($ldap, $path, $filter, $justthese);
    $entry = ldap_get_entries($ldap, $sr);

    $entry  = $entry[0];

    ldap_close($ldap);


    return Array('UserName' => $login, 'Email'=>$entry['mail'][0], 'RealName'=>$entry['cn'][0]);
}
 

