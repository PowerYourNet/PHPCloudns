<?php

class CloudnsException extends Exception {
	public function __construct($message, $code = 0, Exception $previous = null) { parent::__construct($message, $code, $previous); }
};
abstract class CloudnsRequest {

	protected $base = 'https://api.cloudns.net/';
	protected $SSL = true;
	protected $user = null;
	protected $pass = null;
	protected $params;
	protected $routes = array(
		'record_list' => 'records.json',
		'record_add' => 'add-record.json',
		'record_delete' => 'delete-record.json',
		'record_modify' => 'mod-record.json',
		'zones_list' => 'list-zones.json',
		'zones_stats' => 'get-zones-stats.json',
		'zones_pages' => 'get-pages-count.json',
		'zone_add' => 'register.json',
		'zone_delete' => 'delete.json',
		'slave_master_add' => 'add-master-server.json',
		'slave_master_delete' => 'delete-master-server.json',
		'slave_master_list' => 'master-servers.json',
		'zone_axfr_add' => 'axfr-add.json',
		'zone_axfr_delete' => 'axfr-remove.json',
		'zone_axfr_list' => 'axfr-list.json',
		'zone_import' => 'axfr-import.json',
		'nameserver_list' => 'available-name-servers',
		'zone_updated' => 'is-updated.json',
		'zone_status' => 'update-status.json',
		'domain_available' => 'check-available.json',
		'zone_stats_hourly' => 'statistics-hourly.json',
		'zone_stats_daily' => 'statistics-daily.json',
		'zone_stats_weekly' => 'statistics-weekly.json',
		'zone_stats_monthly' => 'statistics-monthly.json',
		'zone_stats_yearly' => 'statistics-yearly.json',
		'zone_stats_last30days' => 'statistics-last-30-days.json',
		'mailforward_add' => 'add-mail-forward.json',
		'mailforward_list' => 'mail-forwards.json',
		'mailforward_delete' => 'delete-mail-forward.json',
		'login_test' => 'login.json',		
		'clouddomain_add' => 'add-cloud-domain.json',
		'clouddomain_list' => 'list-cloud-domains.json',
		'clouddomain_delete' => 'delete-cloud-domain.json',
		'clouddomain_changemaster' => 'set-master-cloud-domain.json',
		'record_dynamicURL' => 'get-dynamic-url.json',
		'record_modifySOA' => 'modify-soa.json',
		'record_getSOA' => 'soa-details.json',
		'record_copy' => 'copy-records.json',
		'domain_contacts' => 'get-contacts.json',
		'domain_renew' => 'order-renew-domain.json',
		'domain_transfercode' => 'get-transfer-code.json',
		'domain_privacy' => 'edit-privacy-protection.json',
		'domain_transferlock' => 'edit-transfer-lock.json',
		'domain_info' => 'domain-info.json',		
	);
	protected $agent = 'PowerYourNet Cloudns PHP Library v1.0.1 - https://github.com/PowerYourNet';
	
	public function __construct($user, $pass) {	
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { $this->SSL = false; } else { $this->SSL = true; }
		$this->setCredentials($user, $pass); 
	}
	public function setSSLVerify ($bool) {
		if (!! $bool) { $this->SSL = true; }
		else { $this->SSL = false; }
	}
	public function setAgent ($agent) { $this->agent = $agent; }
	public function setBase ($base) { $this->base = $base; }
	protected function setCredentials ($user, $pass) {
		if (!!! is_null($user) && !!! is_null($pass) && !! is_string($pass) && !! is_int($user)) {
			$this->user = $user;
			$this->pass = $pass;
		} else { throw new CloudnsException('Incorrect ID or Password!'); }
	}
	private function getRequestURI ($params, $route, $type) {
		if (!!! is_null($this->user) && !!! is_null($this->pass)) {
			$this->params = $params;
			$this->params['auth-id'] = $this->user;
			$this->params['auth-password'] = $this->pass;
	
			return $this->base . $type . '/' . $this->routes[$route] . '?' . http_build_query($this->params);	
		} else { throw new CloudnsException('ID or Password cannot be null!'); }
	}
	private function doRequest ($params, $route, $type) { return $this->docURL($this->getRequestURI($params, $route, $type)); }
	protected function docURL ($url) {
		$ch = curl_init(); 
		echo str_replace('&0=', '&', rawurldecode ($url));
		curl_setopt($ch, CURLOPT_URL, str_replace('&0=', '&', rawurldecode ($url)));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->SSL);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        $resp = curl_exec($ch); 
		if(curl_errno($ch)){ throw new CloudnsException('Curl Request Error: ' . curl_error($ch)); }
		curl_close($ch);
		return $resp;
	}
	protected function to_string_array ($array, $name) { 
		$str = '';
		foreach ($array as $value) { $str .= $value . '&' . $name . '[]='; }
		return substr($str, 0, ((4 + strlen($name)) * -1));
	}
	public function record_list ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function record_delete ($domain, $id) { return $this->doRequest(array('domain-name' => $domain, 'record-id' => $id), __FUNCTION__, 'dns'); }
	public function zone_add () { 
		if (func_num_args() == 2) {	return $this->doRequest(array('domain-name' => func_get_arg(0), 'zone-type' => func_get_arg(1)), __FUNCTION__, 'dns');	}
		elseif (func_num_args() > 2) {
			if (!! is_array(func_get_arg(2))) {
				if (func_num_args() < 4) { return $this->doRequest(array('domain-name' => func_get_arg(0), 'zone-type' => func_get_arg(1), 'ns[]' => $this->to_string_array(func_get_arg(2), 'ns')), __FUNCTION__, 'dns'); }
				else { return $this->doRequest(array('domain-name' => func_get_arg(0), 'zone-type' => func_get_arg(1), 'ns[]' => $this->to_string_array(func_get_arg(2), 'ns'), 'master-ip' => func_get_arg(3)), __FUNCTION__, 'dns'); }
			} else { throw new CloudnsException('Invalid Cast Type, must be an Array()'); }
		}
	}
	public function zone_delete ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function zones_list () { 
		if (func_num_args() == 2) {	return $this->doRequest(array('page' => func_get_arg(0), 'rows-per-page' => func_get_arg(1)), __FUNCTION__, 'dns'); } 
		else { return $this->doRequest(array('page' => func_get_arg(0), 'rows-per-page' => func_get_arg(1), 'search' => func_get_arg(2)), __FUNCTION__, 'dns'); }		
	}
	public function zones_pages () {
		if (func_num_args() == 2) { return $this->doRequest(array('rows-per-page' => func_get_arg(0), 'search' => func_get_arg(1)), __FUNCTION__, 'dns');}
		else { return $this->doRequest(array('rows-per-page' => func_get_arg(0)), __FUNCTION__, 'dns'); }
	}
	public function zones_stats () { return $this->doRequest(array(), __FUNCTION__, 'dns'); }
	public function zone_status ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	
	public function zone_updated ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function zone_stats_hourly ($domain, $day, $month, $year) { return $this->doRequest(array('domain-name' => $domain, 'day' => $day, 'month' => $month, 'year' => $year), __FUNCTION__, 'dns'); }
	public function zone_stats_daily ($domain, $month, $year) { return $this->doRequest(array('domain-name' => $domain, 'month' => $month, 'year' => $year), __FUNCTION__, 'dns'); }
	public function zone_stats_monthly ($domain, $year) { return $this->doRequest(array('domain-name' => $domain, 'year' => $year), __FUNCTION__, 'dns'); }
	public function zone_stats_yearly ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function zone_stats_last30days ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	
	public function nameservers_list () { return $this->doRequest(array(), __FUNCTION__, 'dns'); }
	public function login_test() { return $this->doRequest(array(), __FUNCTION__, 'dns'); }
	public function mailforward_add ($domain, $box, $host, $dest) { return $this->doRequest(array('domain-name' => $domain, 'box' => $box, 'host' => $host, 'destination' => $dest), __FUNCTION__, 'dns'); }
	public function mailforward_list ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function mailforward_delete ($domain, $id) { return $this->doRequest(array('domain-name' => $domain, 'mail-forward-id' => $id), __FUNCTION__, 'dns'); }
	public function clouddomain_add ($domain, $clouddomain) { return $this->doRequest(array('domain-name' => $domain, 'cloud-domain-name' => $clouddomain), __FUNCTION__, 'dns'); }	
	public function clouddomain_list ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function clouddomain_delete ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function clouddomain_changemaster ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function slave_master_add ($domain, $ip) { return $this->doRequest(array('domain-name' => $domain, 'master-ip' => $ip), __FUNCTION__, 'dns'); }	
	public function slave_master_delete ($domain, $id) { return $this->doRequest(array('domain-name' => $domain, 'master-id' => $id), __FUNCTION__, 'dns'); }	
	public function slave_master_list ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }		
	public function zone_axfr_add ($domain, $ip) { return $this->doRequest(array('domain-name' => $domain, 'ip' => $ip), __FUNCTION__, 'dns'); }
	public function zone_axfr_delete ($domain, $id) { return $this->doRequest(array('domain-name' => $domain, 'id' => $id), __FUNCTION__, 'dns'); }
	public function zone_import ($domain, $server) { return $this->doRequest(array('domain-name' => $domain, 'server' => $server), __FUNCTION__, 'dns'); }
	public function zone_axfr_list ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	// UNTESTED FROM HERE on DOWNWARDS
	public function record_copy () {
		if (func_get_arg(2) == true) { return $this->doRequest(array('domain-name' => func_get_arg(0), 'from_domain' => func_get_arg(1), 'delete-current-records' => 1), __FUNCTION__, 'dns'); }
		else { return $this->doRequest(array('domain-name' => func_get_arg(0), 'from_domain' => func_get_arg(1)), __FUNCTION__, 'dns'); }
	}
	public function record_getSOA ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'dns'); }
	public function record_modifySOA ($domain, $ns, $mail, $refresh, $retry, $expire, $ttl) { return $this->doRequest(array('domain-name' => $domain, 'primary-ns' => $ns, 'admin-mail' => $mail, 'refresh' => $refresh, 'retry' => $retry, 'expire' => $expire, 'default-ttl' => $ttl), __FUNCTION__, 'dns'); }	
	public function record_dynamicURL ($domain, $id) { return $this->doRequest(array('domain-name' => $domain, 'record-id' => $id), __FUNCTION__, 'dns'); }
	public function domain_available ($name, $tld) { return $this->doRequest(array('name' => $name, 'tld' => $tld), __FUNCTION__, 'domains'); }
	public function domain_renew ($domain, $period) { return $this->doRequest(array('domain-name' => $domain, 'period' => $period), __FUNCTION__, 'domains'); }	
	public function domain_transfercode ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'domains'); }	
	public function domain_privacy ($domain, $privacy) { 
		if ($privacy == true) { return $this->doRequest(array('domain-name' => $domain, 'status' => 1), __FUNCTION__, 'domains'); } 
		else { return $this->doRequest(array('domain-name' => $domain, 'status' => 0), __FUNCTION__, 'domains'); }
	}
	public function domain_transferlock ($domain, $lock) { 
		if ($lock == true) { return $this->doRequest(array('domain-name' => $domain, 'status' => 1), __FUNCTION__, 'domains'); } 
		else { return $this->doRequest(array('domain-name' => $domain, 'status' => 0), __FUNCTION__, 'domains'); }
	}
	public function domain_info ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'domains'); }	
	public function domain_contacts ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'domains'); }	
	public function domain_listnameservers ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'domains'); }	
	public function domain_modifynameservers ($domain, $array) { return $this->doRequest(array('domain-name' => $domain, 'nameservers[]' => $this->to_string_array($array, 'nameservers')), __FUNCTION__, 'domains'); }
	public function domain_listglueservers ($domain) { return $this->doRequest(array('domain-name' => $domain), __FUNCTION__, 'domains'); }		
	public function domain_deleteglueserver ($domain, $host, $ip) { return $this->doRequest(array('domain-name' => $domain, 'host' => $host, 'ip' => $ip), __FUNCTION__, 'domains'); }	
	public function domain_addglueserver ($domain, $host, $ip) { return $this->doRequest(array('domain-name' => $domain, 'host' => $host, 'ip' => $ip), __FUNCTION__, 'domains'); }
	public function domain_modifyglueserver ($domain, $host, $oldip, $new) { return $this->doRequest(array('domain-name' => $domain, 'host' => $host, 'old-ip' => $oldip, 'new-ip' => $newip), __FUNCTION__, 'domains'); }
	public function record_add ($domain, $type, $host, $rec, $ttl, $array) { return $this->doRequest(array('domain-name' => $domain, 'record-type' => $type, 'host' => $host, 'record' => $rec, 'ttl' => $ttl, http_build_query($array)), __FUNCTION__, 'dns'); }	
	public function record_modify ($domain, $id, $host, $rec, $ttl, $array) { return $this->doRequest(array('domain-name' => $domain, 'record-id' => $id, 'host' => $host, 'record' => $rec, 'ttl' => $ttl, http_build_query($array)), __FUNCTION__, 'dns'); }		
}

final class Cloudns extends CloudnsRequest {
	public function __construct ($user, $pass) { 
		if (!!! defined('PHP_VERSION_ID')) {
			$version = explode('.', PHP_VERSION);
			define('PHP_VERSION_ID', (int) ($version[0] * 10000 + $version[1] * 100 + $version[2]));
		}
		if (PHP_VERSION_ID >= 50300) {
			if (!! extension_loaded('curl') && !! function_exists('curl_init')) { parent::__construct($user, $pass); }
			else { throw new CloudnsException('PHP cURL extension not found!'); }
		} else { throw new CloudnsException('PHP version < 5.3 not supported!'); }
	}
}
